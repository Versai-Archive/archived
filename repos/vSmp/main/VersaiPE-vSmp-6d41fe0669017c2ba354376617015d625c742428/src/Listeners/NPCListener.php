<?php

declare(strict_types=1);

namespace Versai\RPGCore\Listeners;

use pocketmine\event\Listener;
use pocketmine\entity\Villager;
use pocketmine\player\Player;
use Versai\RPGCore\Main;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use Versai\RPGCore\Libraries\FormAPI\window\{
    SimpleWindowForm,
    CustomWindowForm
};
use Versai\RPGCore\Libraries\FormAPI\elements\Button;
use pocketmine\item\VanillaItems;
use pocketmine\item\ItemFactory;

class NPCListener implements Listener {

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onEntityHit(EntityDamageByEntityEvent $event) {
        $entity = $event->getEntity();
        $damager = $event->getDamager();

        if (!$damager instanceof Player) {
            return;
        }

        $sessionManager = $this->plugin->getSessionManager();
        $session = $sessionManager->getSession($damager);

        if ($entity instanceof Villager) {
            $tag = $entity->getScoreTag();
            if ($tag == "bs_npc") {
                $event->cancel();

                $sells = $this->plugin->getConfig()->getNested("NPC.blacksmith");
                
                if (!$sells) {
                    $damager->sendMessage("§cThe config file could not be found for NPC's please contact an administrator");
                }

                $mainWindow = new SimpleWindowForm("BLACKSMITH_FORM", "§7Blacksmith", "", function(Player $player, Button $btn) use ($sells, $session) {
                    
                    $item = ItemFactory::getInstance()->get(intval(explode(":", $btn->getName())[0]), intval(explode(":", $btn->getName())[1]));
                    
                    $cost = $this->plugin->getConfig()->getNested("NPC.blacksmith.{$item->getId()}:{$item->getMeta()}");

                    if (!$cost) {
                        $player->sendMessage("§cNo cost could be found");
                        return;
                    }

                    if (!$sells) {
                        $player->sendMessage("§cThe config file could not be found for NPC's please contact an administrator");
                    }

                    if (!$player->getInventory()->canAddItem($item)) {
                        return $player->sendMessage("§cThis item can not be purchased to your inventory, because you do not have enough room");
                    };
                    if ($session->getCoins() < $cost) {
                        return $player->sendMessage("§cYou can not afford this item!");
                    }

                    $form = new CustomWindowForm("PURCHASE", "§7Purchase", "Complete your purchase for {$item->getName()}", function(Player $player, CustomWindowForm $window) use ($cost, $item, $session) {
                        $max = $window->getElement("maximum")->getFinalValue();
                        $amount = $window->getElement("amount")->getFinalValue();

                        if ($max) {
                            $maxHold = $player->getInventory()->getAddableItemQuantity($item->setCount(10000000000));
                            $maxBuy = floor($session->getCoins() / $cost);

                            var_dump($maxHold . " - Maximum able to hold");

                            if($maxHold > $maxBuy) { // 900 only buy 235
                                $session->removeCoins(intval($maxBuy * $cost));
                                $item->setCount(intval($maxBuy));
                                $player->getInventory()->addItem($item);
                                $cost = $maxBuy * $cost;
                                $player->sendMessage("§aSuccsesfully purchased §e{$maxBuy} §aIron Ingot(s) for §c{$cost}");
                                return;
                            }

                            if($maxHold <= $maxBuy) { // 124 afford 163
                                $session->removeCoins(intval($maxHold * $cost));
                                $item->setCount($maxHold);
                                $player->getInventory()->addItem($item);
                                $cost = $maxHold * $cost;
                                $player->sendMessage("§aSuccsesfully purchased §e{$maxHold} §aIron Ingot(s) for §c{$cost}");
                                return;
                            }
                        } else {
                            $cost = intval($amount * $cost);
                            if ($cost > $session->getCoins()) {
                                $player->sendMessage("§cYou can not afford that much Iron!");
                                return;
                            } else {
                                $maxHold = $player->getInventory()->getAddableItemQuantity($item->setCount(10000000000));
                                if ($amount > $maxHold) {
                                    $cost = intval($maxHold * $cost);
                                    $session->removeCoins(intval($cost));
                                    $item->setCount(intval(floor($maxHold)));
                                    $player->getInventory()->addItem($item);
                                    $player->sendMessage("§aSuccesfully purchased §e{$maxHold} §aIron for §c{$cost}");
                                    return;
                                }
                                $session->removeCoins($cost);
                                $item->setCount(intval(floor($amount)));
                                $player->getInventory()->addItem($item);
                                $player->sendMessage("§aSuccesfully purchased §e{$amount} §aIron for §c{$cost}");
                                return;
                            }
                        }
                            }); 

                            $form->addSlider("amount", "§aQuantity", 1, 64, 1, 64);
                            $form->addToggle("maximum", "§l§cPurchase Maximum?", false);

                            $form->showTo($player);
                    });

                    foreach ($this->plugin->getConfig()->getNested("NPC.blacksmith") as $data => $price) {
                        $item = ItemFactory::getInstance()->get(intval(explode(":", $data)[0]), intval(explode(":", $data)[1]));
                        $mainWindow->addButton("{$item->getId()}:{$item->getMeta()}", "{$item->getName()} " . PHP_EOL . " §gGold: {$price}");
                    }

                $mainWindow->showTo($damager);
            } else {
                return;
            }
        } else {
            return;
        }
    }
}