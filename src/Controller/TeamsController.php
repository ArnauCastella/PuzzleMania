<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Service\ValidatorService;
use Salle\PuzzleMania\Repository\UserRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;
use Salle\PuzzleMania\Model\Team;

class TeamsController
{
    private ValidatorService $validator;
    private bool $formVisible = false;

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private Messages       $flash
    )
    {
        $this->validator = new ValidatorService();
    }

    public function showTeams(Request $request, Response $response): Response {
        if ($this->userRepository->checkIfJoinedTeam($_SESSION['email'])) {
            $this->flash->addMessage(
                'notifications',
                'You already joined a team!'
            );

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();

            return $response->withHeader('Location', $routeParser->urlFor("showTeamStats"))->withStatus(302);
        } else {
            $myArray = $this->userRepository->getEmptyTeams();

            return $this->twig->render($response, 'joinTeam.twig', [
                "myArray" => $myArray,
                'signed_in' => true
            ]);
            //return $this->twig->render($response, 'teams.twig');
        }
    }

    public function showCreateTeam(Request $request, Response $response): Response {
        return $this->twig->render($response, 'createTeam.twig', ['signed_in' => true]);
    }

    public function createTeam(Request $request, Response $response): Response {

        $team_name = $_POST['teamName'];
        
        // DB CHECK
        // Check if team name is correct, then if exists in database.
        if (preg_match('/^[a-zA-Z0-9]+$/', $team_name)) {
            if (!($this->userRepository->checkTeamExists($team_name))) {
                $this->userRepository->createTeam($team_name, $_SESSION['email']);
                $_SESSION['team_name'] = $team_name;
            } else{
            }
        } else {
            echo "The team name is not alphanumeric.";
        }
        

        return $this->twig->render($response, 'home.twig', ['signed_in' => true]);
    }

    public function joinTeam(Request $request, Response $response): Response {
        $myArray = $this->userRepository->getEmptyTeams();

        return $this->twig->render($response, 'joinTeam.twig', [
            "myArray" => $myArray,
            'signed_in' => true
        ]);
    }

    public function joinTeamButton(Request $request, Response $response): Response {

        #echo "You wanted to join the team with name:" .$_POST['teamName'];

        $myArray = $this->userRepository->joinTeam($_POST['teamName'], $_SESSION['email']);

        $_SESSION['team_name'] = $_POST['teamName'];

        return $this->twig->render($response, 'home.twig', ['signed_in' => true]);
    }

    public function showTeamStats(Request $request, Response $response): Response {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        /*
            <p>Name of the Team: {{info['teamName']}}<p>
            <p>Number of players: {{info['numberOfTeamPlayers']}}<p>
            <p>Players: {{info['numberOfTeamPlayers']}}<p>
            <p>Points from Last Game: {{info['points']}}<p>
        */

        $teamUserArray = $this->userRepository->getTeamInfo($_SESSION['email']);

        $required_info['teamName'] = $teamUserArray[0]->getTeamName();

        // Finding users.
        $users = [];

        $parts = explode('@', $teamUserArray[0]->getUserEmail());

        $username = $parts[0];

        $users[0] = $username;

        // Finding number of users.
        if (!empty($teamUserArray[0]->getUserEmail()) && !empty($teamUserArray[0]->getUserEmail2())) {
            $required_info['numberOfTeamPlayers'] = 2;
            $parts = explode('@', $teamUserArray[0]->getUserEmail2());

            $username = $parts[0];

            $users[1] = $username;
        } else {
            $required_info['numberOfTeamPlayers'] = 1;
        }

        //$required_info['numberOfTeamPlayers'] = count($teamUserArray);
        $required_info['points'] = $teamUserArray[0]->getLastPoints();

        $qr = file_exists('uploads/'.$_SESSION['team_name'].'.png');

        return $this->twig->render(
            $response,
            'teamStats.twig',
            [
                'notifications' => $notifications,
                'info' => $required_info,
                'users' => $users,
                'qr_generated' => $qr,
                'signed_in' => true
            ]
        );
    }

    public function generateQR(Request $request, Response $response): Response {

        $team_id = $this->userRepository->getTeamID($_SESSION['email']);

        $url = "http://localhost:8030/invite/join/".$team_id;

        $data = array(
            'symbology' => 'QRCode',
            'code' => $url
        );
        
        $options = array(
        'http' => array(
            'method'  => 'POST',
            'content' => json_encode( $data ),
            'header' =>  "Content-Type: application/json\r\n" .
                        "Accept: image/png\r\n"
            )
        );

        $context  = stream_context_create( $options );
        $url = 'http://pw_barcode/BarcodeGenerator';
        $resp = file_get_contents( $url, false, $context );
        file_put_contents('uploads/'.$_SESSION['team_name'].'.png', $resp);

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        return $response->withHeader('Location', $routeParser->urlFor("showTeamStats"))->withStatus(302);
    }

    public function test(Request $request, Response $response): Response {

        return $this->twig->render($response, 'teamStats.twig', ['signed_in' => true]);
    }
}
