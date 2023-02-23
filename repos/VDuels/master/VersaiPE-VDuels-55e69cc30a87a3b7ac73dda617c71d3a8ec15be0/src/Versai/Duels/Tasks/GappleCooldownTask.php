<?php
declare(strict_types=1);

namespace Versai\Duels\Tasks;

use pocketmine\scheduler\Task;
use Versai\Duels\Duels;

class GappleCooldownTask extends Task{

    /** @var Duels $duels */
    private Duels $duels;
    /** @var array $gappleCooldowns */
    private array $gappleCooldowns = [];

    public function __construct(Duels $duels){
        $this->duels = $duels;
    }

    public function onRun(): void{
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