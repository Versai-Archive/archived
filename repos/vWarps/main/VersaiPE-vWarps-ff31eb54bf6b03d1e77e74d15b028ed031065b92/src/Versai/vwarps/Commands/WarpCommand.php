<?php
declare(strict_types=1);

namespace Versai\vwarps\Commands;

use CortexPE\Commando\exception\ArgumentOrderException;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\vwarps\Commands\Arguments\PlayerArgument;
use Versai\vwarps\Commands\Arguments\WarpEnumArgument;
use Versai\vwarps\Main;
use Versai\vwarps\Warp;
use function array_values;
use function count;
use function str_replace;
use function strtr;

class WarpCommand extends PluginBaseCommand {

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void {
        $this->registerArgument(0, new WarpEnumArgument('warp', true));
        $this->registerArgument(1, new PlayerArgument('player', true));
    }
    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $plugin = $this->getPlugin();
        $config = $plugin->getConfig();
        $message = $config->get('Message');
        if (isset($args['warp'])) {
            $warp = $this->getPlugin()->getWarpsContainer()->getWarp($args['warp']);
            if ($warp !== null) {
                if (isset($args['player'])) {
                    if ($sender->hasPermission(Main::PERMISSION_ROOT . 'other')) {
                        $player = $sender->getServer()->getPlayerExact($args['player']);
                        if ($player === null) {
                            $sender->sendMessage("That player is offline or doesn't exist!");
                            return;
                        }
                        $this->sendPlayerToWarp($player, $warp, $message);
                        return;
                    }
                }

                if ($sender instanceof Player) {
                    $this->sendPlayerToWarp($sender, $warp, $message);
                    return;
                }

                $this->sendUsage();

            } else {
                $sender->sendMessage(TextFormat::RED . "That warp doesn't exist!");
            }
        } elseif ($sender instanceof Player) {
            $plugin = $this->getPlugin();
            /** @var Warp[] $warps */
            $warps = (array)$plugin->getWarpsContainer()->getWarps();
            $deviceOS = $plugin->getDeviceOS()->isPE($sender);
            foreach ($warps as $key => $warp) {
                if (!$this->canUseWarp($sender, $warp)) {
                    unset($warps[$key]);
                    continue;
                }
                if ($warp->isPocket() && !$deviceOS) {
                    unset($warps[$key]);
                    continue;
                }
                $level = $warp->getPosition()->getWorld();
                if ($level !== null) {
                    $buttons[] = new MenuOption(strtr($warp->getFormat(), [
                        '{warp}' => $warp->getName(),
                        '{players}' => count($level->getPlayers()),
                        '{level}' => $level->getDisplayName(),
                    ]), $warp->getImage());
                }
            }
            if (isset($buttons)) {
                $formConfig = $config->getAll()['Form'];
                $sender->sendForm(new MenuForm($formConfig['Title'], $formConfig['Text'], $buttons, function (Player $player, int $selectedOption) use ($warps, $message): void {
                    $warps = array_values($warps);
                    /** @var Warp $warp */
                    $warp = $warps[$selectedOption];
                    $this->sendPlayerToWarp($player, $warp, $message);
                }));
            } elseif (count($warps) === 0) {
                $sender->sendMessage(TextFormat::RED . "There are no warps set up or you don't have permission to use any!");
            } else {
                $sender->sendMessage(TextFormat::RED . 'You do not have access to any of the warps!');
            }
        }else{
            $this->sendUsage();
        }
    }

    /**
     * @param Player $player
     * @param Warp $warp
     * @param $message
     */
    private function sendPlayerToWarp(Player $player, Warp $warp, $message): void {
        if($warp->sendPlayer($player)) {
            $player->sendMessage(str_replace('{warp}', $warp->getName(), $message));
        } else {
            $player->sendMessage(TextFormat::RED . 'This warp is pocket edition only!');
        }
    }

    /**
     * @param Player $player
     * @param Warp $warp
     * @return bool
     */
    private function canUseWarp(Player $player, Warp $warp): bool {
        return ($player->hasPermission(Main::PERMISSION_USE_ALL)
        || $player->hasPermission(Main::PERMISSION_USE . strtr(Main::PERMISSION_USE, ['{warp}' => $warp->getName()])));
    }
}
