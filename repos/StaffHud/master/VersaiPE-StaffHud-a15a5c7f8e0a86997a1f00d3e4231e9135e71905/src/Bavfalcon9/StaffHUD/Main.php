<?php

namespace Bavfalcon9\StaffHUD;

/* Commands */
use pocketmine\plugin\PluginBase;
use pocketmine\command\{
    Command,
    CommandSender
};
use pocketmine\permission\Permission;
use pocketmine\utils\Config;

/* Misc */

use pocketmine\{permission\PermissionManager, permission\PermissionParser, Player, Server};

/* Commands */
use Bavfalcon9\StaffHUD\Command\{
    staff
};

/* Events */
use Bavfalcon9\StaffHUD\EventManager;
use Bavfalcon9\StaffHUD\Classes\StaffHUD;


class Main extends PluginBase {
    public $EventManager;
    public $staffHUD;
    private $scoreboards;
    private $config;
    private $staffMode = [];
    private $scoreboardManager;

    public function onEnable():void {
        $this->EventManager = new EventManager($this);
        $this->staffHUD = new StaffHUD($this);
        $this->getServer()->getPluginManager()->registerEvents($this->EventManager, $this);
        $this->loadCommands();
        //$this->scoreboards = $this->getServer()->getPluginManager()->getPlugin('ScoreboardAPI');
        //if ($this->scoreboards === null) {

        //}
        //$this->saveResource($this->getDataFolder . 'config.yml');
    }

    private function loadCommands() {
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->registerAll('StaffHUD', [
            new staff($this)
        ]);

        $this->addPerms([
            new Permission('StaffHUD.staffmode', 'Use /staff', [

        PermissionParser::DEFAULT_OP

            ]),
            new Permission('StaffHUD.staffmode.see', 'See others in staff mode.', [

                PermissionParser::DEFAULT_OP

            ]),
            new Permission('StaffHUD.staffmode.see_op', 'See others in staff mode.', [

                PermissionParser::DEFAULT_OP

            ])
        ]);
    }

    /**
     * @param Permission[] $permissions
     */

    protected function addPerms(array $permissions) {
        foreach ($permissions as $permission) {

            PermissionManager::getInstance()->addPermission($permission);

        }
    }

}