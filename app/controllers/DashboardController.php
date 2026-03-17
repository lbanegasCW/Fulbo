<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\TournamentRepository;

class DashboardController extends BaseController
{
    public function index(Request $request): void
    {
        $user = auth_user();
        $repo = new TournamentRepository();
        $nextTournament = is_admin()
            ? $repo->nextScheduledGlobal()
            : $repo->nextScheduledForUser((int) $user['id']);

        $this->view->render('player/dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'nextTournament' => $nextTournament,
        ]);
    }
}
