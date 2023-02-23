<?php


namespace Martin\Sumo\Game;


use Martin\GameAPI\Kit\IKit;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;

class SumoKit implements IKit
{
    public const KIT_NAME = "Sumo";

    public function getName(): string
    {
        return self::KIT_NAME;
    }

    public function getArmorInventory(): array
    {
        return [];
    }

    public function getInventory(): array
    {
        return [Item::get(ItemIds::STEAK, 0, 64)];
    }


    public function getEffects(): array
    {
        return [
            new EffectInstance(Effect::getEffect(Effect::SPEED), 60 * 60),
            new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 60 * 60, 3),
        ];
    }
}