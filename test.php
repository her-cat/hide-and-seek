<?php

use App\Manager\Game;
use App\Models\Player;

require_once __DIR__ . '/vendor/autoload.php';

$redId = "red_player";
$blueId = "blue_player";

$game = new Game();

//添加玩家
$game->createPlayer($redId, 6, 1);
//添加玩家
$game->createPlayer($blueId, 6, 10);

for ($i = 0; $i < 300; $i++) {
    $direct = mt_rand(0, 3);
    $game->playerMove($redId, Player::DIRECTION[$direct]);
    if ($game->isGameOver()) {
        echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
        $game->printGameMap();
        echo "game over~".PHP_EOL;
        break;
    }

    $direct = mt_rand(0, 3);
    $game->playerMove($blueId, Player::DIRECTION[$direct]);
    if ($game->isGameOver()) {
        echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
        $game->printGameMap();
        echo "game over~".PHP_EOL;
        break;
    }
    echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
    $game->printGameMap();
    usleep(200000);
}