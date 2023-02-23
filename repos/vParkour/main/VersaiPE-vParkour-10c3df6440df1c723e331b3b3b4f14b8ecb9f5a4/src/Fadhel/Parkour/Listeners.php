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

use pocketmine\Server;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Listeners implements Listener
{

    protected Main $plugin;


    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        if ($this->plugin->hasSession($player)) {
            $this->plugin->getSession($player)->expireSession();
        }
    }

    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $player->getWorld()->getBlock($player->getPosition()->subtract(0, 0, 0));
        $sign = $player->getWorld()->getTile($player->getPosition()->subtract(0, 2, 0));

        switch ($block->getFullId()) {

            case VanillaBlocks::WEIGHTED_PRESSURE_PLATE_HEAVY()->getFullId():
                if ($sign instanceof Sign) {
                    $text = $sign->getText();
                    if (!$this->plugin->hasSession($player) and $text[0] === "PARKOUR_START" and in_array($player->getWorld()->getFolderName(), $this->plugin->getArenas())) {
                        $this->plugin->createSession($player, $player->getWorld()->getFolderName());
                        return;
                    }
                }
                if ($this->plugin->hasSession($player)) {
                    $session = $this->plugin->getSession($player);
                    $config = new Config($this->plugin->getDataFolder() . "arenas/" . $session->getMap() . "/data.yml", Config::YAML);
                    $checkpoints = $config->getAll();
                    foreach ($checkpoints["checkpoints"] as $i => $checkpoint) {
                        $checkpointPosition = new Vector3(explode(":", $checkpoint)[0], explode(":", $checkpoint)[1], explode(":", $checkpoint)[2]);
                        $blockPosition = new Vector3(round($block->getX()), round($block->getY()), round($block->getZ()));
                        if ($i > $session->getReachedCheckpoints() and $checkpointPosition->equals($blockPosition)) {
                            $player->sendMessage(str_replace(["{reached_checkpoints}", "{left_checkpoints}"], [$i, $session->getTotalCheckpoints()], TextFormat::colorize((string)$this->plugin->getConfig()->get("checkpoint-set"))));
                            $session->addCheckpoint($i);
                        }
                    }
                }
                break;

            case VanillaBlocks::WEIGHTED_PRESSURE_PLATE_LIGHT()->getFullId():
                if ($this->plugin->hasSession($player)) {
                    $this->plugin->getSession($player)->endParkour();
                }
        }
    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        $path = $this->plugin->getDataFolder() . "arenas/" . $world;
        if (Server::getInstance()->isOp($player->getName()) and $event->getBlock()->getFullId() === VanillaBlocks::WEIGHTED_PRESSURE_PLATE_HEAVY()->getFullId() and in_array($player->getWorld()->getFolderName(), $this->plugin->getArenas())) {
            $config = new Config($path . "/data.yml", Config::YAML);
            $checkpoints = $config->getAll();
            $i = count($checkpoints["checkpoints"]) + 1;
            $pos = round($event->getBlock()->getX()) . ":" . round($event->getBlock()->getY()) . ":" . round($event->getBlock()->getZ());
            $config->setNested("checkpoints." . $i, $pos);
            $config->save();
            $player->sendMessage(TextFormat::GREEN . "Successfully set checkpoint #" . TextFormat::WHITE . $i);
        }
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld()->getFolderName();
        $path = $this->plugin->getDataFolder() . "arenas/" . $world;
        if (Server::getInstance()->isOp($player->getName()) and $event->getBlock()->getFullId() === VanillaBlocks::WEIGHTED_PRESSURE_PLATE_HEAVY()->getFullId() and in_array($player->getWorld()->getFolderName(), $this->plugin->getArenas())) {
            $config = new Config($path . "/data.yml", Config::YAML);
            $checkpoints = $config->getAll();
            foreach ($checkpoints["checkpoints"] as $i => $checkpoint) {
                $checkpointPosition = new Vector3(explode(":", $checkpoint)[0], explode(":", $checkpoint)[1], explode(":", $checkpoint)[2]);
                $blockPosition = new Vector3(round($event->getBlock()->getX()), round($event->getBlock()->getY()), round($event->getBlock()->getZ()));
                if ($checkpointPosition->equals($blockPosition)) {
                    $config->removeNested("checkpoints." . $i);
                    $config->save();
                    $player->sendMessage(TextFormat::GREEN . "Successfully removed checkpoint #" . TextFormat::WHITE . $i);
                }
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            switch ($event->getCause()) {
                case EntityDamageEvent::CAUSE_VOID:
                    if ($this->plugin->hasSession($player)) {
                        $event->setCancelled();
                        $this->plugin->getSession($player)->sendCheckpoint();
                    }
            }
        }
    }

    public function onLevelChange(EntityTeleportEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if ($this->plugin->hasSession($player)) {
                $player->sendMessage(TextFormat::colorize((string)$this->plugin->getConfig()->get("parkour-quit")));
                $this->plugin->getSession($player)->expireSession();
            }
        }
    }
}