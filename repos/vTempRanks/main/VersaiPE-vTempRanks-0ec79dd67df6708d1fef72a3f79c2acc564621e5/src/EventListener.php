<?php
declare(strict_types=1);

namespace Versai\vTempRanks;

use CortexPE\Hierarchy\Hierarchy;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener {

    private Main $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();

        $this->plugin->getProvider()->asyncGetPlayer($player->getName(), function($result) use ($player){
            if(count($result) === 0) return;

            foreach ($result as $row) {
                $until = $row['until'];
                if (Utilities::hasRankExpired((int)$until)) {
                    /** @var Hierarchy $hierarchy */
                    $hierarchy = $this->plugin->getServer()->getPluginManager()->getPlugin("vHierarchy");

                    $member = $hierarchy->getMemberFactory()->getMember($player);
                    $role = $hierarchy->getRoleManager()->getRoleByName($row['rank']);
                    if ($role !== null) {
                        if ($member->hasRole($role)) {
                            $member->removeRole($role);
                            $this->plugin->getProvider()->asyncResetPlayerRank($player->getName(), $role->getName());
                            $this->plugin->getLogger()->info("Removed {$player->getName()}'s Temporary '{$role->getName()}' Rank!");
                        }
                    }
                }
            }
        });
    }
}