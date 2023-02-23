<?php


namespace Martin\GameAPI\Kit\Kits;


use Martin\GameAPI\Kit\IKit;
use pocketmine\Player;

class CachedPlayer implements IKit
{
    private Player $player;


    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getArmorInventory(): array
    {
        return [
            $this->player->getArmorInventory()->getHelmet(),
            $this->player->getArmorInventory()->getChestplate(),
            $this->player->getArmorInventory()->getLeggings(),
            $this->player->getArmorInventory()->getBoots()
        ];
    }

    public function getInventory(): array
    {
        return $this->player->getInventory()->getContents();
    }

    public function getEffects(): array
    {
        return $this->player->getEffects();
    }

    public function getName(): string
    {
        return $this->player->getName();
    }
}