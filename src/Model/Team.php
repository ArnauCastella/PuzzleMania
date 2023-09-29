<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Model;

use DateTime;
use JsonSerializable;

class Team implements JsonSerializable
{

    private int $team_id = 0;
    private string $team_name = "";
    private string $user_email = "";
    private string $user_email2 = "";
    private int $last_game_points = 0;
    private int $participantsNum = 0;

//    public function __construct(
//        string   $email,
//        string   $password,
//        Datetime $createdAt,
//        Datetime $updatedAt
//    )
//    {
//        $this->email = $email;
//        $this->password = $password;
//        $this->createdAt = $createdAt;
//        $this->updatedAt = $updatedAt;
//    }

    /**
     * Static constructor / factory
     */
    public static function create(): Team
    {
        return new self();
    }

    /**
     * Function called when encoded with json_encode
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function getTeamId()
    {
        return $this->team_id;
    }

    public function getTeamName()
    {
        return $this->team_name;
    }

    public function getUserEmail()
    {
        return $this->user_email;
    }

    public function getUserEmail2()
    {
        return $this->user_email2;
    }

    public function getLastPoints()
    {
        return $this->last_game_points;
    }

    public function getParticipantsNum()
    {
        return $this->participantsNum;
    }

    public function setTeamId(int $id)
    {
        $this->team_id = $id;
        return $this;
    }

    public function setTeamName(string $team_name)
    {
        $this->team_name = $team_name;
        return $this;
    }

    public function setUserEmail(string $user_email)
    {
        $this->user_email = $user_email;
        return $this;
    }
    public function setUserEmail2(string $user_email2)
    {
        $this->user_email2 = $user_email2;
        return $this;
    }

    public function setLastPoints(int $last_game_points)
    {
        $this->last_game_points = $last_game_points;
        return $this;
    }

    public function setParticipantsNum(int $participantsNum)
    {
        $this->participantsNum = $participantsNum;
        return $this;
    }
}
