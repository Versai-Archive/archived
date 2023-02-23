<?php


namespace Martin\GameAPI\Types;


interface GameStateType
{
    public const STATE_WAITING = 0;
    public const STATE_ONGOING = 1;
    public const STATE_END = 2;
}