<?php
declare(strict_types=1);

namespace Versai\arenas;

use pocketmine\player\Player;
use pocketmine\world\Position;

class Arena
{

    private string $name;
    private array $kitIDs, $settings;
    private int $spawnRadius;
    private Position $spawnLocation;

    public function __construct(string $name, array $kitIDs, Position $spawnLocation, array $settings, int $spawnRadius = 0)
    {
        $this->name = $name;
        $this->kitIDs = $kitIDs;
        $this->spawnLocation = $spawnLocation;
        $this->settings = $settings;
        $this->spawnRadius = $spawnRadius;
    }

    public function getSpawnRadius(): int
    {
        return $this->spawnRadius;
    }

    public function setSpawnRadius(int $radius): void
    {
        $this->spawnRadius = $radius;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSpawnLocation(): Position
    {
        return $this->spawnLocation;
    }

    public function setSpawnLocation(Position $location): void
    {
        $this->spawnLocation = $location;
    }

    public function getKitIDs(): array
    {
        return $this->kitIDs;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getProtectionArea(): int
    {
        return ($this->settings["Protection-Area"] ?? 0);
    }

    public function setProtection(int $protection): void
    {
        $this->settings["Protection-Area"] = $protection;
    }

    public function isPlaceable(): bool
    {
        return ($this->settings["Placeable"] ?? true);
    }

    public function setPlaceable(bool $value): void
    {
        $this->settings["Placeable"] = $value;
    }

    public function isBreakable(): bool
    {
        return ($this->settings["Breakable"] ?? true);
    }

    public function setBreakable(bool $value): void
    {
        $this->settings["Breakable"] = $value;
    }

    public function getAllowedBlocksList(): ?array
    {
        return ($this->settings["Allowed-Block-List"] ?? null);
    }

    public function setAllowedBlocksList(array $blocks): void
    {
        $this->settings["Allowed-Block-List"] = $blocks;
    }

    public function hasBlockDecay(): bool
    {
        return ($this->settings["Block-Decay"] ?? false);
    }

    public function setBlockDecay(bool $value): void
    {
        $this->settings["Block-Decay"] = $value;
    }

    public function getBuildLimit(): int
    {
        return ($this->settings["Build-Limit"] ?? 256);
    }

    // This function is used by vWarp to teleport players on arenas
    public function teleportToSpawn(Player $player, ?float $yaw = null, ?float $pitch = null): void
    {
        if ($player instanceof Player) {
            if ($this->getSpawnRadius() > 0) {
                $player->teleport(new Position(
                    random_int($this->getSpawnLocation()->getX() - $this->getSpawnRadius(), $this->getSpawnLocation()->getX() + $this->getSpawnRadius()), //random x
                    $this->getSpawnLocation()->getY(),
                    random_int($this->getSpawnLocation()->getZ() - $this->getSpawnRadius(), $this->getSpawnLocation()->getZ() + $this->getSpawnRadius()), // random z
                    $this->getSpawnLocation()->getWorld()
                ), $yaw, $pitch);
            } else {
                $player->teleport($this->getSpawnLocation(), $yaw, $pitch);
            }
        }

    }

    public function setBuildLimit(int $limit): void
    {
        $this->settings["Build-Limit"] = $limit;
    }

    public function canPickUpItems(): bool
    {
        return ($this->settings["Item-Pickup"] ?? true);
    }

    public function setCanPickUpItems(bool $value): void
    {
        $this->settings["Item-Pickup"] = $value;
    }

    public function getKnockback(): float
    {
        return ($this->settings["Knockback"] ?? 0.4);
    }

    public function setKnockback(float $knockback): void
    {
        $this->settings["Knockback"] = $knockback;
    }

    public function getHitCooldown(): int
    {
        return ($this->settings["Hit-Cooldown"] ?? 5);
    }

    public function setHitCooldown(int $hitCooldown): void
    {
        $this->settings["Hit-Cooldown"] = $hitCooldown;
    }

    public function hasFallDamage(): bool
    {
        return ($this->settings["Fall-Damage"] ?? true);
    }

    public function setFallDamage(bool $value): void
    {
        $this->settings["Fall-Damage"] = $value;
    }

    public function hasHungerLoss(): bool
    {
        return ($this->settings["Hunger-Loss"] ?? true);
    }

    public function setHungerLoss(bool $value): void
    {
        $this->settings["Hunger-Loss"] = $value;
    }

    public function getAll(): array
    {
        return [
            "Kit-IDs" => $this->getKitIDs(),
            "spawn" => implode(":", [round($this->spawnLocation->getX(), 1), round($this->spawnLocation->getY()), round($this->spawnLocation->getZ(), 1)]),
            "spawn-radius" => $this->getSpawnRadius(),
            "settings" => $this->settings
        ];
    }

    public function getAllInfo(): array
    {
        $arr = [];
        foreach ($this->settings as $setting => $value) {
            if ($setting === "Allowed-Block-List") {
                continue;
            } elseif ($value === true || $value === false) {
                $arr[] = "- $setting: {$this->boolToString($value)}";
            } else {
                $arr[] = "- $setting: $value";
            }
        }
        return $arr;
    }

    public function boolToString(bool $bool): string
    {
        return $bool ? "true" : "false";
    }
}