<?php

namespace Martin\RedRover\Form;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Toggle;
use Martin\GameAPI\Game\Maps\Map;
use Martin\RedRover\Main;
use pocketmine\Player;

class CreateForm extends CustomForm
{
    public function __construct()
    {
        $maps = Main::getInstance()->getMaps();

        parent::__construct(Main::getInstance()->getMessage("forms.create.title", [], false), [
            new Dropdown("map", "Select a map", array_map(static function (Map $map): string {
                return $map->getName();
            }, $maps)),
            new Toggle("private", "Private event (Requires code)", false)
        ], function (Player $player, CustomFormResponse $response) use ($maps): void {
            $map = $maps[$response->getInt("map")];
            $private = $response->getBool("private");
            $private_text = $private === true ? "private" : "public";
            $player->chat("./redrover create {$map->getName()} $private_text");
        });
    }
}