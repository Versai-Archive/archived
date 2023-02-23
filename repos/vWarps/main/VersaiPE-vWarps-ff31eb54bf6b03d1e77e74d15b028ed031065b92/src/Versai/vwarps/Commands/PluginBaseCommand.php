<?php
declare(strict_types=1);

namespace Versai\vwarps\Commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\plugin\Plugin;
use Versai\vwarps\Main;

abstract class PluginBaseCommand extends BaseCommand {

    private Main $plugin;

    /**
     * WarpCommand constructor.
     * @param Main $plugin
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(Main $plugin, string $name, string $description = "", array $aliases = []) {
        parent::__construct($plugin, $name, $description, $aliases);
        $this->plugin = $plugin;
    }

    /**
     * @return Main
     */
    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
