<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TeamRepository;
use App\Repositories\TournamentRepository;
use RuntimeException;

class TournamentService
{
    private TournamentRepository $tournaments;
    private TeamRepository $teams;

    public function __construct()
    {
        $this->tournaments = new TournamentRepository();
        $this->teams = new TeamRepository();
    }

    public function create(array $payload): int
    {
        $this->tournaments->beginTransaction();
        try {
            $id = $this->tournaments->create([
                'nombre' => $payload['nombre'],
                'anio' => $payload['anio'],
                'rondas_iniciales' => $payload['rondas_iniciales'],
                'inicio_programado_at' => $payload['inicio_programado_at'] ?? null,
                'estado' => 'sorteo_pendiente',
            ]);

            $this->tournaments->attachPlayers($id, $payload['jugadores']);
            $this->tournaments->attachPots($id, $payload['bombos']);

            $this->tournaments->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->tournaments->rollback();
            throw $e;
        }
    }

    public function runDraw(int $tournamentId): array
    {
        $players = $this->tournaments->players($tournamentId);
        if (count($players) < 2) {
            throw new RuntimeException('Debe haber al menos 2 jugadores para sortear.');
        }

        $ids = array_column($players, 'id');
        shuffle($ids);
        $this->tournaments->saveDrawOrder($tournamentId, $ids);
        $this->createTurns($tournamentId, $ids);
        $this->tournaments->updateState($tournamentId, 'eleccion_equipos');

        return $this->tournaments->drawOrder($tournamentId);
    }

    private function createTurns(int $tournamentId, array $orderedUserIds): void
    {
        $pots = $this->tournaments->pots($tournamentId);
        $turn = 1;
        foreach ($pots as $pot) {
            for ($pickRound = 1; $pickRound <= (int) $pot['equipos_por_jugador']; $pickRound++) {
                foreach ($orderedUserIds as $userId) {
                    $this->tournaments->createTurn($tournamentId, (int) $pot['bombo_id'], (int) $userId, $turn++);
                }
            }
        }
    }

    public function getTurnState(int $tournamentId): array
    {
        $current = $this->tournaments->currentTurn($tournamentId);
        if (!$current) {
            return [
                'current' => null,
                'next' => null,
                'history' => $this->tournaments->selectionsByTournament($tournamentId),
            ];
        }

        if (empty($current['equipos_ofrecidos_json'])) {
            $pots = $this->tournaments->pots($tournamentId);
            $potData = null;
            foreach ($pots as $pot) {
                if ((int) $pot['bombo_id'] === (int) $current['bombo_id']) {
                    $potData = $pot;
                    break;
                }
            }
            $offerSize = $potData ? (int) $potData['oferta_por_turno'] : 3;
            $offerTeams = $this->teams->randomAvailableByPotForTournament(
                (int) $current['bombo_id'],
                $tournamentId,
                $offerSize
            );
            $teamIds = array_map(fn (array $team) => (int) $team['id'], $offerTeams);
            $this->tournaments->updateTurnOffer((int) $current['id'], $teamIds);
            $current['equipos_ofrecidos_json'] = json_encode($teamIds);
        }

        return [
            'current' => $this->inflateTurnOffer($current),
            'next' => $this->tournaments->nextTurn($tournamentId),
            'history' => $this->tournaments->selectionsByTournament($tournamentId),
        ];
    }

    private function inflateTurnOffer(array $turn): array
    {
        $ids = json_decode((string) $turn['equipos_ofrecidos_json'], true) ?: [];
        $offered = [];
        foreach ($ids as $id) {
            $team = $this->teams->find((int) $id);
            if ($team) {
                $offered[] = $team;
            }
        }
        $turn['equipos_ofrecidos'] = $offered;

        return $turn;
    }

    public function chooseTeam(int $tournamentId, int $userId, int $teamId): void
    {
        $this->tournaments->beginTransaction();
        try {
            $current = $this->tournaments->currentTurn($tournamentId);
            if (!$current) {
                throw new RuntimeException('No hay turnos pendientes.');
            }
            if ((int) $current['usuario_id'] !== $userId) {
                throw new RuntimeException('No es tu turno para elegir equipo.');
            }

            $offeredIds = json_decode((string) $current['equipos_ofrecidos_json'], true) ?: [];
            if (!in_array($teamId, $offeredIds, true)) {
                throw new RuntimeException('El equipo seleccionado no pertenece a la oferta actual.');
            }

            $this->tournaments->saveSelection(
                $tournamentId,
                (int) $current['bombo_id'],
                $userId,
                $teamId,
                (int) $current['id']
            );
            $this->tournaments->completeTurn((int) $current['id'], $teamId);

            if ($this->tournaments->allTurnsSelected($tournamentId)) {
                $this->generateFixture($tournamentId);
                $this->tournaments->updateState($tournamentId, 'en_juego');
            }

            $this->tournaments->commit();
        } catch (\Throwable $e) {
            $this->tournaments->rollback();
            throw $e;
        }
    }

