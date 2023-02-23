<?php


namespace Martin\GameAPI\Types;


interface PlayerStateType
{
    public const STATE_PLAYING = 0;
    public const STATE_WAITING = 1;
    public const STATE_DEAD = 2;
    public const STATE_FINISHED = 3;
}