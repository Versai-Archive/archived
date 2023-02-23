<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/21/2020
 * Time: 4:03 PM
 */
declare(strict_types=1);


namespace ARTulloss\VersaiHUD;


use ARTulloss\Cooldown\Cooldown;
use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Queue\QueueManager;
use ARTulloss\Kits\Kits;
use ARTulloss\Scoreboards;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use function str_replace;

abstract class HUDTask extends Task{
    /** @var Scoreboards\Main|null $scoreboards */
    private $scoreboards;
    /** @var Cooldown|null $cooldown */
    private $cooldown;
    /** @var Duels|null */
    private $duels;
    /** @var bool[] $enabled */
    protected $enabled;
    /**
     * HUDTask constructor.
     * @param Scoreboards\Main|null $scoreboards
     * @param Cooldown|null $cooldown
     * @param Duels|null $duels
     */
    public function __construct(?Scoreboards\Main $scoreboards, ?Cooldown $cooldown, ?Duels $duels) {
        $this->scoreboards = $scoreboards;
        $this->cooldown = $cooldown;
        $this->duels = $duels;
    }
    /**
     * @param Player $player
     * @param string $string
     * @return string
     */
    public function replaceString(Player $player, string $string): string{
        $cause = $player->getLastDamageCause();
        if($cause instanceof EntityDamageByEntityEvent) {
            $opponent = $cause->getDamager();
            if($opponent instanceof Player) {
                $opponentName = $player->getName();
                $oping = $opponent->getPing();
            }
        }
        $playerName = $player->getName();
        if($this->scoreboards !== null) {
            $kills = $this->scoreboards->getData($playerName, Scoreboards\Types::TYPE_KILLS);
            $deaths = $this->scoreboards->getData($playerName, Scoreboards\Types::TYPE_DEATHS);
            $kdr = $this->scoreboards->getData($playerName, Scoreboards\Types::TYPE_KDR);
            $streak = $this->scoreboards->getData($playerName, Scoreboards\Types::TYPE_STREAKS);
        } else {
            $kills = 0;
            $deaths = 0;
            $kdr = 0;
            $streak = 0;
        }
        if($this->cooldown !== null) {
            $combat = $this->cooldown->isCombatEnabled() ? $this->cooldown->combat->getCooldown($player) : 0;
            $pearl = $this->cooldown->isPearlEnabled() ? $this->cooldown->pearl->getCooldown($player) : 0;
        } else {
            $combat = 0;
            $pearl = 0;
        }
        if($this->duels !== null) {
            /** @var Kits $kits */
            $kits = $this->duels->getServer()->getPluginManager()->getPlugin('Kits');
            $uqueue = $this->duels->queueManager->getInQueueTypes($kits->kitTypes, QueueManager::UNRANKED);
            $rqueue = $this->duels->queueManager->getInQueueTypes($kits->kitTypes, QueueManager::RANKED);
            $queue = $uqueue + $rqueue;
            $playing = $this->duels->duelManager->getTotalMatchesRunning();
        } else {
            $queue = 0;
            $uqueue = 0;
            $rqueue = 0;
            $playing = 0;
        }
        return str_replace(["{player}", "{ping}", "{opponent}", "{oping}", "{kills}", "{deaths}", "{kdr}", "{streak}", "{combat}", "{pearl}", "{queue}", "{uqueue}", "{rqueue}", "{playing}"],
            [
                $player->getName(), $player->getPing(), $opponentName ?? "None", $oping ?? 0,
                $kills, $deaths, $kdr, $streak, $combat, $pearl, $queue, $uqueue, $rqueue, $playing
            ],
            $string);
    }
    /**
     * @param Player $player
     * @param bool $enabled
     */
    public function setStateForPlayer(Player $player, bool $enabled): void{
        $this->enabled[$player->getId()] = $enabled;
    }
    /**
     * @param Player $player
     * @return bool
     */
    public function getStateForPlayer(Player $player): bool{
        return $this->enabled[$player->getId()] ?? false;
    }
}