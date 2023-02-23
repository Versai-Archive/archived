<?php
declare(strict_types=1);

namespace Versai\vwarps\Commands\Arguments;

use CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;

class WarpEnumArgument extends StringEnumArgument
{
    public const WARP_NODEBUFF = "NoDebuff";
    public const WARP_COMBO = "Combo";
    public const WARP_SUMO = "Sumo";
    public const WARP_LOBBY = "Lobby";

    protected const VALUES = [
        "nodebuff" => self::WARP_NODEBUFF,
        "combo" => self::WARP_COMBO,
        "sumo" => self::WARP_SUMO,
        "lobby" => self::WARP_LOBBY
    ];

    public function parse(string $argument, CommandSender $sender) {
        return (string)$this->getValue($argument);
    }

    public function getTypeName(): string {
        return "warp";
    }
}