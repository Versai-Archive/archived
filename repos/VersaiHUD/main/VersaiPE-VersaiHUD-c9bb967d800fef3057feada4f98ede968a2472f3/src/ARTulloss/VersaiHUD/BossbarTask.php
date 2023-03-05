<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/15/2020
 * Time: 5:21 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiHUD;

use ARTulloss\Cooldown\Cooldown;
use ARTulloss\Duels\Duels;
use ARTulloss\Scoreboards\Main;
use pocketmine\Player;
use pocketmine\Server;
use xenialdan\apibossbar\DiverseBossBar;
use Exception;
use function rand;
use function array_rand;
use function count;

class BossbarTask extends HUDTask {
    /** @var DiverseBossBar $bossbar */
    private $bossbar;
    /** @var float[] $percentages */
    private $percentages;
    /** @var float $globalPercentage */
    private $globalPercentage;
    /** @var string[] $titles */
    private $titles;
    /** @var string[] $globalTitles */
    private $globalTitles;
    /** @var string[] $subtitles */
    private $subtitles;
    /** @var string[] $globalSubtitles */
    private $globalSubtitles;
    /** @var bool[] $bossbarEnabled */
    private $bossbarEnabled;
    /** @var int $mode */
    private $mode;
    /** @var int $index */
    private $index = 0;
    /** @var int $titleIndex */
    private $titleIndex = 0;
    /** @var int $subtitleIndex */
    private $subtitleIndex = 0;


    public const MODE_CYCLE = 1;
    public const MODE_RANDOM = 2;
    public const MODE_ERROR = 3;

    /**
     * BossbarTask constructor.
     * @param Main|null $scoreboards
     * @param Cooldown|null $cooldown
     * @param Duels|null $duels
     * @param array $globalTitles
     * @param array $globalSubtitles
     * @param int $mode
     * @throws Exception
     */
    public function __construct(?Main $scoreboards, ?Cooldown $cooldown, ?Duels $duels, array $globalTitles, array $globalSubtitles, int $mode) {
        parent::__construct($scoreboards, $cooldown, $duels);
        $this->bossbar = new DiverseBossBar();
        $this->setGlobalTitles($globalTitles);
        $this->setGlobalSubtitles($globalSubtitles);
        if($mode === self::MODE_ERROR)
            throw new Exception('Invalid mode provided');
        $this->mode = $mode;
    }
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();
        $this->bossbar->addPlayers($onlinePlayers);
        $titleCount = count($this->globalTitles);
        $subtitleCount = count($this->globalSubtitles);
        $maxIndex = Utilities::lcm($titleCount, $subtitleCount);
        if($this->mode === self::MODE_CYCLE) {
            $this->index++;
            if($this->index === $maxIndex)
                $this->index = 0;

            $this->titleIndex++;
            if($this->titleIndex === $titleCount)
                $this->titleIndex = 0;

            $this->subtitleIndex++;
            if($this->subtitleIndex === $subtitleCount)
                $this->subtitleIndex = 0;
            $this->setGlobalPercentage($this->index / ($maxIndex - 1));
        } else
            $this->setGlobalPercentage(rand(0, 100) / 100);
        $this->bossbar->showToAll();
        foreach ($onlinePlayers as $player) {
            if(!$this->getStateForPlayer($player)) {
                $this->bossbar->removePlayer($player);
                continue;
            }
            $id = $player->getId();
            if(isset($this->percentages[$id]))
                $this->bossbar->setPercentageFor([$player], $this->percentages[$id]);
            else
                $this->bossbar->setPercentageFor([$player], $this->globalPercentage);
            if(isset($this->titles[$id]))
                $this->bossbar->setTitleFor([$player], $this->replaceString($player, $this->titles[$id]));
            else
                $this->bossbar->setTitleFor([$player], $this->mode === self::MODE_CYCLE ?
                    $this->replaceString($player, $this->globalTitles[$this->titleIndex]) :
                    $this->replaceString($player, $this->globalTitles[array_rand($this->globalTitles)]));
            if(isset($this->subtitles[$id]))
                $this->bossbar->setSubTitleFor([$player], $this->replaceString($player, $this->subtitles[$id]));
            else
                $this->bossbar->setSubTitleFor([$player], $this->mode === self::MODE_CYCLE ?
                    $this->replaceString($player, $this->globalSubtitles[$this->subtitleIndex]) :
                    $this->replaceString($player, $this->globalSubtitles[array_rand($this->globalSubtitles)]));
        }
    }
    /**
     * @param array $titles
     */
    public function setGlobalTitles(array $titles): void{
        $this->globalTitles = $titles;
    }
    /**
     * @return array
     */
    public function getGlobalTitles(): array{
        return $this->globalTitles;
    }
    /**
     * @param array $subtitles
     */
    public function setGlobalSubtitles(array $subtitles): void{
        $this->globalSubtitles = $subtitles;
    }
    /**
     * @return array
     */
    public function getGlobalSubtitles(): array{
        return $this->globalSubtitles;
    }
    /**
     * @param float $percentage
     */
    private function setGlobalPercentage(float $percentage): void{
        $this->globalPercentage = $percentage;
    }
    /**
     * @return float
     */
    public function getGlobalPercentage(): float{
        return $this->globalPercentage;
    }
    /**
     * @param Player $player
     * @param string $title
     */
    public function setTitleForPlayer(Player $player, string $title): void{
        $this->titles[$player->getId()] = $title;
        $this->bossbar->setTitleFor([$player], $title);
    }
    /**
     * @param Player $player
     * @return string|null
     */
    public function getTitleForPlayer(Player $player): ?string{
        return $this->titles[$player->getId()] ?? null;
    }
    /**
     * @param Player $player
     */
    public function unsetTitleForPlayer(Player $player): void{
        unset($this->titles[$player->getId()]);
    }
    /**
     * @param Player $player
     * @param string $subtitle
     */
    public function setSubtitleForPlayer(Player $player, string $subtitle): void{
        $this->subtitles[$player->getId()] = $subtitle;
        $this->bossbar->setSubTitleFor([$player], $subtitle);
    }
    /**
     * @param Player $player
     * @return string|null
     */
    public function getSubtitleForPlayer(Player $player): ?string{
        return $this->subtitles[$player->getId()] ?? null;
    }
    /**
     * @param Player $player
     */
    public function unsetSubtitleForPlayer(Player $player): void{
        unset($this->subtitles[$player->getId()]);
    }
    /**
     * @param Player $player
     * @param float $percentage
     */
    public function setPercentageForPlayer(Player $player, float $percentage): void{
        $this->percentages[$player->getId()] = $percentage;
        $this->bossbar->setPercentageFor([$player], $this->percentages[$player->getName()]);
    }
    /**
     * @param Player $player
     * @return float|null
     */
    public function getPercentageForPlayer(Player $player): ?float{
        return $this->percentages[$player->getId()] ?? null;
    }
    /**
     * @param Player $player
     */
    public function unsetPercentageForPlayer(Player $player): void{
        unset($this->percentages[$player->getId()]);
    }
}