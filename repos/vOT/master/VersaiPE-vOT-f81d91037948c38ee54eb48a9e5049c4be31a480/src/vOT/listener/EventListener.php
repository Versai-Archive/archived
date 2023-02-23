<?php declare(strict_types=1);

namespace vOT\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use vOT\Loader;

class EventListener implements Listener {
    private Loader $plugin;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $e): void {
        $username = $e->getPlayer()->getName();
        $this->plugin->getDB()->hasTime($username, function($has) use ($username): void {
            if(!$has) $this->plugin->getDB()->updateTime($username, 0);
        });
        Loader::$TIMES[$username] = time();
    }

    public function onQuit(PlayerQuitEvent $e): void {
        $username = $e->getPlayer()->getName();
        $this->plugin->getDB()->getRawTime($username, function($old) use ($username): void {
            $this->plugin->getDB()->updateTime($username, $old + (time() - Loader::$TIMES[$username]));
            $this->plugin->getDB()->updateLastSeen($username, time());
            unset(Loader::$TIMES[$username]);
        });
    }
}