<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\PotRepository;
use App\Repositories\TeamRepository;

class AdminPotController extends BaseController
{
    private PotRepository $pots;
    private TeamRepository $teams;

    public function __construct()
    {
        parent::__construct();
        $this->pots = new PotRepository();
        $this->teams = new TeamRepository();
    }

    public function index(Request $request): void
    {
        $potId = (int) ($request->input('bombo_id') ?? 0);
        $selectedPot = $potId > 0 ? $this->pots->find($potId) : null;
        $teams = $selectedPot ? $this->teams->byPot($potId) : [];

        $this->view->render('admin/pots', [
            'title' => 'Bombos y equipos',
            'pots' => $this->pots->all(),
            'selectedPot' => $selectedPot,
            'teams' => $teams,
        ]);
    }

    public function storePot(Request $request): void
    {
        $this->pots->create([
            'nombre' => trim((string) $request->input('nombre')),
            'descripcion' => trim((string) $request->input('descripcion')),
            'activo' => $request->input('activo') ? 1 : 0,
        ]);
        flash('success', 'Bombo creado.');
        $this->response->redirect('/admin/bombos');
    }

    public function updatePot(Request $request): void
    {
        $this->pots->update((int) $request->input('id'), [
            'nombre' => trim((string) $request->input('nombre')),
            'descripcion' => trim((string) $request->input('descripcion')),
            'activo' => $request->input('activo') ? 1 : 0,
        ]);
        flash('success', 'Bombo actualizado.');
        $this->response->redirect('/admin/bombos');
    }

    public function storeTeam(Request $request): void
    {
        $bomboId = (int) $request->input('bombo_id');
        $this->teams->create([
            'bombo_id' => $bomboId,
            'nombre' => trim((string) $request->input('nombre')),
            'abreviatura' => strtoupper(trim((string) $request->input('abreviatura'))),
            'activo' => $request->input('activo') ? 1 : 0,
        ]);
        flash('success', 'Equipo agregado al bombo.');
        $this->response->redirect('/admin/bombos?bombo_id=' . $bomboId);
    }

    public function updateTeam(Request $request): void
    {
        $team = $this->teams->find((int) $request->input('id'));
        if (!$team) {
            flash('error', 'Equipo no encontrado.');
            $this->response->redirect('/admin/bombos');
        }

        $this->teams->update((int) $team['id'], [
            'nombre' => trim((string) $request->input('nombre')),
            'abreviatura' => strtoupper(trim((string) $request->input('abreviatura'))),
            'activo' => $request->input('activo') ? 1 : 0,
        ]);
        flash('success', 'Equipo actualizado.');
        $this->response->redirect('/admin/bombos?bombo_id=' . (int) $team['bombo_id']);
    }
}
