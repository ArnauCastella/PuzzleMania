<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller\API;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Service\ValidatorService;
use Salle\PuzzleMania\Repository\RiddleRepository;
use Salle\PuzzleMania\Model\Riddle;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class RiddlesAPIController {
    private ValidatorService $validator;

    public function __construct(
        private Twig           $twig,
        private RiddleRepository $riddleRepository,
        private Messages       $flash
    )
    {
        $this->validator = new ValidatorService();
    }

    public function showListRiddles(Request $request, Response $response): Response {
        // Get all the riddles.
        $riddlesJson = $this->riddleRepository->getAllRiddles();
        
        
        // Convert the JSON array to a PHP array
        $riddleArray = json_decode($riddlesJson, true);

        // Create an empty array to hold the Riddle objects
        $riddles = array();

        // Loop through the JSON array and create a Riddle object for each item
        foreach ($riddleArray as $riddleObject) {
            $riddle = new Riddle();
            $riddle->setRiddleId($riddleObject['riddle_id']);
            $riddle->setUserId($riddleObject['user_id']);
            $riddle->setRiddle($riddleObject['riddle']);
            $riddle->setAnswer($riddleObject['answer']);
            $riddles[] = $riddle;
        }

        return $this->twig->render($response, 'riddlesList.twig',
        [
            'riddles' => $riddles,
            'signed_in' => true
        ]);
    }

    public function addRiddleAPI(Request $request, Response $response): Response {
        // Get the form data as an associative array
        $jsonData = file_get_contents('php://input');

        // Decode the JSON into an associative array
        $jsonDataAux = json_decode($jsonData, true);

        if (!empty($jsonDataAux['user_id']) && !empty($jsonDataAux['riddle']) && !empty($jsonDataAux['answer'])) {
            // Execute query.
            $this->riddleRepository->createRiddleAPI($jsonData);

            // Create an array and add the decoded JSON variable
            $jsonArray = array($jsonDataAux);

            // Encode the array into a JSON string
            $result = json_encode($jsonArray);

            $response->getBody()->write($result);
                return $response->withHeader('Content-Type', 'application/json');
        } else {
            $data = array(
                'message' => "'riddle' and/or 'answer' and/or 'userId' key missing"
            );
            
            $jsonString = json_encode($data);

            $response->getBody()->write($jsonString);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function getRiddlesAPI(Request $request, Response $response): Response {
        // Get all the riddles.
        $riddlesJson = $this->riddleRepository->getAllRiddles();
        


        // Decode the JSON string into a PHP array
        $data = json_decode($riddlesJson, true);

        if (!empty($data)) {
            $response->getBody()->write($riddlesJson);
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $data = array(
                'message' => "'riddle' and/or 'answer' and/or 'userId' key missing"
            );
            
            $jsonString = json_encode($data);

            $response->getBody()->write($jsonString);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        /*
        // Convert the JSON array to a PHP array
        $riddleArray = json_decode($riddlesJson, true);

        // Create an empty array to hold the Riddle objects
        $riddles = array();

        // Loop through the JSON array and create a Riddle object for each item
        foreach ($riddleArray as $riddleObject) {
            $riddle = new Riddle();
            $riddle->setRiddleId($riddleObject['riddle_id']);
            $riddle->setUserId($riddleObject['user_id']);
            $riddle->setRiddle($riddleObject['riddle']);
            $riddle->setAnswer($riddleObject['answer']);
            $riddles[] = $riddle;
        }
        */
    }

    public function showSpecificRiddle(Request $request, Response $response): Response {
        // Getting URL extension.
        $currentUrl = $_SERVER['REQUEST_URI']; // Getting /riddles/X

        // Retrieven Riddle ID.
        $pattern = "/\/[^\/]+\/(.*)/";
        preg_match($pattern, $currentUrl, $matches);
        $riddle_id = $matches[1];

        // API Call.
        $riddleJSON = $this->riddleRepository->getRiddleById($riddle_id);

        $riddleJson = json_decode($riddleJSON, true);

        $riddle = new Riddle();
        $riddle->setRiddleId($riddleJson[0]['riddle_id']);
        $riddle->setUserId($riddleJson[0]['user_id']);
        $riddle->setRiddle($riddleJson[0]['riddle']);
        $riddle->setAnswer($riddleJson[0]['answer']);
        
        return $this->twig->render($response, 'riddlesListSpecific.twig', [
            'riddle' => $riddle,
            'signed_in' => true
        ]);

    }

    public function updateSpecificRiddleAPI(Request $request, Response $response): Response {
        // Retrieve the request body JSON
        $requestBody = file_get_contents('php://input');

        // Decode the JSON into an associative array
        $jsonData = json_decode($requestBody, true);

        // URL
        $url = $_SERVER['REQUEST_URI'];

        // Define the pattern using regex to extract the dynamic value from the URL
        $pattern = '/\/riddle\/(\d+)/';

        // Perform the regex match
        preg_match($pattern, $url, $matches);

        // Extract the captured value
        $extractedValue = $matches[1];

        if (empty($jsonData['riddle']) || empty($jsonData['answer'])) {
            $data = array(
                'message' => "The riddle and/or answer cannot be empty"
            );
    
            $data = json_encode($data);
    
            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } else if (count(json_decode($this->riddleRepository->getRiddleById($extractedValue))) == 0) {
            $data = array(
                'message' => "Riddle with id " .$extractedValue. " does not exist"
            );
    
            $data = json_encode($data);
    
            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } else {
            // Add the extracted value to the JSON as "riddle_id"
            $jsonData['riddle_id'] = $extractedValue;

            // Convert the updated data back to JSON string
            $updatedJsonString = json_encode($jsonData);

            $this->riddleRepository->updateSpecificRiddleAPI($updatedJsonString);

            $data = array(
                'message' => "Riddle has been correctly updated."
            );

            $data = json_encode($data);

            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }

    public function deleteSpecificRiddleAPI(Request $request, Response $response): Response {
        // Retrieve the request body JSON
        $requestBody = file_get_contents('php://input');

        // Decode the JSON into an associative array
        $jsonData = json_decode($requestBody, true);

        // URL
        $url = $_SERVER['REQUEST_URI'];

        // Define the pattern using regex to extract the dynamic value from the URL
        $pattern = '/\/riddle\/(\d+)/';

        // Perform the regex match
        preg_match($pattern, $url, $matches);

        // Extract the captured value
        $extractedValue = $matches[1];

        if (count(json_decode($this->riddleRepository->getRiddleById($extractedValue))) == 0) {
            $data = array(
                'message' => "Riddle with id " .$extractedValue. " does not exist"
            );
    
            $data = json_encode($data);
    
            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } else {
            $this->riddleRepository->deleteSpecificRiddleAPI($extractedValue);

            $data = array(
                'message' => "Riddle has been correctly deleted."
            );

            $data = json_encode($data);

            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }    
}

?>