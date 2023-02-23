<?php


namespace Martin\GameAPI\Kit;


use Martin\GameAPI\Kit\Kits\CachedPlayer;
use Martin\GameAPI\Kit\Kits\EmptyKit;
use Martin\GameAPI\Kit\Kits\NoDebuffKit;
use pocketmine\Player;

class KitManager
{
    private static array $kits = [];

    private static bool $initalized = false;

    public static function init(): void
    {
        if (static::isInitalized()) {
            return;
        }

        self::$initalized = true;
        self::addKit(new NoDebuffKit());
        self::addKit(new EmptyKit());
    }

    /**
     * @return bool
     */
    public static function isInitalized(): bool
    {
        return self::$initalized;
    }

    public static function addKit(IKit ...$kits): void
    {
        foreach ($kits as $kit) {
            if (!($kit instanceof IKit)) {
                return;
            }
            self::$kits[strtolower($kit->getName())] = $kit;
        }
    }

    public static function getKit(string $kit): ?IKit
    {
        return self::$kits[strtolower($kit)] ?? null;
    }

    public static function fromPlayer(Player $player): IKit
    {
        return new CachedPlayer($player);
    }

    public static function toPlayer(IKit $kit, Player $player): void
    {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();

        $player->getInventory()->setContents($kit->getInventory());
        $player->getArmorInventory()->setContents($kit->getArmorInventory());
        foreach ($kit->getEffects() as $effectInstance) {
            $player->addEffect($effectInstance);
        }
    }
}