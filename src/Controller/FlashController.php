<?php
    declare(strict_types=1);

    namespace Salle\PuzzleMania\Controller;

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;
    use Slim\Flash\Messages;
    use Slim\Routing\RouteContext;
    use Slim\Views\Twig;

    final class FlashController {

        // Fixed.
        public function __construct(private Twig $twig, private Messages $flash) {

        }

        public function alreadyJoinedTeam(Request $request, Response $response): Response {
            $this->flash->addMessage(
                'notifications',
                'You already joined a team!'
            );

            echo "Hello from flash controller!.";

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();

            return $response->withHeader('Location', $routeParser->urlFor("showTeamStats"))->withStatus(302);
        }

    }
?>