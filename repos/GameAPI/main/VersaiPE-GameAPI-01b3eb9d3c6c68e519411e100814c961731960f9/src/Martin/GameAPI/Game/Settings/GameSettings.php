<?php


namespace Martin\GameAPI\Game\Settings;


class GameSettings
{
    private bool $allowSpectators = true;
    private int $maxHealth = 20;
    private int $minimumTeamSize = 2;
    private int $maximumTeamSize = 4;

    /**
     * @return bool
     */
    public function isAllowSpectators(): bool
    {
        return $this->allowSpectators;
    }

    /**
     * @param bool $allowSpectators
     */
    public function setAllowSpectators(bool $allowSpectators): void
    {
        $this->allowSpectators = $allowSpectators;
    }

    /**
     * @return int
     */
    public function getMaxHealth(): int
    {
        return $this->maxHealth;
    }

    /**
     * @param int $maxHealth
     */
    public function setMaxHealth(int $maxHealth): void
    {
        $this->maxHealth = $maxHealth;
    }

    /**
     * @return int
     */
    public function getMinimumTeamSize(): int
    {
        return $this->minimumTeamSize;
    }

    /**
     * @param int $minimumTeamSize
     */
    public function setMinimumTeamSize(int $minimumTeamSize): void
    {
        $this->minimumTeamSize = $minimumTeamSize;
    }

    /**
     * @return int
     */
    public function getMaximumTeamSize(): int
    {
        return $this->maximumTeamSize;
    }

    /**
     * @param int $maximumTeamSize
     */
    public function setMaximumTeamSize(int $maximumTeamSize): void
    {
        $this->maximumTeamSize = $maximumTeamSize;
    }
}