<?php

declare(strict_types=1);

use App\Controllers\AdminPotController;
use App\Controllers\AdminUserController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\RankingController;
use App\Controllers\TournamentController;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\GuestMiddleware;

/** @var App\Core\Router $router */

$router->get('/', [AuthController::class, 'loginForm'], [GuestMiddleware::class]);
$router->get('/login', [AuthController::class, 'loginForm'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class, CsrfMiddleware::class]);

$router->get('/activar', [AuthController::class, 'activateForm'], [GuestMiddleware::class]);
$router->post('/activar', [AuthController::class, 'activate'], [GuestMiddleware::class, CsrfMiddleware::class]);

$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

$router->get('/torneos', [TournamentController::class, 'index'], [AuthMiddleware::class]);
$router->get('/torneos/{id}', [TournamentController::class, 'show'], [AuthMiddleware::class]);
$router->post('/torneos/elegir-equipo', [TournamentController::class, 'chooseTeam'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/torneos/cargar-resultado', [TournamentController::class, 'submitResult'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/torneos/cerrar', [TournamentController::class, 'close'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/torneos/desempate/resolver', [TournamentController::class, 'resolveTiebreak'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/ranking', [RankingController::class, 'index'], [AuthMiddleware::class]);

$router->get('/admin/usuarios', [AdminUserController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/usuarios/crear', [AdminUserController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/usuarios/editar', [AdminUserController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/admin/bombos', [AdminPotController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/bombos/crear', [AdminPotController::class, 'storePot'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/bombos/editar', [AdminPotController::class, 'updatePot'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/equipos/crear', [AdminPotController::class, 'storeTeam'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/equipos/editar', [AdminPotController::class, 'updateTeam'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/admin/torneos/nuevo', [TournamentController::class, 'createForm'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/torneos/{id}/editar', [TournamentController::class, 'editForm'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/torneos/crear', [TournamentController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/torneos/editar', [TournamentController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/torneos/sortear', [TournamentController::class, 'runDraw'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
