<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\Riddle;
use DateTime;

final class MySQLRiddleRepository implements RiddleRepository
{
    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    public function getAllRiddles(){
        // Convert the JSON string to an associative array
        $query = <<<'QUERY'
        SELECT * FROM riddles
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        // Fetch all rows as an associative array
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $riddles = array();
        foreach ($rows as $row) {
            $riddle = new Riddle();
            $riddle->setRiddleId($row['riddle_id']);
            $riddle->setUserId($row['user_id']);
            $riddle->setRiddle($row['riddle']);
            $riddle->setAnswer($row['answer']);
            $riddles[] = $riddle;
        }

        // Return the array of Riddle objects as a JSON string
        return json_encode($riddles);
    }


    public function createRiddleAPI(string $create_riddle_json){
        // Convert the JSON string to an associative array
        $formData = json_decode($create_riddle_json, true);

        $user_id = $formData['user_id'];
        $riddle = $formData['riddle'];
        $answer = $formData['answer'];

        $query = <<<'QUERY'
        INSERT INTO riddles(user_id, riddle, answer)
        VALUES(:user_id, :riddle, :answer)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $statement->bindParam(':riddle', $riddle, PDO::PARAM_STR);
        $statement->bindParam(':answer', $answer, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getRiddleById(string $riddle_id){
        // Convert the JSON string to an associative array
        $query = <<<'QUERY'
        SELECT * FROM riddles WHERE riddle_id = :riddle_id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':riddle_id', $riddle_id, PDO::PARAM_STR);

        $statement->execute();

        // Fetch all rows as an associative array
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $riddles = array();
        foreach ($rows as $row) {
            $riddle = new Riddle();
            $riddle->setRiddleId($row['riddle_id']);
            $riddle->setUserId($row['user_id']);
            $riddle->setRiddle($row['riddle']);
            $riddle->setAnswer($row['answer']);
            $riddles[] = $riddle;
        }

        // Return the array of Riddle objects as a JSON string
        return json_encode($riddles);
    }

    public function updateSpecificRiddleAPI(string $jsonUpdateRiddle){
        // Decode the updated JSON string back into an associative array
        $jsonUpdateRiddle = json_decode($jsonUpdateRiddle, true);

        // Convert the JSON string to an associative array
        $query = <<<'QUERY'
        UPDATE riddles
        SET riddle_id = :riddle_id, user_id = :user_id, riddle = :riddle, answer = :answer
        WHERE riddle_id = :riddle_id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':riddle_id', $jsonUpdateRiddle['riddle_id'], PDO::PARAM_STR);
        $statement->bindParam(':user_id', $jsonUpdateRiddle['user_id'], PDO::PARAM_STR);
        $statement->bindParam(':riddle', $jsonUpdateRiddle['riddle'], PDO::PARAM_STR);
        $statement->bindParam(':answer', $jsonUpdateRiddle['answer'], PDO::PARAM_STR);

        $statement->execute();
    }

    public function deleteSpecificRiddleAPI(string $riddle_id){
        // Convert the JSON string to an associative array
        $query = <<<'QUERY'
            DELETE FROM riddles WHERE riddle_id = :riddle_id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':riddle_id', $riddle_id, PDO::PARAM_STR);

        $statement->execute();
    }

    public function createGame(int $riddle1, int $riddle2, int $riddle3): int {
        $query = <<<'QUERY'
        INSERT INTO games(riddle1, riddle2, riddle3)
        VALUES(:riddle1, :riddle2, :riddle3)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':riddle1', $riddle1, PDO::PARAM_STR);
        $statement->bindParam(':riddle2', $riddle2, PDO::PARAM_STR);
        $statement->bindParam(':riddle3', $riddle3, PDO::PARAM_STR);

        $statement->execute();

        $query = <<<'QUERY'
        SELECT game_id FROM games WHERE riddle1 = :riddle1 AND riddle2 = :riddle2 AND riddle3 = :riddle3 
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':riddle1', $riddle1, PDO::PARAM_INT);
        $statement->bindParam(':riddle2', $riddle2, PDO::PARAM_INT);
        $statement->bindParam(':riddle3', $riddle3, PDO::PARAM_INT);

        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $rows[count($rows)-1]['game_id']; 
    }

    public function getNextRiddle(int $gameId, int $urlNum) {
        if ($urlNum == 1) {
            $query = <<<'QUERY'
            SELECT riddle1 FROM games WHERE game_id = :game_id
            QUERY;
        } else if ($urlNum == 2) {
            $query = <<<'QUERY'
            SELECT riddle2 FROM games WHERE game_id = :game_id
            QUERY;
        } else {
            $query = <<<'QUERY'
            SELECT riddle3 FROM games WHERE game_id = :game_id
            QUERY;
        }
        
        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':game_id', $gameId, PDO::PARAM_INT);

        $statement->execute();
        
        $riddleId = $statement->fetchAll(PDO::FETCH_ASSOC)[0]['riddle'.$urlNum];
        return $this->getRiddleById(strval($riddleId));
    }
}
