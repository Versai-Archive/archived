<?php


namespace Martin\GameAPI\Kit\Kits;


use Martin\GameAPI\Kit\IKit;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;

class NoDebuffKit implements IKit
{
    public const INSTANT_HEALING = 21;
    public const INSTANT_HEALING_2 = 22;
    public const UNBREAKING_LEVEL = 10;
    public const SPEED_DURATION = 60 * 60;
    public const SPEED_AMPLIFIER = 1;

    public function getName(): string
    {
        return "NoDebuff";
    }

    public function getArmorInventory(): array
    {
        $helmet = Item::get(ItemIds::DIAMOND_HELMET);
        $chestplate = Item::get(ItemIds::DIAMOND_CHESTPLATE);
        $leggings = Item::get(ItemIds::DIAMOND_LEGGINGS);
        $boots = Item::get(ItemIds::DIAMOND_BOOTS);
        /** @var Item $armor */
        foreach ([$helmet, $chestplate, $leggings, $boots] as $armor) $armor->addEnchantment($this->getUnbreaking());

        return [
            $helmet,
            $chestplate,
            $leggings,
            $boots
        ];
    }

    /**
     * @description Gets used pretty often here so why not
     */
    private function getUnbreaking(): EnchantmentInstance
    {
        return new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), self::UNBREAKING_LEVEL);
    }

    public function getInventory(): array
    {
        $sword = Item::get(ItemIds::DIAMOND_SWORD);
        $sword->addEnchantment($this->getUnbreaking());
        $potion = Item::get(ItemIds::SPLASH_POTION, self::INSTANT_HEALING_2);
        return array_merge([$sword], array_fill(1, 35, $potion));
    }

    public function getEffects(): array
    {
        return [
            new EffectInstance(Effect::getEffect(Effect::SPEED), self::SPEED_DURATION, self::SPEED_AMPLIFIER, true)
        ];
    }
}