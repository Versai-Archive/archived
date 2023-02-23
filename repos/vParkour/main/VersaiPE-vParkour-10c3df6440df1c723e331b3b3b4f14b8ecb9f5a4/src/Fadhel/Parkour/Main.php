<?php

/**
 * Copyright 2020 Fadhel
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Fadhel\Parkour;

use ARTulloss\Groups\Groups;
use Fadhel\Parkour\commands\Parkour;
use Fadhel\Parkour\utils\Runtime;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {

    protected array $sessions = [];

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new Listeners($this), $this);
        $this->getServer()->getCommandMap()->register("parkour", new Parkour($this));
        $this->getScheduler()->scheduleRepeatingTask(new Runtime($this), 20);
        $this->getArenas();
    }

    public function getArenas(): array {
        $path = $this->getDataFolder() . "arenas/";
        @mkdir($path);
        $arenas = array();
        foreach (scandir($path) as $dir) {
            array_push($arenas, $dir);
        }
        return $arenas;
    }

    public function createSession(Player $player, string $world): bool {
        $player->sendMessage(TextFormat::GREEN . "You have started the parkour! Type checkpoint in chat to go to your most recent checkpoint or type quit to quit!");
        if (!$this->hasSession($player)) {
            $this->getLogger()->debug("Creating session for: " . $player->getName());
            $this->sessions[$player->getName()] = new Session($this, $player, $world);
            return true;
        }
        return false;
    }

    public function hasSession(Player $player): bool {
        return isset($this->sessions[$player->getName()]);
    }

    public function removeSession(Player $player): bool {
        if ($this->hasSession($player)) {
            unset($this->sessions[$player->getName()]);
            $groups = $player->getServer()->getPluginManager()->getPlugin('Groups');
            if($groups === null) return true;
            $scoreboardTask = $groups->scoreboardListener->getTask();
            $bossbarTask = $groups->bossbarListener->getTask();
            if($scoreboardTask !== null && $bossbarTask !== null) {
                $name = $player->getName();
                $scoreboardTask->resetPlayersTextByName($name);
                $bossbarTask->resetBarProgressByName($name);
            }
            return true;
        }
        return false;
    }


    public function getSession(Player $player): ?Session {
        return $this->hasSession($player) ? $this->sessions[$player->getName()] : null;
    }

    public function onDisable(): void {
        foreach ($this->getAllSessions() as $session) {
            if ($session instanceof Session) {
                $session->expireSession();
                $this->getLogger()->debug("Creating data for " . $session->getPlayer()->getName() . ". Map: " . $session->getMap() . " Checkpoints: " . $session->getReachedCheckpoints() . " Left checkpoints: " . $session->getLeftCheckpoints() . " Time: " . $session->getTime());
            }
        }
    }

    public function getAllSessions(): array {
        return $this->sessions;
    }
}