<?php


namespace Duels;


use Duels\game\GameMatch;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use session\SessionManager;

class Loader extends PluginBase
{
    /**
     * @var Loader 
     */

    private static $instance = null;

    private array $arenas = [];

    private $sessionManager;

    public Config $modes;


    protected function onEnable(): void
    {
        
        self::$instance = $this;

        @mkdir($this->getDataFolder().DIRECTORY_SEPARATOR."arenas");

        $this->modes = new Config($this->getDataFolder()."modes.json", Config::JSON, [

            "Combo" => [

                "kb" => 0.5,
                'items' => [
                    '306:0:1:4:5',
                    '307:0:1',
                    '308:0:1',
                    '309:0:1',
                    '276:0:1'
                ]

            ]

        ]);

        $this->registerCommands();

        $this->loadArenas();

        $this->sessionManager = new SessionManager();



    }
    public function loadArenas()
    {

        $files = scandir($this->getDataFolder().DIRECTORY_SEPARATOR."arenas");

        foreach ($files as $arenasFile) {

          $ignore = ["..", "."];

          if (!in_array($arenasFile, $ignore)) {

              $arena = new Config($this->getDataFolder().DIRECTORY_SEPARATOR."arenas/".$arenasFile, Config::YAML);

              if ($arena->exists("name")) {

                  $name = $arena->get("name");

              $this->arenas[$name] = new GameMatch($name);

              $this->getLogger()->info("§a{$name} arena is done");

              }




          }

        }

    }

    private function registerCommands() {

        $this->getServer()->getCommandMap()->registerAll("duel", [

        new commands\ArenaCommand($this)

        ]);

    }

    public static function getInstance()
    {

        return self::$instance;
        
    }

    /**
 * @return SessionManager
 */
    public function getSessionManager()
    {
        return $this->sessionManager;
    }


}