<?php
declare(strict_types=1);

namespace Versai\vwarps\Utilities;

use pocketmine\network\mcpe\protocol\types\DeviceOS as PMDevice;
use pocketmine\player\Player;
use function in_array;

class DeviceOS{

    private array $devices;
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
            PMDevice::ANDROID, PMDevice::IOS, PMDevice::AMAZON
        ], true);
    }

}
