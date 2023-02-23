<?php
namespace Jibix\Disguise\utils;
use Jibix\Disguise\Main;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\Server;


/**
 * Class Utils
 * @package Jibix\Disguise\utils
 * @author Jibix
 * @date 08.02.2022 - 23:35
 * @project Disguise
 */
class Utils{

    /**
     * Function getRandomName
     * @return string
     */
    public static function getRandomName(): string{
        $file = Main::getInstance()->getNameFile();
        $names = $file->get("names", []);
        $name = $names[array_rand($names)];
        if (mt_rand(1, 100) > 60) return $name;

        $tags = $file->get("tags", []);
        $frontTags = $file->get("frontTags", []);
        if (mt_rand(1, 4) == 2) $name = str_replace($name, $frontTags[array_rand($frontTags)] . ucwords($name), $name);
        if (mt_rand(1, 3) == 3) {
            if (mt_rand(1, 4) == 3) $name .= "_" . $tags[array_rand($tags)];
            else $name .= $tags[array_rand($tags)];
        } else {
            if (mt_rand(1, 4) == 4) $name .= mt_rand(1, 1200);
        }
        if (mt_rand(1, 6) == 5) $name = str_replace($name[array_rand((array)$name)], $name[array_rand((array)$name)], $name);
        return $name;
    }

    /**
     * Function getPlayerList
     * @param Player $player
     * @return array
     */
    public static function getPlayerList(Player $player): array{
        $players = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $online) {
            if ($online->getName() === $player->getName()) continue;
            $players[] = $online->getName();
        }
        sort($players);
        return $players;
    }

    /**
     * Function skinToArray
     * @param Skin $skin
     * @return array
     */
    public static function skinToArray(Skin $skin): array{
        return [
            "skinId" => $skin->getSkinId(),
            "skinData" => $skin->getSkinData(),
            "capeData" => $skin->getCapeData(),
            "geometryName" => $skin->getGeometryName(),
            "geometryData" => $skin->getGeometryData()
        ];
    }
}