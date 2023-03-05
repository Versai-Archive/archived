<?php
declare(strict_types=1);

namespace ARTulloss\Duels\Tasks;

use pocketmine\scheduler\Task;
use ARTulloss\Duels\Duels;

class GappleCooldownTask extends Task{

    /** @var Duels $duels */
    private $duels;
    /** @var array $gappleCooldowns */
    private $gappleCooldowns = [];

    public function __construct(Duels $duels){
        $this->duels = $duels;
    }

    public function onRun(int $currentTick){
        foreach ($this->gappleCooldowns as $name => $cooldown){
            if($cooldown !== 0){
                $this->setGappleCooldown($name, ($cooldown - 1));
            }
        }
    }

    public function setGappleCooldown(string $name, int $cooldown = 20){
        $this->gappleCooldowns[$name] = $cooldown;
    }
    public function getGappleCooldown(string $name){
        return $this->gappleCooldowns[$name];
    }
}