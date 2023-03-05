<?php


namespace lang\event;


use lang\LanguageAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EventHandler implements Listener
{

    private LanguageAPI $own;

    /**
     * EventHandler constructor.
     * @param LanguageAPI $param
     */
    public function __construct(\lang\LanguageAPI $param)
    {

        $this->own = $param;

    }

    public function onJoin(PlayerJoinEvent $event) {

        $player = $event->getPlayer();

        $session = $this->own->getSession()->getSession($player);

        $session->setCurrentLang($player->getLocale());

        $player->sendMessage("Your idiome is: ".$session->getCurrentLang());



    }
}