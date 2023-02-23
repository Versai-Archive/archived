<?php
declare(strict_types=1);

namespace Versai\vwarps\Commands;

use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Versai\vwarps\Commands\Arguments\IconTypeArgument;
use Versai\vwarps\Main;
use Versai\vwarps\Warp;
use function explode;
use function implode;
use function str_replace;

class CreateWarpCommand extends PluginBaseCommand {

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void {
        $this->setPermission(Main::PERMISSION_ROOT . 'create');
        $this->registerArgument(0, new RawStringArgument('name'));
        $this->registerArgument(1, new IconTypeArgument('type' . IconTypeArgument::DELIMITER . 'data', true));
        $this->registerArgument(2, new BooleanArgument('pocket', true));
        $this->registerArgument(3, new TextArgument('format', true));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @throws \JsonException
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if(!$this->testPermission($sender)) {
            return;
        }
        if($sender instanceof Player) {
            if(isset($args['name'])) {
                $defaults = $this->getPlugin()->getConfig()->getAll()['Defaults'];
                $image = $args['type' . IconTypeArgument::DELIMITER . 'data'] ?? null;
                if($image !== null) {
                    list($imageAr['type'], $imageAr['data']) = explode(IconTypeArgument::DELIMITER, $image);
                }
                $pocket = $args['pocket'] ?? $defaults['Pocket'];
                $format = $args['Format'] ?? '';
                if($format !== '') {
                    $format = str_replace('|', TextFormat::EOL, $format);
                } else {
                    $format = implode(TextFormat::EOL, $defaults['Format']) ?? 'Error, missing default and assignment for format!';
                }
                $warp = new Warp($sender->getPosition(), $sender->getLocation()->getYaw(), $sender->getLocation()->getPitch(), $args['name'], $format, $pocket, $imageAr ?? $defaults['Image']);
                $plugin = $this->getPlugin();
                $plugin->getWarpsContainer()->addWarp($warp);
                $config = $this->getPlugin()->getConfig();
                $configArray = $config->getAll();
                $name = $warp->getName();
                $configArray['Warps'][$name] = $warp->toConfigEntry();
                $config->setAll($configArray);
                $config->save();
                $sender->sendMessage(TextFormat::GREEN . "Successfully created warp $name");
            } else {
                $this->sendUsage();
            }
        }
    }
}
