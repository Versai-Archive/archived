<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Miscellaneous;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\ModerationPM\Commands\Arguments\MessageArgument;
use Versai\ModerationPM\Commands\ModerationCommand;
use Versai\ModerationPM\Main;

class StaffChatCommand extends ModerationCommand{

    protected function prepare(): void{
        $this->registerArgument(0, new MessageArgument('message', true));
        $this->setPermission(Main::PERMISSION_PREFIX . 'staff_chat');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if ($sender instanceof Player){
            if ($this->testPermission($sender)){

                $staffChat = $this->plugin->getStaffChat();

                if (!$staffChat->isInStaffChat($sender)){
                    $staffChat->addToStaffChat($sender);
                }

                if (isset($args['message'])){
                    $staffChat->sendMessage($sender, $args['message']);
                    return;
                }

                $staffChatToggled = $this->plugin->getStaffChatToggled();

                if ($staffChatToggled->checkState($sender)){
                    $staffChatToggled->reverseAction($sender);
                    $sender->sendMessage(TextFormat::GREEN . 'Staff chat disabled!');
                    return;
                }
                $staffChatToggled->action($sender);
                $sender->sendMessage(TextFormat::GREEN . 'Staff chat enabled!');
            }
        }elseif (isset($args['message'])){
            $staffChat = $this->plugin->getStaffChat();
            if (!$staffChat->isInStaffChat($sender))
                $staffChat->addToStaffChat($sender);
            $staffChat->sendMessage($sender, $args['message']);
        }else{
            $this->sendUsage();
        }
    }
}
