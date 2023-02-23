<?php
namespace Jibix\Disguise\form;
use CortexPE\Hierarchy\Hierarchy;
use Frago9876543210\EasyForms\elements\Dropdown;
use Frago9876543210\EasyForms\elements\Input;
use Frago9876543210\EasyForms\elements\Label;
use Frago9876543210\EasyForms\elements\Toggle;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;
use Jibix\Disguise\disguise\DisguiseManager;
use Jibix\Disguise\Main;
use Jibix\Disguise\utils\Utils;
use pocketmine\player\Player;
use pocketmine\Server;


/**
 * Class DisguiseForm
 * @package Jibix\Disguise\form
 * @author Jibix
 * @date 09.02.2022 - 11:27
 * @project Disguise
 */
class DisguiseForm{

    /**
     * @param Player $player
     * @param string|null $message
     */
    public function __construct(Player $player, ?string $message = null){
        $roles = Main::getInstance()->getConfig()->get("disguise")["roles"];

        if (!empty($message)) $elements[] = new Label($message);
        $elements[] = new Toggle("Disguise", DisguiseManager::getInstance()->isDisguised($player->getName()));
        if ($player->hasPermission("disguise.use.ownName")) $elements["ownName"] = new Input("Disguise Name (leave empty for random)", "xProGamerHD");
        if ($player->hasPermission("disguise.use.stealSkin")) $elements["skinStealer"] = new Dropdown("Skin Stealer", array_merge(["§cRandom Skin"], Utils::getPlayerList($player)));
        if ($player->hasPermission("disguise.use.role")) $elements["role"] = new Dropdown("Diguise Role", array_keys($roles));
        $player->sendForm(new CustomForm(
            "§bDisguise",
            array_values($elements),
            function (Player $player, CustomFormResponse $response) use ($elements, $roles): void{
                if (!$response->getToggle()->getValue()) {
                    if (DisguiseManager::getInstance()->isDisguised($player->getName())) DisguiseManager::getInstance()->undisguisePlayer($player);
                } else {
                    if (isset($elements["ownName"])) $name = $response->getInput()->getValue();
                    if (isset($elements["skinStealer"])) $skinPlayer = $response->getDropdown()->getSelectedOption();
                    if (isset($elements["role"])) $role = $roles[$response->getDropdown()->getSelectedOption()];
                    if (!empty($name)) {
                        if (is_file(Server::getInstance()->getDataPath() . "/players/" . $name . ".dat")) {
                            new self($player, "§cYou can't use a name of an existing player!");
                            return;
                        }
                        if (strlen($name) < 5 || strlen($name) > 16) {
                            new self($player, "§cPlease use min 5 and max 16 chars for your disguise name!");
                            return;
                        }
                        #Todo: Add an insult check for the name!
                    }
                    if (!empty($skinPlayer) && $skinPlayer !== "§cRandom Skin") {
                        if (!($online = Server::getInstance()->getPlayerExact($skinPlayer))) {
                            new self($player, "§cThe selected player is not online anymore!");
                            return;
                        }
                        $skin = $online->getSkin();
                    }
                    DisguiseManager::getInstance()->disguisePlayer($player, $name ?? null, $skin ?? null, $role ?? null);
                }
            }
        ));
    }
}