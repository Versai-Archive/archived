<?php

namespace lang;

use lang\idiome\Idiome;
use lang\session\SessionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class LanguageAPI extends PluginBase
{

    private static $instance = null;

    /**
     * @var SessionManager
     */

    private $session;

    private $DEFAULT_LANG = "eng";

    private $langs = [];

    private Config $defualt;

    public function onEnable(): void
    {

        self::$instance = $this;

        @mkdir($this->getDataFolder()."/idiomes");

       $this->defualt = new Config($this->getDataFolder()."/idiomes/eng.json", Config::JSON, [
            "welcome.message" => "Welcome to the server {player}",
            "welcome.popup_message" => "Hi there {player}"
        ]);

       $this->loadIdiomes();

       $this->session = new SessionManager();

        $this->getServer()->getPluginManager()->registerEvents(new event\EventHandler($this), $this);

    }

    public function loadIdiomes()
    {

        try
        {

        foreach (scandir($this->getDataFolder()."/idiomes") as $item => $value) {

            $ignore = ['.',  '..'];

            if (!in_array($value, $ignore)) {

                $this->getLogger()->info("This idiome has loaded " . $value);

                $key = explode(".", $value);

                $this->langs[$key[0]] = new Idiome($key[0]);


            }
        }

        }catch (\Exception $exception) {



        }
        
    }


    /**
     * @return Idiome
     * @throws \IdiomeException
     */
    public function getIdiome($lang): Idiome
    {

        if (!isset($this->langs[$lang])) {

            $lang = $this->DEFAULT_LANG;
            throw new \IdiomeException("Idome not founded");
        }

    }

    /**
     * @return null
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }


}