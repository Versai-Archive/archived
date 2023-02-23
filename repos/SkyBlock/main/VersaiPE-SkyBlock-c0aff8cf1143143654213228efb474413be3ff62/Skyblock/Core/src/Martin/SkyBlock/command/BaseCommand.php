<?php


namespace Martin\SkyBlock\command;


use Martin\SkyBlock\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

abstract class BaseCommand extends Command{
	/** @var SubCommand[] */
	protected array $subCommands = [];
	private Loader $loader;

	public function __construct(Loader $loader, string $name, string $description, array $aliases){
		$this->loader = $loader;
		parent::__construct($name, $description, "/$name", $aliases);
		$this->initSubCommands();
	}

	abstract public function initSubCommands() : void;

	public function registerSubCommand(SubCommand $subCommand) : bool{
		if(in_array($subCommand, $this->subCommands, true)){
			return false;
		}

		$this->subCommands[] = $subCommand;
		return true;
	}

	/**
	 * @throws ReflectionException
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$subCommandArgument = strtolower(array_shift($args));
		$subCommand = $this->getSubCommand($subCommandArgument);
		if($subCommand !== null){
			$canExecute = $subCommand->testPermission($sender);

			if($canExecute){
				if($sender instanceof Player){
					$subCommand->onCommand($sender, $args);
					return;
				}

				if($sender instanceof ConsoleCommandSender){
					$subCommand->onConsoleCommand($sender, $args);
					return;
				}


				$classedSender = Utils::getNiceClassName($sender);
				$sender->sendMessage(TextFormat::RED . "Edge case happend! You are $classedSender");
				return;
			}

			return;
		}

		$sender->sendMessage(TextFormat::RED . "Error! Subcommand was not found. Are you sure this exist?");
	}

	public function getSubCommand(string $needle) : ?SubCommand{
		$needle = strtolower($needle);
		foreach($this->subCommands as $subCommand){
			if(in_array($needle, array_merge($subCommand->getAliases(), [$subCommand->getName()]), true)){
				return $subCommand;
			}
		}

		return null;
	}

	/**
	 * @return Loader
	 */
	public function getLoader() : Loader{
		return $this->loader;
	}
}