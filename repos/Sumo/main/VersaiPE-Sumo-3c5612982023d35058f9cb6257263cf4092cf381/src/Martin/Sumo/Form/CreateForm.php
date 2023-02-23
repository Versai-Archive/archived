<?php


namespace Martin\Sumo\Form;


use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use Martin\Sumo\Main;
use pocketmine\Player;

class CreateForm extends CustomForm
{
    public function __construct(Main $plugin)
    {
        $maps = $plugin->getMaps();
        $mapOptions = [];

        foreach ($maps as $map) {
            $mapOptions[] = $map->getName() . " made by " . $map->getAuthor();
        }

        parent::__construct("Sumo - Create", [new Dropdown("map", "Select a map", $mapOptions)], function (Player $player, CustomFormResponse $response) use ($maps): void {
            $map = $maps[$response->getInt("map")];
            $player->chat("/sumo create {$map->getName()}");
        });
    }
}