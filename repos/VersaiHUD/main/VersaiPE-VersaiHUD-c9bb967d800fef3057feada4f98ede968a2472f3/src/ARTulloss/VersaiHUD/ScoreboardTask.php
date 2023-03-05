<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/17/2020
 * Time: 5:59 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiHUD;

use ARTulloss\Cooldown\Cooldown;
use ARTulloss\Duels\Duels;
use ARTulloss\Scoreboards\Main;
use pocketmine\Player;
use pocketmine\Server;
use function array_slice;

class ScoreboardTask extends HUDTask{
    /** @var ScoreboardAPI $scoreboardAPI */
    private $scoreboardAPI;
    /** @var array[] */
    private $scoreboards;
    /** @var array[] $globalScoreboards */
    private $globalScoreboards;
    /**
     * ScoreboardTask constructor.
     * @param Main|null $scoreboards
     * @param Cooldown|null $cooldown
     * @param Duels|null $duels
     * @param ScoreboardAPI $api
     * @param array $globalScoreboards
     */
    public function __construct(?Main $scoreboards, ?Cooldown $cooldown, ?Duels $duels, ScoreboardAPI $api, array $globalScoreboards) {
        parent::__construct($scoreboards, $cooldown, $duels);
        $this->scoreboardAPI = $api;
        $this->globalScoreboards = $globalScoreboards;
    }
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void{
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();
        foreach ($onlinePlayers as $player) {
            if(!$this->getStateForPlayer($player)) {
                $this->scoreboardAPI->remove($player);
                continue;
            }
            $scoreboard = $this->getScoreboardForPlayer($player);
            if($scoreboard === null)
                $scoreboard = $this->globalScoreboards[$player->getLevel()->getName()] ?? $this->globalScoreboards['Default'];
            $this->scoreboardAPI->new($player, 'VersaiHUD', $scoreboard[0]);
            $scoreboard = array_slice($scoreboard, 1);
            foreach ($scoreboard as $index => $line) {
                $this->scoreboardAPI->setLine($player, ++$index, $this->replaceString($player, $line));
            }
        }
    }
    /**
     * @param Player $player
     * @param array $scoreboard
     */
    public function setScoreboardForPlayer(Player $player, array $scoreboard): void{
        $this->scoreboards[$player->getId()] = $scoreboard;
    }
    /**
     * @param Player $player
     * @return array|null
     */
    public function getScoreboardForPlayer(Player $player): ?array{
        return $this->scoreboards[$player->getId()] ?? null;
    }
}