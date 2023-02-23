<?php

declare(strict_types=1);

namespace CustomPotions;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;

class CPListener implements Listener{

    /**
     * @param PlayerItemConsumeEvent $event
     */

    public function onConsume(PlayerItemConsumeEvent $event) : void{

        $player = $event->getPlayer();

        if($event->getItem()->getId() === 373){
            $event->setCancelled();
            $damage = $event->getItem()->getDamage();

            switch($damage){
                case 100:

                    $player->addEffect((new EffectInstance(Effect::getEffect(Effect::SPEED)))->setDuration(360 * 20)->setAmplifier(1));
                    $player->addEffect((new EffectInstance(Effect::getEffect(Effect::HASTE)))->setDuration(360 * 22)->setAmplifier(2));
                    $player->addEffect((new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION)))->setDuration(180 * 20)->setAmplifier(1));

                    $player->getInventory()->removeItem(Item::get(Item::POTION, 100, 1));
                    $player->getInventory()->addItem(Item::get(Item::GLASS_BOTTLE, 0, 1));
                    $player->addTitle("§l§8[§c+§8]§r §7Consumed:", "§l§cRaiding Potion§r");
                    break;

                    case 101:

                        $player->addEffect((new EffectInstance(Effect::getEffect(Effect::JUMP)))->setDuration(180 * 50)->setAmplifier(1));
                        $player->addEffect((new EffectInstance(Effect::getEffect(Effect::STRENGTH)))->setDuration(30 * 50)->setAmplifier(1));
                        $player->addEffect((new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION)))->setDuration(360 * 50)->setAmplifier(1));
                        $player->addEffect((new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE)))->setDuration(360 * 50)->setAmplifier(1));
    
                        $player->getInventory()->removeItem(Item::get(Item::POTION, 101, 1));
                        $player->getInventory()->addItem(Item::get(Item::GLASS_BOTTLE, 0, 1));
                        $player->addTitle("§l§8[§b+§8]§r §7Consumed:", "§l§bPvP Potion§r");
                        break;

                    case 102:

                        $player->addEffect((new EffectInstance(Effect::getEffect(Effect::REGENERATION)))->setDuration(360 * 50)->setAmplifier(2));
                        $player->addEffect((new EffectInstance(Effect::getEffect(Effect::ABSORPTION)))->setDuration(360 * 50)->setAmplifier(2));
        
                        $player->getInventory()->removeItem(Item::get(Item::POTION, 102, 1));
                        $player->getInventory()->addItem(Item::get(Item::GLASS_BOTTLE, 0, 1));
                        $player->addTitle("§l§8[§e+§8]§r §7Consumed:", "§l§eHealer Potion§r");
                        break;

                    case 103:

                            $player->addEffect((new EffectInstance(Effect::getEffect(Effect::SPEED)))->setDuration(360 * 20)->setAmplifier(3));
                            $player->addEffect((new EffectInstance(Effect::getEffect(Effect::HASTE)))->setDuration(360 * 20)->setAmplifier(3));
                            $player->addEffect((new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE)))->setDuration(180 * 50)->setAmplifier(2));
                            $player->addEffect((new EffectInstance(Effect::getEffect(Effect::WATER_BREATHING)))->setDuration(180 * 50)->setAmplifier(2));
                            $player->addEffect((new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION)))->setDuration(180 * 50)->setAmplifier(2));
        
                            $player->getInventory()->removeItem(Item::get(Item::POTION, 103, 1));
                            $player->getInventory()->addItem(Item::get(Item::GLASS_BOTTLE, 0, 1));
                            $player->addTitle("§l§8[§d+§8]§r §7Consumed:", "§l§dMining Potion§r");
                            break;
            }
        }
    }
}