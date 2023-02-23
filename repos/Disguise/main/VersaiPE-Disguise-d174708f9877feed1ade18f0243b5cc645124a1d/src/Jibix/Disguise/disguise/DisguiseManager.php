<?php
namespace Jibix\Disguise\disguise;
use CortexPE\Hierarchy\role\Role;
use Jibix\Disguise\Main;
use Jibix\Disguise\task\GiveRandomSkinTask;
use Jibix\Disguise\utils\Utils;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;


/**
 * Class DisguiseManager
 * @package Jibix\Disguise\disguise
 * @author Jibix
 * @date 09.02.2022 - 11:45
 * @project Disguise
 */
class DisguiseManager{
    use SingletonTrait;

    /** @var array */
    public array $disguised = [];

    /**
     * Function isDisguised
     * @param string $playerName
     * @return bool
     */
    public function isDisguised(string $playerName): bool{
        return isset($this->disguised[$playerName]);
    }

    /**
     * Function hasDisguiseRole
     * @param string $playerName
     * @return bool
     */
    public function hasDisguiseRole(string $playerName): bool{
        return isset($this->disguised[$playerName]['role']);
    }

    /**
     * Function getDisguiseInfo
     * @param string $playerName
     * @return array
     */
    public function getDisguiseInfo(string $playerName): array{
        return $this->disguised[$playerName] ?? [];
    }

    /**
     * Function disguisePlayer
     * @param Player $player
     * @param string|null $customName
     * @param Skin|null $customSkin
     * @param int|null $role
     */
    public function disguisePlayer(Player $player, ?string $customName = null, ?Skin $customSkin = null, ?int $role = null): void{
        $skin = $player->getSkin();
        if (!empty($customName)) {
            $name = $customName;
        } else {
            $name = Utils::getRandomName();
        }
        if (!empty($customSkin)) {
            $player->setSkin($customSkin);
            $player->sendSkin();
        } else {
            $url = Main::API_URL . rand(1, 999999) . ".png";
            Server::getInstance()->getAsyncPool()->submitTask(new GiveRandomSkinTask($player->getName(), $url, Main::getInstance()->getDataFolder() . "temp.png"));
        }
        $array = [
            "disguise" => $name,
            "skin" => Utils::skinToArray($skin)
        ];
        if (!empty($role)) $array["role"] = $role;
        $player->setDisplayName($name);
        $player->setNameTag($name);

        //Need to do this bc pm4 is weird
        foreach (Server::getInstance()->getOnlinePlayers() as $online){
            $online->getNetworkSession()->onPlayerRemoved($player);
            $online->getNetworkSession()->onPlayerAdded($player);
        }

        $this->disguised[$player->getName()] = $array;
        $player->sendMessage("§aYour have been disguised to§b {$name}§a!");
    }

    /**
     * Function undisguisePlayer
     * @param Player $player
     */
    public function undisguisePlayer(Player $player): void{
        if (!$this->isDisguised($player->getName())) return;
        $data = $this->getDisguiseInfo($player->getName());
        $skin = $data['skin'];

        $player->setDisplayName($player->getName());
        $player->setNameTag($player->getName());
        $player->setSkin(new Skin($skin['skinId'], $skin['skinData'], $skin['capeData'], $skin['geometryName'], $skin['geometryData']));
        $player->sendSkin();
        $player->sendMessage("§aYou have been undisguised!");

        unset($this->disguised[$player->getName()]);
    }
}
