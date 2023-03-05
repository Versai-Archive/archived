<?php

declare(strict_types=1);

namespace ARTulloss\VersaiHUD;

use ARTulloss\Cooldown\Cooldown;
use ARTulloss\Duels\Duels;
use ARTulloss\Scoreboards;
use pocketmine\plugin\PluginBase;
use function strtolower;

class Main extends PluginBase{
    /** @var BossbarTask $bossbarTask */
    private $bossbarTask;
    /** @var ScoreboardTask $scoreboardTask */
    private $scoreboardTask;
    /**
     * @throws \Exception
     */
	public function onEnable(): void{
	    $configArray = $this->getConfig()->getAll();
	    $scheduler = $this->getScheduler();
	    $pluginManager = $this->getServer()->getPluginManager();
	    /** @var Scoreboards\Main $scoreboards */
	    $scoreboards = $pluginManager->getPlugin('Scoreboards');
	    /** @var Cooldown $cooldown */
	    $cooldown = $pluginManager->getPlugin('Cooldown');
	    /** @var Duels $duels */
	    $duels = $pluginManager->getPlugin('Duels');
	    $scheduler->scheduleRepeatingTask($this->bossbarTask = new BossbarTask($scoreboards, $cooldown, $duels, $configArray['Bossbar']['Titles'], $configArray['Bossbar']['Subtitles'], $this->resolveMode($configArray['Bossbar']['Mode'])), 20);
	    $scheduler->scheduleRepeatingTask($this->scoreboardTask = new ScoreboardTask($scoreboards, $cooldown, $duels, new ScoreboardAPI($this), $configArray['Scoreboard']), 20);
	}
    /**
     * @param string $modeString
     * @return int
     */
	public function resolveMode(string $modeString): int{
	    $modeString = strtolower($modeString);
	    if($modeString === 'cycle')
	        return BossbarTask::MODE_CYCLE;
	    if($modeString === 'random')
	        return BossbarTask::MODE_RANDOM;
	    return BossbarTask::MODE_ERROR;
    }
    /**
     * @return BossbarTask
     */
    public function getBossbarTask(): BossbarTask{
	    return $this->bossbarTask;
    }
    public function getScoreboardTask(): ScoreboardTask{
        return $this->scoreboardTask;
    }
}
