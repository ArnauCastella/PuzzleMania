<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use PDO;
use Salle\PuzzleMania\Model\User;
use Salle\PuzzleMania\Model\Team;

final class MySQLUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDO $databaseConnection;

    public function __construct(PDO $database)
    {
        $this->databaseConnection = $database;
    }

    public function createUser(User $user): void
    {
        $query = <<<'QUERY'
        INSERT INTO users(email, password, coins, createdAt, updatedAt)
        VALUES(:email, :password, :coins, :createdAt, :updatedAt)
        QUERY;

        $queryWithoutCoins = <<<'QUERY'
        INSERT INTO users(email, password, createdAt, updatedAt)
        VALUES(:email, :password, :createdAt, :updatedAt)
        QUERY;

        $email = $user->email();
        $password = $user->password();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);
        $updatedAt = $user->updatedAt()->format(self::DATE_FORMAT);

        if (empty($coins)) $query = $queryWithoutCoins;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('createdAt', $createdAt, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getUserByEmail(string $email)
    {
        $query = <<<'QUERY'
        SELECT * FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $row;
        }
        return null;
    }

    public function getUserById(int $id)
    {
        $query = <<<'QUERY'
        SELECT * FROM users WHERE id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_INT);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_OBJ);
            return $row;
        }
        return null;
    }

    public function getAllUsers()
    {
        $query = <<<'QUERY'
        SELECT * FROM users
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        $users = [];

        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();

            for ($i = 0; $i < $count; $i++) {
                $user = User::create()
                    ->setId(intval($rows[$i]['id']))
                    ->setEmail($rows[$i]['email'])
                    //->setPassword($rows[$i]['password']) - don't ever expose pswd!!!!
                    ->setCreatedAt(date_create_from_format('Y-m-d H:i:s', $rows[$i]['createdAt']))
                    ->setUpdatedAt(date_create_from_format('Y-m-d H:i:s', $rows[$i]['updatedAt']));
                $users[] = $user;
            }
        }
        return $users;
    }

    public function checkTeamExists(string $team_name): bool {

        $query = <<<'QUERY'
        SELECT team_id FROM teams WHERE team_name = :team_name
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam(':team_name', $team_name, PDO::PARAM_STR);
        $statement->execute();

        $count = $statement->rowCount();
        
        return ($count > 0);
    }

    public function getEmptyTeams()
    {
        $query = <<<'QUERY'
        SELECT team_name FROM teams WHERE user_email2 is null GROUP BY team_name
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->execute();

        $teams = [];

        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            //print_r($rows);
            for ($i = 0; $i < $count; $i++) {
                $team = Team::create()
                    ->setTeamName($rows[$i]['team_name'])
                    ->setParticipantsNum(1);
                $teams[] = $team;
            }
        }
        return $teams;
    }

    public function createTeam(string $team_name, string $user_email): void
    {
        $query = <<<'QUERY'
        INSERT INTO teams(team_name, user_email)
        VALUES(:team_name, :user_email)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':team_name', $team_name, PDO::PARAM_STR);
        $statement->bindParam('user_email', $user_email, PDO::PARAM_STR);

        $statement->execute();
    }

    public function joinTeam(string $team_name, string $user_email): void
    {
        $query = <<<'QUERY'
        UPDATE teams
        SET user_email2 = :user_email
        WHERE team_name = :team_name
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':team_name', $team_name, PDO::PARAM_STR);
        $statement->bindParam(':user_email', $user_email, PDO::PARAM_STR);

        $statement->execute();
    }

    public function checkIfJoinedTeam(string $user_email): bool {

        $query = <<<'QUERY'
        SELECT team_id FROM teams WHERE (user_email = :user_email or user_email2 = :user_email)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam(':user_email', $user_email, PDO::PARAM_STR);
        $statement->execute();

        $count = $statement->rowCount();
        
        return ($count > 0);
    }

    public function getTeamInfo(string $user_email){
        $query = <<<'QUERY'
        SELECT team_name, user_email, user_email2, points_last_game 
        FROM teams 
        WHERE team_name LIKE (SELECT team_name FROM teams WHERE (user_email like :user_email or user_email2 like :user_email))
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':user_email', $user_email, PDO::PARAM_STR);

        $statement->execute();

        $teams = [];

        $count = $statement->rowCount();
        if ($count > 0) {
            $rows = $statement->fetchAll();
            for ($i = 0; $i < $count; $i++) {
                $team = Team::create()
                    ->setTeamName($rows[$i]['team_name'])
                    ->setUserEmail($rows[$i]['user_email'])
                    ->setLastPoints($rows[$i]['points_last_game']);
                    if ($rows[$i]['user_email2'] != null) {
                        $team->setUserEmail2($rows[$i]['user_email2']);
                    }
                $teams[] = $team;
            }
        }
        return $teams;
    }

    public function getTeamID(string $user_email){
        $query = <<<'QUERY'
        SELECT team_id 
        FROM teams WHERE (user_email like :user_email or user_email2 like :user_email)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':user_email', $user_email, PDO::PARAM_STR);

        $statement->execute();

        $team_id = $statement->fetchColumn();

        return $team_id;
    }

    public function saveTeamScore(){
        $teamId = $this->getTeamID($_SESSION['email']);
        
        $query = <<<'QUERY'
        UPDATE teams
        SET points_last_game = :score
        WHERE team_id = :team_id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam(':team_id', $teamId, PDO::PARAM_INT);
        $statement->bindParam(':score', $_SESSION['score'], PDO::PARAM_INT);

        $statement->execute();
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

        // Convert each row to a JSON array
        $jsonArrays = array();
        foreach ($rows as $row) {
            $jsonArrays[] = json_encode($row);
        }

        // Return the JSON arrays as a single JSON string
        return json_encode($jsonArrays);
    }


    public function createRiddle(string $create_riddle_json){
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
}
