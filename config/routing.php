<?php

declare(strict_types=1);

use DI\Container;
use Salle\PuzzleMania\Controller\API\RiddlesAPIController;
use Salle\PuzzleMania\Controller\API\UsersAPIController;
use Salle\PuzzleMania\Controller\SignUpController;
use Salle\PuzzleMania\Controller\SignInController;
use Salle\PuzzleMania\Controller\HomeController;
use Salle\PuzzleMania\Controller\TeamsController;
use Salle\PuzzleMania\Controller\ProfileController;
use Salle\PuzzleMania\Controller\FlashController;
use Salle\PuzzleMania\Controller\GameController;

use Salle\PuzzleMania\Middleware\AuthorizationMiddleware;
use Salle\PuzzleMania\Middleware\ProfileMiddleware;
use Salle\PuzzleMania\Middleware\TeamsMiddleware;
use Salle\PuzzleMania\Middleware\GameMiddleware;

use Slim\App;

function addRoutes(App $app, Container $container): void
{
    $app->get('/', HomeController::class . ':showHome')->setName('showHome');

    // Sign in.
    $app->get('/sign-in', SignInController::class . ':showSignInForm')->setName('signIn');
    $app->post('/sign-in', SignInController::class . ':signIn');

    // Sign up.
    $app->get('/sign-up', SignUpController::class . ':showSignUpForm')->setName('signUp');
    $app->post('/sign-up', SignUpController::class . ':signUp');

    // Teams.
    $app->get('/teams', TeamsController::class . ':showTeams')->setName('teams')->add(AuthorizationMiddleware::class);
    $app->get('/show-create-team', TeamsController::class . ':showCreateTeam')->setName('showCreateTeam');
    $app->post('/create-team', TeamsController::class . ':createTeam')->setName('createTeam');
    $app->get('/joinTeam', TeamsController::class . ':joinTeam')->setName('joinTeam');
    $app->post('/joinTeamButton', TeamsController::class . ':joinTeamButton')->setName('joinTeamButton');
    $app->get('/team-stats', TeamsController::class . ':showTeamStats')->setName('showTeamStats');
    $app->get('/team-stats-QR', TeamsController::class . ':generateQR')->setName('generateQR');
    $app->get('/invite/join/{teamId}', TeamsController::class . ':test')->setName('test');

    // Profile.
    $app->get('/profile', ProfileController::class . ':showProfile')->setName('profile')->add(AuthorizationMiddleware::class);
    $app->post('/update-profile-submit', ProfileController::class . ':processProfileForm')->setName('processProfileForm');
    
    // Flash.
    $app->get('/flash-team-joined', FlashController::class . ':alreadyJoinedTeam')->setName('alreadyJoinedTeam');


    // Riddle API.
    $app->get('/riddle', RiddlesAPIController::class . ':getRiddlesAPI')->setName('getRiddlesAPI');
    $app->post('/riddle', RiddlesAPIController::class . ':addRiddleAPI')->setName('addRiddleAPI');
    $app->put('/riddle/{id}', RiddlesAPIController::class . ':updateSpecificRiddleAPI')->setName('updateSpecificRiddleAPI');
    $app->delete('/riddle/{id}', RiddlesAPIController::class . ':deleteSpecificRiddleAPI')->setName('deleteSpecificRiddleAPI');

    // Ridle API Aux.
    $app->get('/riddles', RiddlesAPIController::class . ':showListRiddles')->setName('showListRiddles')->add(AuthorizationMiddleware::class);;
    $app->get('/riddles/{id}', RiddlesAPIController::class . ':showSpecificRiddle')->setName('showSpecificRiddle');
    
    // Game
    $app->get('/game', GameController::class . ':showGame')->setName('showGame')->add(AuthorizationMiddleware::class);;
    $app->post('/game', GameController::class . ':startGame')->setName('startGame');
    $app->get('/game/{gameId}/riddle/{riddleId}', GameController::class . ':showRiddle')->setName('showRiddle');
    $app->post('/game/{gameId}/riddle/{riddleId}', GameController::class . ':submitAnswer')->setName('submitAnswer');
}
?>