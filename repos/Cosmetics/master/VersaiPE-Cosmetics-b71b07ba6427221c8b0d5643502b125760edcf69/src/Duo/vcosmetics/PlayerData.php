<?php
declare(strict_types=1);

namespace Duo\vcosmetics;

class PlayerData {

    private array $xuids = [];

    public function setXUID(string $name, $xuid){
        $this->xuids[$name] = $xuid;
    }

    public function getXUID(string $name){
        return $this->xuids[$name];
    }
}