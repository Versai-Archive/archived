<?php


namespace Martin\GameAPI\Kit\Kits;


use Martin\GameAPI\Kit\IKit;

class EmptyKit implements IKit
{
    public const KIT_NAME = "Empty";

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
        return [];
    }

    public function getEffects(): array
    {
        return [];
    }
}