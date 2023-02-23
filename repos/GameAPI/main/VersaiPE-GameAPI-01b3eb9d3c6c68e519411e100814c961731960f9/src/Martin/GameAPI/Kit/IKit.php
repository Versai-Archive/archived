<?php


namespace Martin\GameAPI\Kit;


use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;

interface IKit
{
    public function getName(): string;

    /**
     * @return Item[]
     */
    public function getArmorInventory(): array;

    /**
     * @return Item[]
     */
    public function getInventory(): array;

    /**
     * @return EffectInstance[]
     */
    public function getEffects(): array;
}