<?php

namespace App\Models;

class Player
{
    const UP = 'up';
    const DOWN = 'down';
    const LEFT = 'left';
    const RIGHT = 'right';

    const DIRECTION = [self::UP, self::DOWN, self::LEFT, self::RIGHT];

    const PLAYER_TYPE_SEEK = 1;
    const PLAYER_TYPE_HIDE = 2;

    private $id;
    private $type;
    private $x;
    private $y;

    /**
     * Player constructor.
     * @param $id
     * @param $x
     * @param $y
     */
    public function __construct($id, $x, $y)
    {
        $this->id = $id;
        $this->x = $x;
        $this->y = $y;

        $this->type = self::PLAYER_TYPE_SEEK;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(int $type)
    {
        $this->type = $type;
    }

    public function getId()
    {
        return $this->id;
    }

    public function up()
    {
        $this->x--;
    }

    public function down()
    {
        $this->x++;
    }

    public function left()
    {
        $this->y--;
    }

    public function right()
    {
        $this->y++;
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }
}
