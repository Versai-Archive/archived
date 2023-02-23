<?php
declare(strict_types=1);

namespace Versai\V6\arena;

use pocketmine\world\Position;

class Arena {

    public function __construct(
        private string $name,
        private array $kitIDs,
        private Position $spawnLocation,
        private array $settings
    ){}

    public function getName(): string {
        return $this->name;
    }

    public function getSpawnLocation(): Position {
        return $this->spawnLocation;
    }

    public function setSpawnLocation(Position $location): void {
        $this->spawnLocation = $location;
    }

    public function getKitIDs(): array {
        return $this->kitIDs;
    }

    public function getSettings(): array {
        return $this->settings;
    }

    public function getProtectionArea(): int {
        return ($this->settings["Protection-Area"] ?? 0);
    }

    public function setProtection(int $protection): void {
        $this->settings["Protection-Area"] = $protection;
    }

    public function isPlaceable(): bool {
        return ($this->settings["Placeable"] ?? true);
    }

    public function setPlaceable(bool $value): void {
        $this->settings["Placeable"] = $value;
    }

    public function isBreakable(): bool {
        return ($this->settings["Breakable"] ?? true);
    }

    public function setBreakable(bool $value): void {
        $this->settings["Breakable"] = $value;
    }

    public function getAllowedBlocksList(): ?array {
        return ($this->settings["Allowed-Block-List"] ?? null);
    }

    public function setAllowedBlocksList(array $blocks): void {
        $this->settings["Allowed-Block-List"] = $blocks;
    }

    public function hasBlockDecay(): bool {
        return ($this->settings["Block-Decay"] ?? false);
    }

    public function setBlockDecay(bool $value): void {
        $this->settings["Block-Decay"] = $value;
    }

    public function getBuildLimit(): int {
        return ($this->settings["Build-Limit"] ?? 256);
    }

    public function setBuildLimit(int $limit): void {
        $this->settings["Build-Limit"] = $limit;
    }

    public function canPickUpItems(): bool {
        return ($this->settings["Item-Pickup"] ?? true);
    }

    public function setCanPickUpItems(bool $value): void {
        $this->settings["Item-Pickup"] = $value;
    }

    public function getKnockback(): float {
        return ($this->settings["Knockback"] ?? 0.4);
    }

    public function setKnockback(float $knockback): void {
        $this->settings["Knockback"] = $knockback;
    }

    public function getHitCooldown(): int {
        return ($this->settings["Hit-Cooldown"] ?? 5);
    }

    public function setHitCooldown(int $hitCooldown): void {
        $this->settings["Hit-Cooldown"] = $hitCooldown;
    }

    public function hasFallDamage(): bool {
        return ($this->settings["Fall-Damage"] ?? true);
    }

    public function setFallDamage(bool $value): void {
        $this->settings["Fall-Damage"] = $value;
    }

    public function hasHungerLoss(): bool {
        return ($this->settings["Hunger-Loss"] ?? true);
    }

    public function setHungerLoss(bool $value): void {
        $this->settings["Hunger-Loss"] = $value;
    }

    public function getAll(): array {
        return [
            "Kit-IDs" => $this->getKitIDs(),
            "Spawn" => implode(":", [round($this->spawnLocation->getX(), 1), round($this->spawnLocation->getY()), round($this->spawnLocation->getZ(), 1)]),
            "Settings" => $this->settings
        ];
    }

    public function getAllInfo(): array {
        $arr = [];
        foreach($this->settings as $setting => $value) {
            if($setting === "Allowed-Block-List"){
                continue;
            }elseif($value === true || $value === false) {
                $arr[] = "- $setting: {$this->boolToString($value)}";
            }else{
                $arr[] = "- $setting: $value";
            }
        }
        return $arr;
    }

    public function boolToString(bool $bool): string {
        return $bool ? "true" : "false";
    }
}