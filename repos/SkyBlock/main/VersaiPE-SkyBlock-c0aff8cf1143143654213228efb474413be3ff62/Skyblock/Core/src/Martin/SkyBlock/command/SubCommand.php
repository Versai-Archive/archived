<?php


namespace Martin\SkyBlock\command;


use Martin\SkyBlock\Loader;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class SubCommand{
	private BaseCommand $command;
	private string $name;
	private string $description;
	/** @var string[] */
	private array $aliases;

	private ?string $permission = null;

	/**
	 * SubCommand constructor.
	 *
	 * @param SkyBlockCommand $command
	 * @param string          $name
	 * @param string          $description
	 * @param string[]        $aliases
	 */
	public function __construct(BaseCommand $command, string $name, string $description, array $aliases = []){
		$this->command = $command;
		$this->name = $name;
		$this->description = $description;
		$this->aliases = $aliases;
	}

	abstract public function onConsoleCommand(ConsoleCommandSender $sender, array $args) : void;

	abstract public function onCommand(Player $sender, array $args) : void;

	final public function setPermission(?string $permission) : void{
		$this->permission = $permission;
	}

	final public function testPermission(CommandSender $sender) : bool{
		if(!$this->testPermissionSilent($sender)){
			$sender->sendMessage(TextFormat::RED . "Yikes! You are not allowed to execute this command.");
			return false;
		}

		return true;
	}

	final public function testPermissionSilent(CommandSender $sender) : bool{
		if($this->permission === null){
			return true;
		}

		if($sender->isOp()){
			return true;
		}

		foreach(explode(";", $this->permission) as $permission){
			if($sender->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}

	public function getMessage(string $key, array $args = []) : string{
		return $this->getLoader()->getMessageManager()->getMessage($key, $args);
	}

	public function getLoader() : Loader{
		return $this->getCommand()->getLoader();
	}

	public function getCommand() : SkyBlockCommand{
		return $this->command;
	}

	final public function getName() : string{
		return $this->name;
	}

	final public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @return string[]
	 */
	final public function getAliases() : array{
		return $this->aliases;
	}

	public function getServer() : Server{
		return $this->getLoader()->getServer();
	}
}