<?php


namespace Versai\Sumo\Session;


use pocketmine\level\Level;
use pocketmine\math\Vector3;
use Versai\Sumo\Sumo;

class BuildingArena
{
    public ?string $name = null;

    public ?Level $level = null;

    public ?Vector3 $spawningPosition = null;

    public ?Vector3 $position1 = null;

    public ?Vector3 $position2 = null;

    public ?float $spawningYaw = null;

    public ?float $yaw1 = null;

    public ?float $yaw2 = null;

    /**
     * @param Sumo $sumo
     * @param string $any
     * @description true => name good
     * @return bool
     */
    public static function validateName(Sumo $sumo, string $any): bool
    {
        return sizeof(array_filter($sumo->arenas, function ($arena) use ($any) {
                return $arena->name !== $any;
            })) === 0;
    }
}