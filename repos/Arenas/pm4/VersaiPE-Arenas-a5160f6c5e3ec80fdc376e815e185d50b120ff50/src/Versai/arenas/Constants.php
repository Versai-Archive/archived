<?php

namespace Versai\arenas;

use pocketmine\utils\TextFormat;

interface Constants{

    public const PREFIX = TextFormat::BLUE . "[Arenas] ";

    public const BASE_PERMISSION = "arenas.command";
    public const LIST_PERMISSION = self::BASE_PERMISSION . ".list";
    public const INFO_PERMISSION = self::BASE_PERMISSION . ".info";
    public const SET_PERMISSION = self::BASE_PERMISSION . ".set";
    public const CREATE_PERMISSION = self::BASE_PERMISSION . ".create";
    public const REMOVE_PERMISSION = self::BASE_PERMISSION . ".remove";
}