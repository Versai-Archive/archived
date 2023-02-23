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
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Session
{
    /**
     * @var Main
     */
    protected $plugin;

    /**
     * @var Player
     */
    protected $player;

    /**
     * @var string
     */
    protected $map;

    /**
     * @var int[]
     */
    protected $checkpoints = ["total" => 0, "current" => 0, "reached" => 0];

    /**
     * @var int
     */
    protected $time = 0;

    /** @var \ARTulloss\Groups\Task\ScoreboardTask|null  */
    private $scoreboardTask = null;
    /** @var \ARTulloss\Groups\Task\BossbarTask|null  */
    private $bossbarTask = null;

    /**
     * Session constructor.
     * @param Main $plugin
     * @param Player $player
     * @param string $map
     */
    public function __construct(Main $plugin, Player $player, string $map)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->map = $map;
        $mapData = new Config($this->plugin->getDataFolder() . "arenas/" . $this->map . "/data.yml", Config::YAML);
        $this->checkpoints["total"] = count($mapData->get("checkpoints"));
        $playerData = new Config($this->plugin->getDataFolder() . "arenas/" . $this->map . "/players.yml", Config::YAML);
        if ($playerData->get($player->getName())) {
            $data = $playerData->get($player->getName());
            $parkourMap = explode(":", $data)[0];
            $reachedCheckpoint = explode(":", $data)[1];
            $timeElapsed = explode(":", $data)[2];
            if (strtolower($map) === $parkourMap) {
                $this->checkpoints["reached"] = (int)$reachedCheckpoint;
                $this->time = $timeElapsed;
                $this->sendCheckpoint();
            }
        }
        /** @var Groups $groups */
        $groups = $player->getServer()->getPluginManager()->getPlugin('Groups');
        if($groups === null) return;
        $this->scoreboardTask = $groups->scoreboardListener->getTask();
        $this->bossbarTask = $groups->bossbarListener->getTask();
    }

    /**
     * @param int|null $checkpoint
     */
    public function sendCheckpoint(?int $checkpoint = null): void
    {
        if ($this->getReachedCheckpoints() > 0) {
            $config = new Config($this->plugin->getDataFolder() . "arenas/" . $this->getMap() . "/data.yml", Config::YAML);
            $target = $checkpoint ?? $this->getReachedCheckpoints();
            $data = $config->getNested("checkpoints." . $target);
            $x = explode(":", $data)[0];
            $y = explode(":", $data)[1];
            $z = explode(":", $data)[2];
            $this->player->teleport(new Vector3((int)$x, (int)$y, (int)$z));
            $this->setCurrentCheckpoint($target);
            $this->player->sendMessage(str_replace("{checkpoint}", $target, TextFormat::colorize((string)$this->plugin->getConfig()->get("checkpoint-tp"))));
        } else {
            $this->player->sendMessage(TextFormat::RED . "You don't have any checkpoints.");
        }
    }

    /**
     * @return int
     */
    public function getReachedCheckpoints(): int
    {
        return $this->checkpoints["reached"];
    }

    /**
     * @return string
     */
    public function getMap(): string
    {
        return $this->map;
    }

    /**
     * @param int $checkpoint
     */
    public function setCurrentCheckpoint(int $checkpoint): void
    {
        $this->checkpoints["current"] = $checkpoint;
    }

    /**
     * @return int
     */
    public function getCurrentCheckpoint(): int
    {
        return $this->checkpoints["current"];
    }

    /**
     * @return int
     */
    public function reverseCheckpoint(): int
    {
        return (int)$this->checkpoints["current"] - 1;
    }

    /**
     * @return int
     */
    public function forwardCheckpoint(): int
    {
        return (int)$this->checkpoints["current"] + 1;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param int $checkpoint
     */
    public function addCheckpoint(int $checkpoint): void
    {
        $this->checkpoints["reached"] = $checkpoint;
    }

    public function endParkour(): void
    {
        if ($this->getLeftCheckpoints() !== 0) {
            $this->player->sendMessage(TextFormat::RED . "Unable to complete the parkour. You didn't hit all the checkpoints!");
            return;
        }
        $this->player->sendMessage(str_replace(["{time}", "{left_checkpoints}", "{reached_checkpoints}"], [gmdate("H:i:s", $this->getTime()), $this->getLeftCheckpoints(), $this->getReachedCheckpoints()], TextFormat::colorize($this->plugin->getConfig()->get("parkour-finish"))));
        $this->plugin->removeSession($this->player);
        $config = new Config($this->plugin->getDataFolder() . "arenas/" . $this->map . "/players.yml", Config::YAML);
        $config->remove($this->player->getName());
        $config->save();
        $name = $this->player->getName();
        /** @var Groups $groups */
        $groups = $this->plugin->getServer()->getPluginManager()->getPlugin('Groups');
        if ($groups !== null) {
            $data = $groups->playerHandler->getPlayerData($name);
            if ($data !== null && $data->getGroup() === $groups->getDefaultGroupName())
                $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), 'group set "' . $name . '" Ultra 1 day');
        }
    }

    /**
     * @return int
     */
    public function getLeftCheckpoints(): int
    {
        return $this->checkpoints["total"] > $this->checkpoints["reached"] ? $this->checkpoints["total"] - $this->checkpoints["reached"] : 0;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param bool $save
     */
    public function expireSession(bool $save = true): void
    {
        if ($save) {
            $config = new Config($this->plugin->getDataFolder() . "arenas/" . $this->map . "/players.yml", Config::YAML);
            $config->set($this->player->getName(), strtolower($this->getMap()) . ":" . $this->getReachedCheckpoints() . ":" . $this->getTime());
            $config->save();
        } else {
            $config = new Config($this->plugin->getDataFolder() . "arenas/" . $this->map . "/players.yml", Config::YAML);
            $config->remove($this->player->getName());
            $config->save();
        }
        $this->plugin->removeSession($this->player);
    }

    public function tick(): void
    {
        $this->time++;
        if($this->bossbarTask !== null && $this->scoreboardTask !== null) {
            $name = $this->player->getName();
            $reached = $this->getReachedCheckpoints();
            $total = $this->getTotalCheckpoints();
            $time = $this->getTime();
            $this->bossbarTask->setHealthProgressByName($name, $reached, $total);
            //$this->bossbarTask->setTextForByName($name, [ // THIS DOES NOT WORK YIKES
            //    TextFormat::AQUA . "Versai Parkour | Progress: " . TextFormat::BLUE . $reached . " / " . $total . " | Time: $time"
            //]);
            $this->scoreboardTask->setTextForByName($name, [
                TextFormat::AQUA . "Progress: " . TextFormat::BLUE . $reached . " / " . $total,
                TextFormat::AQUA . "Time: " . TextFormat::BLUE . gmdate("H:i:s", $time),
                TextFormat::AQUA . "    versai.pro"
            ]);
        } else
            $this->player->sendTip(str_replace(["{reached_checkpoints}", "{left_checkpoints}", "{time}"], [$this->getReachedCheckpoints(), $this->getTotalCheckpoints(), gmdate("H:i:s", $this->getTime())], TextFormat::colorize((string)$this->plugin->getConfig()->get("progress-bar"))));
    }

    /**
     * @return int
     */
    public function getTotalCheckpoints(): int
    {
        return $this->checkpoints["total"];
    }
}