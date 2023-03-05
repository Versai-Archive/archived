<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/9/2019
 * Time: 9:20 PM
 */
declare(strict_types=1);

namespace ARTulloss\Tap2\Utilities;

use pocketmine\Player;
use function in_array;

class DeviceOS{

    const OS_ANDROID = 1;
    const OS_IOS = 2;
    const OS_OSX = 3;
    const OS_FIREOS = 4;
    const OS_GEARVR = 5;
    const OS_HOLOLENS = 6;
    const OS_WIN10 = 7;
    const OS_WIN32 = 8;
    const OS_DEDICATED = 9;
    const OS_ORBIS = 10;
    const OS_NX = 11;

    /** @var int[] $devices */
    private $devices;
    /**
     * @param string $name
     * @param int|null $deviceOS
     */
    public function setDeviceOS(string $name, ?int $deviceOS): void{
        $this->devices[$name] = $deviceOS;
    }
    /**
     * @param Player $player
     * @return int|null
     */
    public function getDeviceOS(Player $player): ?int{
        return $this->devices[$player->getName()] ?? null;
    }
    public function isPE(Player $player): bool{
        return in_array($this->devices[$player->getName()], [
            self::OS_ANDROID,
            self::OS_IOS,
            self::OS_OSX,
            self::OS_FIREOS
        ], true);
    }
}