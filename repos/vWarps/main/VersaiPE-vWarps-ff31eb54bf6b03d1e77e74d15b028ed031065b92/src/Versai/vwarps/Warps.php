<?php
declare(strict_types=1);

namespace Versai\vwarps;

class Warps{

    /** @var Warp[] $warps */
    private array $warps;

    /**
     * @param string $warpName
     * @return Warp|null
     */
    public function getWarp(string $warpName): ?Warp {
        return $this->warps[$warpName] ?? null;
    }

    /**
     * @return Warp[]|null
     */
    public function getWarps(): ?array {
        return $this->warps;
    }

    /**
     * @param Warp $warp
     */
    public function addWarp(Warp $warp): void {
        $this->warps[$warp->getName()] = $warp;
    }

    /**
     * @param Warp $warp
     * @return bool
     */
    public function removeWarp(Warp $warp): bool {
        return $this->removeWarpByName($warp->getName());
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeWarpByName(string $name): bool {
        $value = isset($this->warps[$name]);
        if($value) {
            unset($this->warps[$name]);
        }
        return $value;
    }
}