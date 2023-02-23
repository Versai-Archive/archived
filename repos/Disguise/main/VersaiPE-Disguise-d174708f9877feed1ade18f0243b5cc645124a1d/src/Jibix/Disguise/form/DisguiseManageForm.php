<?php
namespace Jibix\Disguise\form;
use Frago9876543210\EasyForms\elements\Button;
use Frago9876543210\EasyForms\forms\MenuForm;
use pocketmine\player\Player;


/**
 * Class DisguiseManageForm
 * @package Jibix\Disguise\form
 * @author Jibix
 * @date 09.02.2022 - 17:52
 * @project Disguise
 */
class DisguiseManageForm{

    public function __construct(Player $player){
        #Todo: Add a manage form to manage the whole disguise names/players per form ui
        $player->sendForm(new MenuForm(
            "",
            "",
            [],
            function (Player $player, Button $button): void{

            }
        ));
    }
}