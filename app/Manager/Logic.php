<?php

namespace App\Manager;

use App\Models\Player;

class Logic
{
    const MAP_DISPLAY_LEN = 2;

    public function matchPlayer($playerId)
    {
        DataCenter::pushPlayerToWaitList($playerId);

        DataCenter::$server->task(['code' => TaskManager::TASK_CODE_FIND_PLAYER]);
    }

    public function createRoom(string $redPlayerId, string $bluePlayerId)
    {
        $roomId = uniqid('room_');
        $this->bindRoomWorker($redPlayerId, $roomId);
        $this->bindRoomWorker($bluePlayerId, $roomId);
    }

    public function bindRoomWorker(string $playerId, string $roomId)
    {
        $playerFd = DataCenter::getPlayerFd($playerId);

        DataCenter::$server->bind($playerFd, crc32($roomId));
        DataCenter::setPlayerRoomId($playerId, $roomId);

        Sender::send($playerFd, ['room_id' => $roomId], Sender::MSG_ROOM_ID);
    }

    public function startRoom(string $roomId, string $playerId)
    {
        if (!isset(DataCenter::$global['rooms'][$roomId])) {
            DataCenter::$global['rooms'][$roomId] = [
                'id' => $roomId,
                'manager' => new Game(),
            ];
        }

        /** @var Game $gameManager */
        $gameManager = DataCenter::$global['rooms'][$roomId]['manager'];

        if (empty($gameManager->getPlayers())) {
            $gameManager->createPlayer($playerId, 6, 1);
            Sender::sendByPlayerId($playerId, '', Sender::MSG_WAIT_PLAYER);
        } else {
            $gameManager->createPlayer($playerId, 6, 10);
            Sender::sendByPlayerId($playerId, '', Sender::MSG_START_GAME);
            $this->sendGameInfo($roomId);
        }
    }

    public function sendGameInfo(string $roomId)
    {
        /** @var Game $gameManager */
        $gameManager = DataCenter::$global['rooms'][$roomId]['manager'];

        /** @var Player[] $players */
        $players = $gameManager->getPlayers();
        $mapData= $gameManager->getMapData();

        foreach ($players as $player) {
            $mapData[$player->getX()][$player->getY()] = $player->getId();
        }

        foreach ($players as $player) {
            $data = [
                'players' => $players,
                'map_data' => $this->getNearMap($mapData, $player->getX(), $player->getY()),
            ];

            Sender::sendByPlayerId($player->getId(), $data, Sender::MSG_GAME_INFO);
        }
    }

    public function getNearMap(array $mapData, int $playerX, int $playerY)
    {
        $result = [];

        $display_len = self::MAP_DISPLAY_LEN;

        for($i = -1 * $display_len; $i < $display_len; $i++) {
            $tmp = [];
            for ($j = -1 * $display_len; $j <= $display_len; $j++) {
                $tmp[] = $mapData[$playerX + $i][$playerY + $j] ?? 0;
            }

            $result[] = $tmp;
        }

        return $result;
    }

    public function movePlayer(string $playerId, string $direction)
    {
        $roomId = DataCenter::getPlayerRoomId($playerId);

        Log::info('room id', [$roomId]);

        /** @var Game $gameManager */
        $gameManager = DataCenter::$global['rooms'][$roomId]['manager'];

        $gameManager->playerMove($playerId, $direction);

        $this->sendGameInfo($roomId);
    }
}
