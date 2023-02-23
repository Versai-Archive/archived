<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/22/2020
 * Time: 10:36 AM
 */
declare(strict_types=1);

namespace ARTulloss\TwistedKits\Events;

use ARTulloss\TwistedKits\Main;
use function explode;
use pocketmine\block\ItemFrame;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\tag\StringTag;

class Listener implements PMListener {
    /** @var Main $main */
    private $main;
    /** @var int[] $cooldown */
    private $cooldown;
    /**
     * Listener constructor.
     * @param Main $main
     */
    public function __construct(Main $main) {
        $this->main = $main;
    }
    /**
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event): void{
        if($event->getBlock() instanceof ItemFrame)
            return;
        // echo "SPAM";
        $player = $event->getPlayer();
        $name = $player->getName();
        if(!isset($this->cooldown[$name]))
            $this->cooldown[$name] = 0;
        if($player->getServer()->getTick() - $this->cooldown[$name] > 20) {
            $inv = $player->getInventory();
            $hand = $inv->getItemInHand();
            $namedTagEntry = $hand->getNamedTagEntry('kitSelectionItem');
            if($namedTagEntry instanceof StringTag) {
                $inv->removeItem($hand);
                /**
                if($hand->count === 1)
                    $inv->removeItem($hand);
                else {
                    $hand->pop();
                    $inv->setItemInHand($hand);
                }
                **/
                //echo "THIS IS ON A COOLDOWN";
                $tagValue = $namedTagEntry->getValue();
                $tagExplosion = explode('~', $tagValue);
                $kitName = $tagExplosion[0]; // $tagValue[1] is the microtime to prevent stacking.
                $this->main->getKits()[$kitName]->sendToPlayer($player);
            }
            $this->cooldown[$name] = $player->getServer()->getTick();
        }
    }
    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void{
        unset($this->cooldown[$event->getPlayer()->getName()]);
    }
}