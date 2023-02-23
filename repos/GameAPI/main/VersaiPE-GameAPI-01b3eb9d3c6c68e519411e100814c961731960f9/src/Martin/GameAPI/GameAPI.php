<?php


namespace Martin\GameAPI;


use pocketmine\Player;

class GameAPI
{
    public const VERSION = "1.0.0";

    /** @var GamePlugin[] */
    private static array $plugins = [];

    public static function registerGame(GamePlugin $plugin): void
    {
        if (in_array($plugin, self::$plugins)) {
            return;
        }

        self::$plugins[] = $plugin;
    }

    public static function inGame(Player $player): bool
    {
        foreach (self::$plugins as $gamePlugin) {
            if ($gamePlugin->inGame($player)) {
                return true;
            }
        }

        return false;
    }
}