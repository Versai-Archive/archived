<?php


namespace Martin\GameAPI\Game\Settings;


class GameRules
{
    private bool $build = false; # checked
    private bool $break = false; # checked
    private bool $breakFromPlayers = false; # checked
    private bool $instantRespawn = false;
    private bool $keepInventory = false; # checked
    private bool $itemsDrop = false; # checked
    private bool $xpDrop = false; # checked
    private bool $takeHunger = false; # checked
    private bool $takeFallDamage = true; # checked
    private bool $takeDrowningDamage = true; # checked
    private bool $regenNatural = true; # checked
    private bool $regenNonNatural = true;
    private bool $pickUp = true;

    /**
     * @return bool
     */
    public function canBuild(): bool
    {
        return $this->build;
    }

    /**
     * @param bool $build
     */
    public function setBuild(bool $build): void
    {
        $this->build = $build;
    }

    /**
     * @return bool
     */
    public function canBreak(): bool
    {
        return $this->break;
    }

    /**
     * @param bool $break
     */
    public function setBreak(bool $break): void
    {
        $this->break = $break;
    }

    /**
     * @return bool
     */
    public function canBreakFromPlayers(): bool
    {
        return $this->breakFromPlayers;
    }

    /**
     * @param bool $breakFromPlayers
     */
    public function setBreakFromPlayers(bool $breakFromPlayers): void
    {
        $this->breakFromPlayers = $breakFromPlayers;
    }

    /**
     * @return bool
     */
    public function canInstantRespawn(): bool
    {
        return $this->instantRespawn;
    }

    /**
     * @param bool $instantRespawn
     */
    public function setInstantRespawn(bool $instantRespawn): void
    {
        $this->instantRespawn = $instantRespawn;
    }

    /**
     * @return bool
     */
    public function keepInventory(): bool
    {
        return $this->keepInventory;
    }

    /**
     * @param bool $keepInventory
     */
    public function setKeepInventory(bool $keepInventory): void
    {
        $this->keepInventory = $keepInventory;
    }

    /**
     * @return bool
     */
    public function doItemsDrop(): bool
    {
        return $this->itemsDrop;
    }

    /**
     * @param bool $itemsDrop
     */
    public function setItemsDrop(bool $itemsDrop): void
    {
        $this->itemsDrop = $itemsDrop;
    }

    /**
     * @return bool
     */
    public function canTakeHunger(): bool
    {
        return $this->takeHunger;
    }

    /**
     * @param bool $takeHunger
     */
    public function setTakeHunger(bool $takeHunger): void
    {
        $this->takeHunger = $takeHunger;
    }

    /**
     * @return bool
     */
    public function canTakeFallDamage(): bool
    {
        return $this->takeFallDamage;
    }

    /**
     * @param bool $takeFallDamage
     */
    public function setTakeFallDamage(bool $takeFallDamage): void
    {
        $this->takeFallDamage = $takeFallDamage;
    }

    /**
     * @return bool
     */
    public function canTakeDrowningDamage(): bool
    {
        return $this->takeDrowningDamage;
    }

    /**
     * @param bool $takeDrowningDamage
     */
    public function setTakeDrowningDamage(bool $takeDrowningDamage): void
    {
        $this->takeDrowningDamage = $takeDrowningDamage;
    }

    /**
     * @return bool
     */
    public function canRegenNatural(): bool
    {
        return $this->regenNatural;
    }

    /**
     * @param bool $regenNatural
     */
    public function setRegenNatural(bool $regenNatural): void
    {
        $this->regenNatural = $regenNatural;
    }

    /**
     * @return bool
     */
    public function canRegenNonNatural(): bool
    {
        return $this->regenNonNatural;
    }

    /**
     * @param bool $regenNonNatural
     */
    public function setRegenNonNatural(bool $regenNonNatural): void
    {
        $this->regenNonNatural = $regenNonNatural;
    }

    /**
     * @return bool
     */
    public function doXpDrop(): bool
    {
        return $this->xpDrop;
    }

    /**
     * @param bool $xpDrop
     */
    public function setXpDrop(bool $xpDrop): void
    {
        $this->xpDrop = $xpDrop;
    }

    /**
     * @return bool
     */
    public function canPickUp(): bool
    {
        return $this->pickUp;
    }

    /**
     * @param bool $pickUp
     */
    public function setPickUp(bool $pickUp): void
    {
        $this->pickUp = $pickUp;
    }
}