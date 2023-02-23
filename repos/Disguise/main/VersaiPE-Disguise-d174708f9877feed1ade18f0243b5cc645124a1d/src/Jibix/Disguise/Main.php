<?php
namespace Jibix\Disguise;
use Jibix\Disguise\command\DisguiseCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;


/**
 * Class Main
 * @package Jibix\Disguise
 * @author Jibix
 * @date 08.02.2022 - 23:27
 * @project Disguise
 */
class Main extends PluginBase{

    const API_URL = "https://cdn2.nicemarkmc.com/skins/64x64/"; #/number.png

    /** @var Main */
    private static Main $instance;
    /** @var Config */
    private Config $nameFile;

    /**
     * Function getInstance
     * @return Main
     */
    public static function getInstance(): Main{
        return self::$instance;
    }

    /**
     * Function onLoad
     */
    public function onLoad(): void{
        self::$instance = $this;
        $this->saveResource('config.yml');
        $this->saveResource('geometry.json');
        $this->saveResource('nameList.json');
    }

    /**
     * Function onEnable
     */
    public function onEnable(): void{
        $this->nameFile = new Config(Main::getInstance()->getDataFolder() . "nameList.json", Config::JSON);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register($this->getName(), new DisguiseCommand());
    }

    /**
     * Function getNameFile
     * @return Config
     */
    public function getNameFile(): Config{
        return $this->nameFile;
    }
}