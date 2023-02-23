<?php

namespace ethaniccc\VersaiCPS;

use ethaniccc\VersaiCPS\data\DataManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerMovementType;
use pocketmine\utils\TextFormat;

class CPSListener implements Listener{

    private $lastClick = [];

    public function __construct(VersaiCPS $plugin){
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onJoin(PlayerJoinEvent $event) : void{
        DataManager::getInstance()->add($event->getPlayer());
    }

    public function onGet(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        // start dig is not included in this because inaccurate
        if(($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData) || ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE)){
            $data = DataManager::getInstance()->get($event->getPlayer());
            if($data !== null){
                $currentTime = microtime(true);
                $data->clicks[] = $currentTime;
                $data->clicks = array_filter($data->clicks, function(float $last) use ($currentTime) : bool{
                    return $currentTime - $last <= 1;
                });
                $data->currentCPS = count($data->clicks);
                $data->cpsList[] = $data->currentCPS;
                if(count($data->cpsList) > 40){
                    array_shift($data->cpsList);
                }
                $event->getPlayer()->sendTip(TextFormat::BLUE . "CPS: " . TextFormat::AQUA . $data->currentCPS);
            }
        }
    }

}