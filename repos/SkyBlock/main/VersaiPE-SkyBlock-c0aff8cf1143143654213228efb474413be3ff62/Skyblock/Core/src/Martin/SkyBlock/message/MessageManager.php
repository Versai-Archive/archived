<?php


namespace Martin\SkyBlock\message;


use Martin\SkyBlock\Loader;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class MessageManager{
	public const PREFIX = TextFormat::DARK_BLUE . "[" . TextFormat::BLUE . "Sky" . TextFormat::AQUA . "Block" . TextFormat::DARK_BLUE . "]" . TextFormat::RESET . " ";

	private Loader $loader;
	private Config $config;

	public function __construct(Loader $loader){
		$this->loader = $loader;
		$this->fetchMessageData();
	}

	public function fetchMessageData() : void{
		$this->getLoader()->saveResource("messages.yml");
		$this->config = new Config($this->getLoader()->getDataFolder() . "messages.yml");
	}

	public function getLoader() : Loader{
		return $this->loader;
	}

	public function getMessage(string $messageKey, array $keys = [], bool $prefix = true) : string{
		if(($message = $this->config->getNested($messageKey, "null")) === null){
			return ($prefix ? self::PREFIX : "") . TextFormat::RED . "Internal error: Key with the value $messageKey not found!";
		}

		return ($prefix ? self::PREFIX : "") . self::replaceVariables($message, $keys);
	}

	public static function replaceVariables(string $string, array $var) : string{
		foreach($var as $key => $value){
			$string = str_replace("{" . $key . "}", $value, $string);
		}

		return $string;
	}
}