    public function generateFixture(int $tournamentId, ?array $forcedPlayers = null, string $roundType = 'normal'): void
    {
        $tournament = $this->tournaments->find($tournamentId);
        $players = $forcedPlayers ?: $this->tournaments->players($tournamentId);
        $playerIds = array_map(fn (array $row) => (int) $row['id'], $players);

        if (count($playerIds) < 2) {
            return;
        }

        $roundsToPlay = $roundType === 'normal' ? (int) $tournament['rondas_iniciales'] : 1;
        for ($stageRound = 1; $stageRound <= $roundsToPlay; $stageRound++) {
            $rotation = $playerIds;
            if (count($rotation) % 2 !== 0) {
                $rotation[] = 0;
            }

            $totalRounds = count($rotation) - 1;
            for ($round = 0; $round < $totalRounds; $round++) {
                $globalRoundNumber = $this->tournaments->latestRoundNumber($tournamentId, $roundType) + 1;
                $roundId = $this->tournaments->createRound($tournamentId, $globalRoundNumber, $roundType);

                $pairings = [];
                $half = (int) (count($rotation) / 2);
                for ($i = 0; $i < $half; $i++) {
                    $home = $rotation[$i];
                    $away = $rotation[count($rotation) - 1 - $i];
                    if ($home !== 0 && $away !== 0) {
                        $pairings[] = [$home, $away];
                    }
                }

                foreach ($pairings as [$home, $away]) {
                    if (($round + $stageRound) % 2 === 0) {
                        [$home, $away] = [$away, $home];
                    }
                    $this->tournaments->createMatch([
                        'torneo_id' => $tournamentId,
                        'ronda_id' => $roundId,
                        'jugador_local_id' => $home,
                        'jugador_visitante_id' => $away,
                        'equipo_local' => $this->teamLabelForPlayer($tournamentId, $home),
                        'equipo_visitante' => $this->teamLabelForPlayer($tournamentId, $away),
                        'estado' => 'pendiente',
                    ]);
                }

                $pivot = array_shift($rotation);
                $last = array_pop($rotation);
                array_unshift($rotation, $pivot);
                array_splice($rotation, 1, 0, [$last]);
            }
        }
    }

    private function teamLabelForPlayer(int $tournamentId, int $userId): string
    {
        $all = $this->tournaments->selectionsByTournament($tournamentId);
        $teams = [];
        foreach ($all as $selection) {
            if ((int) $selection['usuario_id'] === $userId) {
                $teams[] = $selection['equipo_nombre'];
            }
        }
        return implode(' / ', $teams);
    }

    public function submitMatchResult(int $matchId, int $currentUserId, int $goalsHome, int $goalsAway): void
    {
        $match = $this->tournaments->matchById($matchId);
        if (!$match) {
            throw new RuntimeException('Partido no encontrado.');
        }
        $isAdminUser = is_admin();

        if (!$isAdminUser) {
            if ($match['estado'] !== 'pendiente') {
                throw new RuntimeException('Solo se puede cargar el siguiente partido pendiente.');
            }

            $activeRound = $this->tournaments->nextPendingRound((int) $match['torneo_id']);
            if (!$activeRound || (int) $activeRound['ronda_id'] !== (int) $match['ronda_id']) {
                throw new RuntimeException('Solo puedes cargar resultados en la ronda actualmente habilitada.');
            }
        }

        if ((int) $match['jugador_local_id'] !== $currentUserId && (int) $match['jugador_visitante_id'] !== $currentUserId && !$isAdminUser) {
            throw new RuntimeException('No puedes cargar resultados en este partido.');
        }

        if ($goalsHome < 0 || $goalsAway < 0 || $goalsHome > 9 || $goalsAway > 9) {
            throw new RuntimeException('Los goles deben estar entre 0 y 9.');
        }

        $this->tournaments->updateMatchResult($matchId, $goalsHome, $goalsAway, $currentUserId);
    }

    public function resolveTournamentState(int $tournamentId): array
    {
        if ($this->tournaments->unfinishedMatchesCount($tournamentId, 'normal') > 0) {
            return ['state' => 'en_juego', 'message' => 'Todavia hay partidos pendientes.'];
        }

        $table = $this->tournaments->standings($tournamentId);
        if (empty($table)) {
            return ['state' => 'en_juego', 'message' => 'No hay resultados para evaluar.'];
        }

        $topPoints = (int) $table[0]['puntos'];
        $leaders = array_values(array_filter($table, fn (array $row) => (int) $row['puntos'] === $topPoints));

        if (count($leaders) === 1) {
            $winner = $leaders[0];
            $this->tournaments->saveChampion($tournamentId, (int) $winner['usuario_id']);
            $this->tournaments->updateState($tournamentId, 'finalizado');
            return ['state' => 'finalizado', 'message' => 'Torneo finalizado con campeon definido.'];
        }

        $this->createTieBreakRound($tournamentId, $leaders);
        $this->tournaments->updateState($tournamentId, 'desempate');
        return ['state' => 'desempate', 'message' => 'Se genero una ronda de desempate.'];
    }

    public function resolveTieBreak(int $tournamentId): array
    {
        if ($this->tournaments->unfinishedMatchesCount($tournamentId, 'desempate') > 0) {
            return ['state' => 'desempate', 'message' => 'Hay partidos de desempate pendientes.'];
        }

        $table = $this->tournaments->standings($tournamentId);
        if (empty($table)) {
            return ['state' => 'desempate', 'message' => 'Sin datos suficientes para desempatar.'];
        }

        $topPoints = (int) $table[0]['puntos'];
        $leaders = array_values(array_filter($table, fn (array $row) => (int) $row['puntos'] === $topPoints));
        if (count($leaders) === 1) {
            $this->tournaments->saveChampion($tournamentId, (int) $leaders[0]['usuario_id']);
            $this->tournaments->updateState($tournamentId, 'finalizado');
            return ['state' => 'finalizado', 'message' => 'Desempate cerrado con campeon.'];
        }

        $this->createTieBreakRound($tournamentId, $leaders);
        return ['state' => 'desempate', 'message' => 'Persistio el empate: se genero una nueva superfinal.'];
    }

    private function createTieBreakRound(int $tournamentId, array $leaders): void
    {
        $players = array_map(fn (array $row) => ['id' => (int) $row['usuario_id']], $leaders);
        $this->generateFixture($tournamentId, $players, 'desempate');
    }
}
