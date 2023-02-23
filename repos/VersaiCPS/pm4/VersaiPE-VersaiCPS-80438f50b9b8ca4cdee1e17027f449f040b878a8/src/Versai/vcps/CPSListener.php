<?php

namespace Versai\vcps;

use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use Versai\vcps\data\DataManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\utils\TextFormat;

class CPSListener implements Listener {

    private array $lastClick = [];
    private array $comboCount = [];

    public function __construct(CPS $plugin) {
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onJoin(PlayerJoinEvent $event) : void {
        DataManager::getInstance()->add($event->getPlayer());
    }

    public function onGet(DataPacketReceiveEvent $event) : void {
        $packet = $event->getPacket();
        if (($player = $event->getOrigin()->getPlayer()) !== null) {
            // start dig is not included in this because inaccurate
            if (($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData) || ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE)) {
                $data = DataManager::getInstance()->get($player);
                if ($data !== null) {
                    $currentTime = microtime(true);
                    $data->clicks[] = $currentTime;
                    $data->clicks = array_filter($data->clicks, function (float $last) use ($currentTime): bool {
                        return $currentTime - $last <= 1;
                    });
                    $data->currentCPS = count($data->clicks);
                    if ($data->currentCPS > 20) {
                        $event->cancel();
                        $data->currentCPS = 20;
                        $data->cpsList[] = $data->currentCPS;
                        if (count($data->cpsList) > 40) {
                            array_shift($data->cpsList);
                        }
                        $player->sendTip(TextFormat::BLUE . "CPS: " . TextFormat::AQUA . $data->currentCPS);
                        return;
                    }
                    $data->cpsList[] = $data->currentCPS;
                    if (count($data->cpsList) > 40) {
                        array_shift($data->cpsList);
                    }
                    $player->sendTip(TextFormat::BLUE . "CPS: " . TextFormat::AQUA . $data->currentCPS);
                }
            }
            if ($packet instanceof LevelSoundEventPacket) {
                $data = DataManager::getInstance()->get($player);
                if ($data !== null) {
                    if ($data->currentCPS > 20) {
                        $sound = $packet->sound;
                        if ($sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                            $this->comboCount[$player->getName()] = 0;
                        } elseif ($sound === LevelSoundEvent::ATTACK_STRONG) {
                            if (isset($this->comboCount[$player->getName()])) {
                                $this->comboCount[$player->getName()]++;
                            } else {
                                $this->comboCount[$player->getName()] = 1;
                            }
                            $player->sendActionBarMessage("Combo Count: " . $this->comboCount[$player->getName()]);
                        }
                    }
                }
            }
        }
    }
}