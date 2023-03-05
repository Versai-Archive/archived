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

namespace Fadhel\Parkour\commands;

use Fadhel\Parkour\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Parkour extends Command
{
    protected Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("parkour", "Parkour", "/parkour <checkpoint|forward|reverse|quit>", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Run this command in-game.");
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . $this->getUsage());
            return;
        }
        switch (strtolower($args[0])) {
            case "create":
                if ($sender->hasPermission("parkour.create")) {
                    $world = $sender->getWorld()->getFolderName();
                    $path = $this->plugin->getDataFolder() . "arenas/" . $world;
                    @mkdir($path);
                    $config = new Config($path . "/data.yml");
                    $config->set("map", strtolower($world));
                    $config->set("checkpoints", []);
                    $config->save();
                    $config = new Config($path . "/players.yml");
                    $config->save();
                } else {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission to use this argument.");
                }
                break;
            case "checkpoint":
                if ($this->plugin->hasSession($sender)) {
                    $checkpoint = $this->plugin->getSession($sender)->getReachedCheckpoints();
                    if (isset($args[1])) {
                        if (is_numeric($args[1])) {
                            if ($this->plugin->getSession($sender)->getReachedCheckpoints() >= (int)$args[1]) {
                                $checkpoint = (int)$args[1];
                            } else {
                                $sender->sendMessage(TextFormat::RED . "You haven't reached that checkpoint yet.");
                                return;
                            }
                        } else {
                            $sender->sendMessage(TextFormat::RED . "That is not a valid number.");
                            return;
                        }
                    }
                    $this->plugin->getSession($sender)->sendCheckpoint($checkpoint);
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have a parkour session.");
                }
                break;
            case "reverse":
                if ($this->plugin->hasSession($sender)) {
                    $checkpoint = $this->plugin->getSession($sender)->reverseCheckpoint();
                    if ($checkpoint > 0) {
                        $this->plugin->getSession($sender)->sendCheckpoint($checkpoint);
                    } else {
                        $sender->sendMessage(TextFormat::RED . "You haven't reached any checkpoints yet.");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have a parkour session.");
                }
                break;
            case "forward":
                if ($this->plugin->hasSession($sender)) {
                    $checkpoint = $this->plugin->getSession($sender)->forwardCheckpoint();
                    if ($this->plugin->getSession($sender)->getReachedCheckpoints() >= $checkpoint) {
                        $this->plugin->getSession($sender)->sendCheckpoint($checkpoint);
                    } else {
                        $sender->sendMessage(TextFormat::RED . "You haven't reached that checkpoint yet.");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have a parkour session.");
                }
                break;
            case "quit":
                if ($this->plugin->hasSession($sender)) {
                    $sender->sendMessage(TextFormat::colorize((string)$this->plugin->getConfig()->get("parkour-quit")));
                    $this->plugin->getSession($sender)->expireSession(false);
                    $sender->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have a parkour session.");
                }
                break;
            case "end":
                if ($sender->hasPermission("parkour.end")) {
                    if (isset($args[1])) {
                        $player = $this->plugin->getServer()->getPlayer($args[1]);
                        if ($player instanceof Player and $this->plugin->hasSession($sender)) {
                            $player->sendMessage(TextFormat::colorize((string)$this->plugin->getConfig()->get("parkour-quit")));
                            $this->plugin->getSession($player)->expireSession(false);
                            $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                            $sender->sendMessage(TextFormat::YELLOW . "Successfully ended " . TextFormat::WHITE . $player->getName() . TextFormat::YELLOW . "'s parkour session.");
                        } else {
                            $sender->sendMessage(TextFormat::RED . "That player does not have a parkour session.");
                        }
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Usage /parkour end <player>");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission to use this argument.");
                }
        }
    }
}