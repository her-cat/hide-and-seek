<?php

namespace App\Manager;

class TaskManager
{
    const TASK_CODE_FIND_PLAYER = 1;

    public static function findPlayer()
    {
        $len = DataCenter::getPlayerWaitListLen();

        if ($len >= 2) {
            return [
                'red_player' => DataCenter::popPlayerFromWaitList(),
                'blue_player' => DataCenter::popPlayerFromWaitList()
            ];
        }

        return false;
    }
}
