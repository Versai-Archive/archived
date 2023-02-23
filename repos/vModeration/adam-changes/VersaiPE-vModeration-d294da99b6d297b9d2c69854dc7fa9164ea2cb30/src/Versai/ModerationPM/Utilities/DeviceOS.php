<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Utilities;

use pocketmine\network\mcpe\protocol\types\DeviceOS as PMDeviceOS;
use pocketmine\player\Player;
use function in_array;

class DeviceOS {

    /** @var int[] $devices */
    private $devices;

    /** @var int[] $inputMode */
    private array $inputMode;
    /**
     * @param string $name
     * @param int|null $deviceOS
     */
    public function setDeviceOS(string $name, ?int $deviceOS): void{
        $this->devices[$name] = $deviceOS;
    }

    /**
     * @param string $name
     * @param int|null $inputMode
     */
    public function setInputMode(string $name, ?int $inputMode): void{
        $this->inputMode[$name] = $inputMode;
    }

    /**
     * @param Player $player
     * @return int|null
     */
    public function getInputMode(Player $player): ?int{
        return $this->inputMode[$player->getName()] ?? null;
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
            PMDeviceOS::ANDROID,
            PMDeviceOS::IOS,
            PMDeviceOS::OSX,
            PMDeviceOS::AMAZON
        ], true);
    }

}