<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/11/2020
 * Time: 2:20 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiSettings\Events;

use ARTulloss\VersaiSettings\Database\Queries;
use ARTulloss\VersaiSettings\Main;
use ARTulloss\VersaiHUD\Main as VersaiHUD;
use ARTulloss\VersaiSettings\Utilities;
use pocketmine\entity\Skin;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use ReflectionException;
use function count;

class Listener implements PMListener{
    /** @var Main $plugin */
    private $plugin;
    /** @var VersaiHUD|null $versaiHUD */
    private $versaiHUD;
    /**
     * Listener constructor.
     * @param Main $main
     */
    public function __construct(Main $main) {
        $this->plugin = $main;
        $this->versaiHUD = $main->getServer()->getPluginManager()->getPlugin('VersaiHUD');
    }
    /**
     * @param DataPacketReceiveEvent $event
     * @throws ReflectionException
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void{
        $packet = $event->getPacket();
        if($packet instanceof ServerSettingsRequestPacket)
            (new ServerSettingsRequestEvent($event->getPlayer()))->call();
    }
    /**
     * @param ServerSettingsRequestEvent $event
     */
    public function onRequestSettings(ServerSettingsRequestEvent $event): void{
        $player = $event->getPlayer();
        ServerSettingsRequestEvent::sendFormToPlayer($this->plugin->getForm($this->plugin->getPlayerSettings($player)), $player);
    }
    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event): void{
        $database = $this->plugin->getDatabase();
        $player = $event->getPlayer();
        $name = $player->getName();
        $database->executeSelect(Queries::SELECT_PLAYER, ['player_name' => $name], function ($result) use ($database, $name): void{
            if(count($result) === 0) {
                $database->executeInsert(Queries::SELECT_INSERT_PLAYER, ['player_name' => $name]);
            }
        }, Utilities::getOnError($this->plugin));
        $database->executeSelect(Queries::SELECT_SETTINGS, ['player_name' => $name], function ($result) use ($player): void{
            if(isset($result[0])) {
                $result = $result[0];
                $this->plugin->setPlayerSettings($player, $result);
                $capeData = array_values($this->plugin->getCapes())[$result['cape']];
                $skin = $player->getSkin();
                if($capeData !== 'Custom') {
                    $newSkin = new Skin($skin->getSkinId(), $skin->getSkinData(), $capeData, $skin->getGeometryName(), $skin->getGeometryData());
                    $player->setSkin($newSkin);
                    $player->sendSkin();
                }
                if($this->versaiHUD !== null) {
                    $scoreboardTask = $this->versaiHUD->getScoreboardTask();
                    $bossbarTask = $this->versaiHUD->getBossbarTask();
                    $scoreboardTask->setStateForPlayer($player, (bool) $result['scoreboard']);
                    $bossbarTask->setStateForPlayer($player, (bool) $result['bossbar']);
                }
            }
        });
    }
}