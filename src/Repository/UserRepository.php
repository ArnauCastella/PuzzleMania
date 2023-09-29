<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\User;

interface UserRepository
{
    public function createUser(User $user): void;
    public function getUserByEmail(string $email);
    public function getUserById(int $id);
    public function getAllUsers();

    public function getEmptyTeams();
    public function checkTeamExists(string $team_name): bool;
    public function createTeam(string $team_name, string $user_email): void;
    public function joinTeam(string $team_name, string $user_email): void;
    public function checkIfJoinedTeam(string $user_email): bool;
    public function getTeamInfo(string $user_email);
    public function saveTeamScore();
}
