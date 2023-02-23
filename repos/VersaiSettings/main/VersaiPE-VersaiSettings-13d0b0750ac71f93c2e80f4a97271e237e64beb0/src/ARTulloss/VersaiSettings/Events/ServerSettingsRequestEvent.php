<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/11/2020
 * Time: 3:11 PM
 */
declare(strict_types=1);

namespace ARTulloss\VersaiSettings\Events;

use dktapps\pmforms\ServerSettingsForm;
use pocketmine\event\player\PlayerEvent;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\Player;
use ReflectionClass;
use ReflectionException;

class ServerSettingsRequestEvent extends PlayerEvent{
    /**
     * ServerSettingsRequestEvent constructor.
     * @param Player $player
     */
    public function __construct(Player $player) {
        $this->player = $player;
    }
    /**
     * @param ServerSettingsForm $form
     * @param Player $player
     */
    static public function sendFormToPlayer(ServerSettingsForm $form, Player $player): void{
        try {
            $refClass = new ReflectionClass(Player::class);
            $refProp = $refClass->getProperty("formIdCounter");
            $refProp->setAccessible(true);
            $refProp->setValue($player, ($id = $refProp->getValue($player) + 1));
            $refProp = $refClass->getProperty("forms");
            $refProp->setAccessible(true);
            $forms = $refProp->getValue($player);
            $forms[$id] = $form;
            $refProp->setValue($player, $forms);
        } catch (ReflectionException $error) {
            $player->getServer()->getPluginManager()->getPlugin('VersaiSettings')->getLogger()->error($error->getMessage());
            return;
        }

        $pk = new ServerSettingsResponsePacket();

        $pk->formId = $id;
        $pk->formData = json_encode($form->jsonSerialize());

        $player->dataPacket($pk);
    }
}