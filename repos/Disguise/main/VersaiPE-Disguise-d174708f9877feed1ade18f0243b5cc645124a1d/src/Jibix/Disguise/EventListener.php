<?php
namespace Jibix\Disguise;
use Jibix\Disguise\disguise\DisguiseManager;
use Jibix\Disguise\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;


/**
 * Class EventListener
 * @package Jibix\Disguise
 * @author Jibix
 * @date 08.02.2022 - 23:29
 * @project Disguise
 */
class EventListener implements Listener{

    /**
     * Function onSkinChange
     * @param PlayerChangeSkinEvent $event
     */
    public function onSkinChange(PlayerChangeSkinEvent $event): void{
        $player = $event->getPlayer();
        if (DisguiseManager::getInstance()->isDisguised($player->getName())) {
            DisguiseManager::getInstance()->disguised[$player->getName()]["skin"] = Utils::skinToArray($event->getNewSkin());
        }
    }
}