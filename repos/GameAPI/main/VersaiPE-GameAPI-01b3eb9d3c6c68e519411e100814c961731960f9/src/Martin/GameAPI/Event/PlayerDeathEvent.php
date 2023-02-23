<?php


namespace Martin\GameAPI\Event;


use Martin\GameAPI\Game\Game;
use Martin\GameAPI\GamePlugin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent as PMPlayerDeathEvent;
use pocketmine\Player;

class PlayerDeathEvent extends BaseGameEvent
{
    private Player $player;
    private $baseEvent;

    public function __construct(GamePlugin $plugin, Game $game, $baseEvent)
    {
        $this->baseEvent = $baseEvent;
        $this->getPlayer();
        parent::__construct($plugin, $game);
    }

    /**
     * @return Player
     * @throws \Exception
     */
    public function getPlayer(): Player
    {
        if ($this->baseEvent instanceof PMPlayerDeathEvent) {
            return $this->baseEvent->getPlayer();
        } else if ($this->baseEvent instanceof EntityDamageEvent) {
            $entity = $this->baseEvent->getEntity();
            if ($entity instanceof Player) {
                return $entity;
            }

            throw new \Exception("Event called while EntityDamageEvent was not executed by a player");
        }

        throw new \Exception("BaseEvent wasn't PlayerDeathEvent or EntityDamageEvent");
    }

    /**
     * @return PMPlayerDeathEvent|EntityDamageEvent
     */
    public function getBaseEvent()
    {
        return $this->baseEvent;
    }

    protected function handleEvent(): void
    {
        $this->getGame()->onDeath($this);
    }
}