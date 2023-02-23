<?php

namespace versai\gameapi\arena;

use pocketmine\world\World;

class Arena
{
    /**
     * @var ArenaPosition[]
     */
    private array $positons;

    public function __construct(
        private string $name,
        private World $world,
        ArenaPosition ...$positions
    ) {
        $this->positons = $positions;
    }

    public function getPosition(int $i): ?ArenaPosition {
        return $this->positons[$i] ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWorld(): World
    {
        return $this->world;
    }
}