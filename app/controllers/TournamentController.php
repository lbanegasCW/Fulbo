<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\PotRepository;
use App\Repositories\TournamentRepository;
use App\Repositories\UserRepository;
use App\Services\TournamentService;

class TournamentController extends BaseController
{
    private TournamentRepository $tournaments;
    private UserRepository $users;
    private PotRepository $pots;
    private TournamentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->tournaments = new TournamentRepository();
        $this->users = new UserRepository();
        $this->pots = new PotRepository();
        $this->service = new TournamentService();
    }

    public function index(Request $request): void
    {
        $userId = (int) auth_user()['id'];
        $winnerId = $request->input('ganador_id') ? (int) $request->input('ganador_id') : null;
        $year = $request->input('anio') ? (int) $request->input('anio') : null;
        $historyMode = $request->input('historial') === '1';
        $recentHistory = is_admin()
            ? $this->tournaments->recentFinishedGlobal()
            : $this->tournaments->recentFinishedForUser($userId);

        if ($winnerId && $year) {
            $winner = $this->users->findById($winnerId);
            $this->view->render('player/tournaments', [
                'title' => 'Torneo',
                'activeTournament' => null,
                'filteredTournaments' => $this->tournaments->wonByUserAndYear($winnerId, $year),
                'filterLabel' => $winner ? ('Ganados por ' . $winner['nombre'] . ' en ' . $year) : ('Ganados en ' . $year),
                'recentHistory' => $recentHistory,
                'showRecentHistory' => false,
                'historyYear' => (int) date('Y'),
            ]);
            return;
        }

        if ($historyMode) {
            $selectedYear = $year ?: (int) date('Y');
            $all = is_admin()
                ? $this->tournaments->all()
                : $this->tournaments->forUser($userId);

            $filtered = array_values(array_filter($all, static fn (array $t) => (int) $t['anio'] === $selectedYear));

            $this->view->render('player/tournaments', [
                'title' => 'Torneo',
                'activeTournament' => null,
                'filteredTournaments' => $filtered,
                'filterLabel' => 'Historial ' . $selectedYear,
                'recentHistory' => $recentHistory,
                'showRecentHistory' => false,
                'historyYear' => $selectedYear,
            ]);
            return;
        }

        $activeTournament = is_admin()
            ? $this->tournaments->activeGlobal()
            : $this->tournaments->activeForUser((int) auth_user()['id']);

        $this->view->render('player/tournaments', [
            'title' => 'Torneo',
            'activeTournament' => $activeTournament,
            'filteredTournaments' => [],
            'filterLabel' => null,
            'recentHistory' => $recentHistory,
            'showRecentHistory' => true,
            'historyYear' => (int) date('Y'),
        ]);
    }

    public function createForm(Request $request): void
    {
        $players = array_values(array_filter($this->users->all(1), fn (array $u) => $u['role_slug'] === 'jugador'));
        $this->view->render('admin/tournaments_create', [
            'title' => 'Nuevo torneo',
            'players' => $players,
            'pots' => $this->pots->active(),
        ]);
    }

    public function editForm(Request $request): void
    {
        $id = (int) $request->input('id');
        $tournament = $this->tournaments->find($id);

        if (!$tournament) {
            flash('error', 'Torneo no encontrado.');
            $this->response->redirect('/torneos');
        }

        if ((string) $tournament['estado'] === 'finalizado') {
            flash('error', 'No se puede editar un torneo finalizado.');
            $this->response->redirect('/torneos/' . $id);
        }

        $this->view->render('admin/tournaments_edit', [
            'title' => 'Editar torneo',
            'tournament' => $tournament,
        ]);
    }

    public function store(Request $request): void
    {
        $players = array_map('intval', (array) $request->input('jugadores', []));
        $potId = (int) $request->input('bombo_id');

        if (empty($players) || $potId <= 0) {
            flash('error', 'Debes seleccionar jugadores y un bombo.');
            $this->response->redirect('/admin/torneos/nuevo');
        }

        $potConfig = [[
            'bombo_id' => $potId,
            'equipos_por_jugador' => (int) ($request->input('equipos_por_jugador') ?? 1),
            'oferta_por_turno' => (int) ($request->input('oferta_por_turno') ?? 2),
        ]];

        $id = $this->service->create([
            'nombre' => trim((string) $request->input('nombre')),
            'anio' => (int) $request->input('anio'),
            'rondas_iniciales' => (int) $request->input('rondas_iniciales'),
            'inicio_programado_at' => $this->normalizeDatetimeLocal((string) $request->input('inicio_programado_at', '')),
            'jugadores' => $players,
            'bombos' => $potConfig,
        ]);

        flash('success', 'Torneo creado en estado sorteo pendiente.');
        $this->response->redirect('/torneos/' . $id);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->input('torneo_id');
        $tournament = $this->tournaments->find($id);
        if (!$tournament) {
            flash('error', 'Torneo no encontrado.');
            $this->response->redirect('/torneos');
        }

        if ((string) $tournament['estado'] === 'finalizado') {
            flash('error', 'No se puede editar un torneo finalizado.');
            $this->response->redirect('/torneos/' . $id);
        }

        $name = trim((string) $request->input('nombre'));
        $year = (int) $request->input('anio');
        $rounds = (int) $request->input('rondas_iniciales');

        if ($name === '' || $year < 2000 || $year > 2100 || $rounds < 1 || $rounds > 5) {
            flash('error', 'Revisa los datos del torneo antes de guardar.');
            $this->response->redirect('/admin/torneos/' . $id . '/editar');
        }

        $this->tournaments->updateBasicData($id, [
            'nombre' => $name,
            'anio' => $year,
            'rondas_iniciales' => $rounds,
            'inicio_programado_at' => $this->normalizeDatetimeLocal((string) $request->input('inicio_programado_at', '')),
        ]);

        flash('success', 'Torneo actualizado correctamente.');
        $this->response->redirect('/torneos/' . $id);
    }

    public function show(Request $request): void
    {
        $id = (int) $request->input('id');
        $tournament = $this->tournaments->find($id);
        if (!$tournament) {
            flash('error', 'Torneo no encontrado.');
            $this->response->redirect('/torneos');
        }

        if (!is_admin()) {
            $allowed = false;
            foreach ($this->tournaments->players($id) as $player) {
                if ((int) $player['id'] === (int) auth_user()['id']) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                flash('error', 'No tienes acceso a este torneo.');
                $this->response->redirect('/torneos');
            }
        }

        $turnState = $this->service->getTurnState($id);
        $matches = $this->tournaments->matches($id);
        $nextPendingRound = $this->tournaments->nextPendingRound($id);

        $groupedRounds = [];
        foreach ($matches as $match) {
            $roundKey = $match['ronda_tipo'] . '-' . $match['ronda_numero'];
            if (!isset($groupedRounds[$roundKey])) {
                $groupedRounds[$roundKey] = [
                    'key' => $roundKey,
                    'tipo' => $match['ronda_tipo'],
                    'numero' => (int) $match['ronda_numero'],
                    'matches' => [],
                ];
            }
            $groupedRounds[$roundKey]['matches'][] = $match;
        }

        $activeRoundKey = null;
        if ($nextPendingRound) {
            $activeRoundKey = $nextPendingRound['ronda_tipo'] . '-' . $nextPendingRound['ronda_numero'];
        }

        $historyByPot = [];
        foreach ($turnState['history'] as $history) {
            $potName = (string) $history['bombo_nombre'];
            if (!isset($historyByPot[$potName])) {
                $historyByPot[$potName] = [];
            }
            $historyByPot[$potName][] = $history;
        }

        $drawOrder = $this->tournaments->drawOrder($id);
        $drawPositions = [];
        foreach ($drawOrder as $drawItem) {
            $drawPositions[(int) $drawItem['usuario_id']] = (int) $drawItem['posicion'];
        }

        $selectedTeamsByUser = [];
        foreach ($turnState['history'] as $history) {
            $userId = (int) $history['usuario_id'];
            if (!isset($selectedTeamsByUser[$userId])) {
                $selectedTeamsByUser[$userId] = [];
            }
            $selectedTeamsByUser[$userId][] = (string) $history['equipo_nombre'];
        }

        $selectionBoard = [];
        $currentTurnUserId = $turnState['current'] ? (int) $turnState['current']['usuario_id'] : null;
        $nextTurnUserId = $turnState['next'] ? (int) $turnState['next']['usuario_id'] : null;
        foreach ($drawOrder as $drawItem) {
            $userId = (int) $drawItem['usuario_id'];
            $teams = $selectedTeamsByUser[$userId] ?? [];

            $status = 'Pendiente';
            if (!empty($teams)) {
                $status = 'Ya eligio';
            }
            if ($nextTurnUserId !== null && $userId === $nextTurnUserId) {
                $status = 'Siguiente';
            }
            if ($currentTurnUserId !== null && $userId === $currentTurnUserId) {
                $status = 'En turno';
            }

            $selectionBoard[] = [
                'posicion' => (int) $drawItem['posicion'],
                'jugador' => (string) $drawItem['nombre'],
                'equipos' => empty($teams) ? '-' : implode(' / ', $teams),
                'estado' => $status,
            ];
        }

        $this->view->render('player/tournament_detail', [
            'title' => 'Detalle torneo',
            'tournament' => $tournament,
            'players' => $this->tournaments->players($id),
            'turnState' => $turnState,
            'groupedRounds' => array_values($groupedRounds),
            'activeRoundKey' => $activeRoundKey,
            'historyByPot' => $historyByPot,
            'drawPositions' => $drawPositions,
            'selectionBoard' => $selectionBoard,
            'pots' => $this->tournaments->pots($id),
            'standings' => $this->tournaments->standings($id),
        ]);
    }

    public function runDraw(Request $request): void
    {
        try {
            $this->service->runDraw((int) $request->input('torneo_id'));
            flash('success', 'Sorteo generado y turnos creados.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        $this->response->redirect('/torneos/' . (int) $request->input('torneo_id'));
    }

    public function chooseTeam(Request $request): void
    {
        $tournamentId = (int) $request->input('torneo_id');
        try {
            $this->service->chooseTeam($tournamentId, (int) auth_user()['id'], (int) $request->input('equipo_id'));
            flash('success', 'Equipo elegido correctamente.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        $this->response->redirect('/torneos/' . $tournamentId);
    }

    public function submitResult(Request $request): void
    {
        $matchId = (int) $request->input('partido_id');
        $tournamentId = (int) $request->input('torneo_id');

        try {
            $this->service->submitMatchResult(
                $matchId,
                (int) auth_user()['id'],
                (int) $request->input('goles_local'),
                (int) $request->input('goles_visitante')
            );
            flash('success', 'Resultado guardado.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }

        $this->response->redirect('/torneos/' . $tournamentId);
    }

    public function close(Request $request): void
    {
        $tournamentId = (int) $request->input('torneo_id');
        $result = $this->service->resolveTournamentState($tournamentId);
        flash('success', $result['message']);
        $this->response->redirect('/torneos/' . $tournamentId);
    }

    public function resolveTiebreak(Request $request): void
    {
        $tournamentId = (int) $request->input('torneo_id');
        $result = $this->service->resolveTieBreak($tournamentId);
        flash('success', $result['message']);
        $this->response->redirect('/torneos/' . $tournamentId);
    }

    private function normalizeDatetimeLocal(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value)
            ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value)
            ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i', $value);

        if (!$dateTime) {
            return null;
        }

        return $dateTime->format('Y-m-d H:i:00');
    }
}
