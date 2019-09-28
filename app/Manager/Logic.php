<?php

namespace App\Manager;

class Logic
{
    public function matchPlayer($playerId)
    {
        DataCenter::pushPlayerToWaitList($playerId);

        DataCenter::$server->task(['code' => TaskManager::TASK_CODE_FIND_PLAYER]);
    }
}
