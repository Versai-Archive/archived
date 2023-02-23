<?php

declare(strict_types=1);

namespace Versai\RPG\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use Versai\RPG\Libs\FormAPI\window\CustomWindowForm;
use Versai\RPG\Main;
use Versai\RPG\RPGPlayer;

class Settings extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if (!$sender instanceof RPGPlayer) return false;

        $language = $sender->getCustomLanguage();

        $translator = Main::getInstance()->getTranslator();

        $form = new CustomWindowForm("player_settings", $translator->translate("raw.settings", [], $language), $translator->translate("descriptions.settings.player", [], $language), function(RPGPlayer $player, CustomWindowForm $data) {
            $language = $data->getElement("language")->getFinalValue();

            // Make not hardcoded?
            $language = match (strtolower($language)) {
                "english" => "en_US",
                "german" => "de_DE",
                default => $player->getCustomLanguage()
            };

            $player->setLanguage($language);
        });

        $val = match($sender->getCustomLanguage()) {
            "en_US" => 0,
            "de_DE" => 1,
            default => 0
        };

        $form->addDropdown("language", "Language", ["English", "German"], $val);

        $sender->sendForm($form);
    }

}