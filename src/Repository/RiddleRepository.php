<?php

declare(strict_types=1);

namespace Salle\PuzzleMania\Repository;

use Salle\PuzzleMania\Model\Riddle;

interface RiddleRepository
{
    public function createRiddleAPI(string $create_riddle_json);
    public function getAllRiddles();
    public function getRiddleById(string $riddle_id);
    public function updateSpecificRiddleAPI(string $update_riddle_json);

    public function createGame(int $riddle1, int $riddle2, int $riddle3);
    public function getNextRiddle(int $gameId, int $urlNum);
}
