<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\TournamentRepository;

class RankingController extends BaseController
{
    public function index(Request $request): void
    {
        $repo = new TournamentRepository();
        $years = $repo->rankingYears();
        $years = array_values(array_unique(array_merge($years, [2025, 2024])));
        rsort($years);

        $year = $request->input('anio') ? (int) $request->input('anio') : null;
        if ($year === null && !empty($years)) {
            $year = (int) $years[0];
        }

        $ranking = $repo->annualRanking($year);
        $hardcoded = $this->hardcodedRanking($year);
        if ($hardcoded !== null) {
            $ranking = $hardcoded;
        }

        foreach ($ranking as &$row) {
            $row['items'] = [];
            if (!empty($row['detalle'])) {
                foreach (explode(';', $row['detalle']) as $item) {
                    [$name, $itemYear, $closeDate, $participants] = array_pad(explode('|', $item), 4, '');
                    $row['items'][] = [
                        'name' => $name,
                        'year' => $itemYear,
                        'close_date' => $closeDate,
                        'participants' => $participants,
                    ];
                }
            }
        }

        $this->view->render('player/ranking', [
            'title' => 'Ranking anual',
            'ranking' => $ranking,
            'selectedYear' => $year,
            'years' => $years,
        ]);
    }

    private function hardcodedRanking(?int $year): ?array
    {
        if ($year === 2024) {
            return [
                ['id' => 0, 'nombre' => 'Maxi', 'campeonatos' => 20, 'items' => []],
                ['id' => 0, 'nombre' => 'Luis', 'campeonatos' => 8, 'items' => []],
                ['id' => 0, 'nombre' => 'Nico', 'campeonatos' => 6, 'items' => []],
                ['id' => 0, 'nombre' => 'Turko', 'campeonatos' => 3, 'items' => []],
                ['id' => 0, 'nombre' => 'German', 'campeonatos' => 1, 'items' => []],
            ];
        }

        if ($year === 2025) {
            return [
                ['id' => 0, 'nombre' => 'Maxi', 'campeonatos' => 6, 'items' => []],
                ['id' => 0, 'nombre' => 'Nico', 'campeonatos' => 6, 'items' => []],
                ['id' => 0, 'nombre' => 'Luis', 'campeonatos' => 5, 'items' => []],
            ];
        }

        return null;
    }
}
