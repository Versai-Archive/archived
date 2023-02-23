<?php

namespace Martin\GameAPI\Types;

/**
 * Class WorldRuleType
 * @package Martin\GameAPI\Types
 * @deprecated In favor of GameAPI/Game/Settings/GameRules
 */
class WorldRuleType
{
    public const BUILD_MAP = 1;
    public const BREAK_MAP = 2;
    public const BREAK_PLAYER = 3;
    public const FIRE_TICK = 4;

    public const INSTANT_RESPAWN = 10;

    public const KEEP_INVENTORY = 20;
    public const DROPS = 21;

    public const HUNGER = 30;
    public const FALL_DAMAGE = 31;
    public const FIRE_DAMAGE = 32;
    public const DROWNING_DAMAGE = 33;
    public const NATURAL_REGENERATION = 35;
    public const NON_NATURAL_REGENERATION = 36;

    public static function buildDefaultSettings(): array
    {
        return [
            self::HUNGER => true,
            self::BUILD_MAP => false,
            self::BREAK_MAP => false,
            self::BREAK_PLAYER => false,
            self::FIRE_TICK => true,
            self::INSTANT_RESPAWN => false,
            self::KEEP_INVENTORY => false,
            self::DROPS => true,
            self::FALL_DAMAGE => true,
            self::FIRE_DAMAGE => true,
            self::DROWNING_DAMAGE => true,
            self::NATURAL_REGENERATION => true,
            self::NON_NATURAL_REGENERATION => true
        ];
    }
}