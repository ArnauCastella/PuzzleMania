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

# 

class HomeController
{
    private ValidatorService $validator;

    public function __construct(
        private Twig           $twig,
        private UserRepository $userRepository,
        private Messages       $flash
    )
    {
        $this->validator = new ValidatorService();
    }


    public function showHome(Request $request, Response $response): Response
    {
        if (isset($_SESSION['user_id'])) {
            $signed_in = true;
        } else {
            $signed_in = false;
        }
        return $this->twig->render(
            $response, 
            'home.twig',
            [
                'signed_in' => $signed_in
            ]
        );
    }
}
