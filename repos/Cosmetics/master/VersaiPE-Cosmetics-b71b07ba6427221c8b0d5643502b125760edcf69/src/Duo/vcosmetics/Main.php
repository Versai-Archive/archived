<?php
declare(strict_types=1);

namespace Duo\vcosmetics;

use _f27b97647b88b486614apoggit\libasynql\DataConnector;
use _f27b97647b88b486614apoggit\libasynql\libasynql;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use Duo\vcosmetics\commands\SettingsCommand;
use Duo\vcosmetics\database\MySQLProvider;
use Duo\vcosmetics\database\Provider;
use Duo\vcosmetics\events\Listener;
use Duo\vcosmetics\events\settings\listeners\CapeListener;
use Duo\vcosmetics\events\settings\listeners\FlightListener;
use Duo\vcosmetics\events\settings\listeners\ParticlesListener;
use Duo\vcosmetics\session\SessionManager;
use function array_diff;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function imagecolorat;
use function imagecreatefrompng;
use function imagesx;
use function imagesy;
use function json_decode;
use function mkdir;
use function preg_match;
use function scandir;

class Main extends PluginBase {

    use SingletonTrait;

    private Config $tagConfig;
    public array $capes, $tags, $clanTags;
    private SessionManager $sessionManager;
    private DataConnector $database;
    private Provider $provider;
    public PlayerData $playerData;

    public function onEnable(): void{
        self::setInstance($this);
        $this->initDatabase();
        $this->sessionManager = new SessionManager($this);
        $this->playerData = new PlayerData();

        $this->registerImages();

        $pluginMngr = $this->getServer()->getPluginManager();
        $pluginMngr->registerEvents(new Listener($this), $this);
        $pluginMngr->registerEvents(new FlightListener($this), $this);
        $pluginMngr->registerEvents(new ParticlesListener($this), $this);

        $this->getServer()->getCommandMap()->register("cosmetics", new SettingsCommand($this, "settings"));

        $this->registerTags();
        $this->registerClanTags();
    }

    public function onDisable(): void{
        if(isset($this->database)){
            $this->database->close();
        }
    }

    public function initDatabase(): void{
        $this->database = libasynql::create($this, (new Config($this->getDataFolder() . "database.yml"))->get("database"), [
            "mysql" => "mysql_stmts.sql"
        ]);
        $this->provider = new MySQLProvider($this, function(): void{
            $this->getLogger()->info("Database loaded!");
        });
    }

    public function getDatabase(): DataConnector{
        return $this->database;
    }

    public function getProvider(): Provider{
        return $this->provider;
    }

    public function getSessionManager(): SessionManager{
        return $this->sessionManager;
    }

    public function registerImages(): void{

        $this->capes["Error"] = "";
        $logger = $this->getLogger();

        if (extension_loaded("gd")) {
            $folder = $this->getDataFolder();

            if (!file_exists($folder . "capes")) {
                mkdir($folder . "capes");
            }

            $logger->notice("GD Extension found!");
            $logger->notice("Enabling capes!");
            unset($this->capes["Error"]);

            $this->capes["Disabled"] = "";

            foreach (array_diff(scandir($folder . "capes", SCANDIR_SORT_DESCENDING), [".."], ["."]) as $file) {
                if (preg_match("/(\.png)$/", $file)) {
                    $data = "";
                    $image = imagecreatefrompng($folder . "capes" . DIRECTORY_SEPARATOR . $file);
                    for ($y = 0, $height = imagesy($image); $y < $height; $y++) {
                        for ($x = 0, $width = imagesx($image); $x < $width; $x++) {
                            $color = imagecolorat($image, $x, $y);
                            $data .= pack("c", ($color >> 16) & 0xFF) //red
                                . pack("c", ($color >> 8) & 0xFF) //green
                                . pack("c", $color & 0xFF) //blue
                                . pack("c", 255 - (($color & 0x7F000000) >> 23)); //alpha
                        }
                    }
                    $name = substr($file, 0, strpos($file, "."));

                    if (strlen($data) === 8192) {
                        $this->getLogger()->notice("Loaded cape: " . $name);
                        $this->capes[$name] = $data;
                    } else {
                        $logger->error("Invalid cape!" . $name ?? "ERROR");
                        $logger->error("Capes should be 8KB in size");
                    }
                }
            }

            if (count($this->capes) === 0) {
                $logger->alert("There are no capes setup!");
            } else {
                $this->getServer()->getPluginManager()->registerEvents(new CapeListener($this), $this);
            }
        } else {
            $logger->error("GD extension not found, capes will not function.");
        }
    }

    public function registerTags(): void {
        $folder = $this->getDataFolder();

        if(!file_exists($this->getDataFolder() . 'tags.json')) {
            file_put_contents($this->getDataFolder() . 'tags.json', '{}');
            $this->registerClanTags();
        } else {
            $tags = json_decode(file_get_contents($this->getDataFolder() . 'tags.json'), true);
            $this->tags[0] = 'Disabled';

            foreach ($tags as $name => $format) {
                $this->tags[$name] = $format;
            }
        }
    }

    public function registerClanTags(): void {
        $folder = $this->getDataFolder();

        if(!file_exists($this->getDataFolder() . 'clans.json')) {
            file_put_contents($this->getDataFolder() . 'clans.json', '{}');
            $this->registerClanTags();
        } else {
            $clans = json_decode(file_get_contents($this->getDataFolder() . 'clans.json'), true);
            $this->clanTags[0] = 'Disabled';

            foreach ($clans as $clan => $format) {
                $this->clanTags[$clan] = $format;
            }
        }
    }
}