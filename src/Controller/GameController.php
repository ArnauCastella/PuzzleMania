<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Repository\UserRepository;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class GameController
{
 

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private Messages       $flash,
        private RiddleRepository $riddleRepository
    )
    {
    }

    public function showGame(Request $request, Response $response): Response
    {
        if (!$this->userRepository->checkIfJoinedTeam($_SESSION['email'])) {
            $this->flash->addMessage(
                'notifications',
                'You need to join a team!'
            );

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();

            return $response->withHeader('Location', $routeParser->urlFor("joinTeam"))->withStatus(302);
        } else {
            $teamUserArray = $this->userRepository->getTeamInfo($_SESSION['email']);
            $teamName = $teamUserArray[0]->getTeamName();

            if (isset($_SESSION['user_id'])) {
                $signed_in = true;
            } else {
                $signed_in = false;
            }

            return $this->twig->render(
                $response,
                'game.twig',
                [
                    'teamName' => $teamName,
                    'signed_in' => $signed_in
                ]
            );
        }
    }

    public function startGame(Request $request, Response $response): Response
    {
        // Create game id, store 3 riddles and redirect to /game/{gameId}/riddle/1
        $riddles = json_decode($this->riddleRepository->getAllRiddles());

        $randomIndexes = [];
        while (count($randomIndexes) < 3) {
            $randomIndex = rand(0, count($riddles) - 1);
            
            if (!in_array($randomIndex, $randomIndexes)) {
                $randomIndexes[] = $randomIndex;
            }
        }
        $gameId = $this->riddleRepository->createGame($riddles[$randomIndexes[0]]->riddle_id, $riddles[$randomIndexes[1]]->riddle_id, $riddles[$randomIndexes[2]]->riddle_id);
        $_SESSION['score'] = 10;
        $url = "http://localhost:8030/game/".$gameId."/riddle/1";

        return $response->withHeader('Location', $url)->withStatus(302);
    }

    public function showRiddle(Request $request, Response $response): Response
    {
        $gameId = $request->getAttribute('gameId');
        $riddleNum = $request->getAttribute('riddleId');
        $riddle = json_decode($this->riddleRepository->getNextRiddle(intval($gameId), intval($riddleNum)), true)[0];

        return $this->twig->render(
            $response,
            'gameRiddle.twig',
            [
                'riddleNum' => $riddleNum,
                'riddleText' => $riddle['riddle'],
                'signed_in' => true
            ]
        );        
    }

    public function submitAnswer(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $gameId = $request->getAttribute('gameId');
        $riddleNum = $request->getAttribute('riddleId');
        $riddle = json_decode($this->riddleRepository->getNextRiddle(intval($gameId), intval($riddleNum)), true)[0];

        $control = [];
        $control['newUrl'] = "http://localhost:8030/game/".$gameId."/riddle/".$riddleNum+1;
        if ($data['answer'] != $riddle['answer']) {
            $_SESSION['score'] -= 10;
            $control['correctAnswer'] = "Correct answer: ".$riddle['answer'];
        } else {
            $_SESSION['score'] += 10;            
        }
        if ($_SESSION['score'] <= 0 || $riddleNum == 3) {
            $control['finish'] = 1;
            $control['score'] = $_SESSION['score'];
            if ($_SESSION['score'] > 0){
                $this->userRepository->saveTeamScore();
            }
        } else {
            $control['next'] = 1;
        }
        return $this->twig->render(
            $response,
            'gameRiddle.twig',
            [
                'riddleNum' => $riddleNum,
                'riddleText' => $riddle['riddle'],
                'control' => $control,
                'signed_in' => true
            ]
        ); 
    }
}