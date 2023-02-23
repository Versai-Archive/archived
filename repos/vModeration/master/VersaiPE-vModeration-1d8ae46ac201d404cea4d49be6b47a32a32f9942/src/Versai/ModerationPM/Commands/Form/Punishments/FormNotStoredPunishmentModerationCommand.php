<?php
declare(strict_types=1);

namespace Versai\ModerationPM\Commands\Form\Punishments;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use Versai\ModerationPM\Commands\Arguments\MessageArgument;
use Versai\ModerationPM\Commands\Form\FormModerationCommandOnline;
use Versai\ModerationPM\Commands\Form\Punishments\Traits\ReasonsTrait;
use Versai\ModerationPM\Main;

abstract class FormNotStoredPunishmentModerationCommand extends FormModerationCommandOnline implements PunishmentCommand{

    use ReasonsTrait;

    /**
     * KickCommand constructor.
     * @param Main $main
     * @param string $name
     * @param string $description
     * @param array $reasons
     * @param array $aliases
     */
    public function __construct(Main $main, string $name, string $description, array $reasons, array $aliases = []){
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . $this->provider->typeToString(static::TYPE, false));
        $this->setReasons($reasons);
    }

    protected function prepare(): void{
        parent::prepare();
        $this->registerArgument(1, new MessageArgument('reason', true));
    }

    /**
     * @param Player $sender
     * @param Player $player
     * @param array $args
     */
    public function runAsPlayer(Player $sender, Player $player, array $args): void{
        if (isset($args['reason']) && $args['reason'] !== '') {
            $this->runAsConsole($sender, $player, $args);
            return;
        }
        $form = new CustomForm(strtr(static::TITLE, ['{player}' => $player->getName()]), [
            new Dropdown('reason', 'Reason', $this->reasons),
            new Input('custom_reason', 'Custom Reason', 'Reason')
        ], function (Player $sender, CustomFormResponse $response) use ($player): void {
            $response = $response->getAll();
            $response['reason'] = $response['custom_reason'] === '' ? $this->reasons[$response['reason']] : $response['custom_reason'];
            $response['name'] = $player->getName();
            $this->callback($sender, $player, $response);
        });
        $sender->sendForm($form);
    }

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param array $args
     */
    public function runAsConsole(CommandSender $sender, Player $player, array $args): void{
        $args['name'] = $player->getName();
        if (isset($args['reason']) && $args['reason']) {
            $this->callback($sender, $player, $args);
        }else {
            $this->sendUsage();
        }
    }

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param array $result
     */
    abstract protected function callback(CommandSender $sender, Player $player, array $result): void;

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param string $reason
     * @throws \Exception
     */
    protected function logKick(CommandSender $sender, Player $player, string $reason): void{
        $logger = $this->plugin->getDiscordLogger();
        if($logger !== null) {
            $content = $this->plugin->getCommandConfig()->getNested('Discord.Content-Kick');
            foreach ($content as $key => $line) {
                $content[$key] = str_replace(['{player}', '{staff}', '{reason}'], [$logger->getXblLinkMarkdown($player->getName()), $logger->getXblLinkMarkdown($sender->getName()), $reason], $line);
            }
            $logger->logGeneric($this->plugin->getProvider()->typeToString(static::TYPE), $content, static::COLOR);
        }
    }

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param string $reason
     * @throws \Exception
     */
    protected function logReport(CommandSender $sender, Player $player, string $reason): void{
        $logger = $this->plugin->getDiscordLogger();
        if($logger !== null) {
            $content = $this->plugin->getCommandConfig()->getNested('Discord.Content-Report');
            foreach ($content as $key => $line) {
                $content[$key] = str_replace(['{reported}', '{player}', '{reason}'], [$logger->getXblLinkMarkdown($player->getName()), $logger->getXblLinkMarkdown($sender->getName()), $reason], $line);
            }
            $logger->logGeneric('Report', $content, static::COLOR, $this->plugin->getCommandConfig()->getNested('Discord.Webhook-Reports'));
        }
    }
}
