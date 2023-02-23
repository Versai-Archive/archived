<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Database\Container;

use function strtolower;

class PlayerDataContainer{

    /** @var PlayerData[] $playerData */
    private array $playerData;

    /**
     * @param PlayerData $data
     */
    public function set(PlayerData $data): void{
        $this->playerData[strtolower($data->getName())] = $data;
    }

    /**
     * @param string $name
     */
    public function unset(string $name): void{
        unset($this->playerData[strtolower($name)]);
    }

    /**
     * @param string $name
     * @return PlayerData|null
     */
    public function get(string $name): ?PlayerData{
        return $this->playerData[strtolower($name)] ?? null;
    }
}
