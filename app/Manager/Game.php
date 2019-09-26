<?php

namespace App\Manager;

use App\Models\Map;
use App\Models\Player;

class Game
{
    /**
     * 游戏地图
     * @var Map
     */
    private $gameMap = [];

    /**
     * 游戏玩家
     * @var array<Player>
     */
    private $players = [];

    /**
     * Game constructor.
     */
    public function __construct()
    {
        $this->gameMap = new Map(12, 12);
    }

    public function createPlayer($playerId, $x, $y)
    {
        $player = new Player($playerId, $x, $y);
        if (!empty($this->players)) {
            $player->setType(Player::PLAYER_TYPE_HIDE);
        }

        $this->players[$playerId] = $player;
    }

    public function playerMove($playerId, $direction)
    {
        $player = $this->players[$playerId];

        if ($this->canMoveToDirection($player, $direction)) {
            $player->{$direction}();
        }
    }

    private function canMoveToDirection(Player $player, $direction)
    {
        $mapData = $this->gameMap->getMapData();

        $target = $this->getMoveTarget($player->getX(), $player->getY(), $direction);

        return (boolean) $mapData[$target[0]][$target[1]];
    }

    private function getMoveTarget($x, $y, $direction)
    {
        switch ($direction) {
            case Player::UP:
                return [--$x, $y];
            case Player::DOWN:
                return [++$x, $y];
            case Player::LEFT:
                return [$x, --$y];
            case Player::RIGHT:
                return [$x, ++$y];
        }

        return [$x, $y];
    }

    public function printGameMap()
    {
        $mapData = $this->gameMap->getMapData();

        $font = [2 => '|S|', 3 => '|H|'];

        /** @var Player $player */
        foreach ($this->players as $player) {
            $mapData[$player->getX()][$player->getY()] = $player->getType() + 1;
        }

        foreach ($mapData as $line) {
            foreach ($line as $cell) {
                if (empty($cell)) {
                    echo '|#|';
                } elseif ($cell == 1) {
                    echo '   ';
                } else {
                    echo $font[$cell];
                }
            }
            echo PHP_EOL;
        }
    }

    public function isGameOver()
    {
        $result = false;
        $x = -1;
        $y = -1;

        $players = array_values($this->players);
        /** @var Player $player */
        foreach ($players as $key => $player) {
            if ($key == 0) {
                $x = $player->getX();
                $y = $player->getY();
            } else if ($x == $player->getX() && $y == $player->getY()) {
                $result = true;
            }
        }

        return $result;
    }
}
