<?php


namespace Martin\SkyBlock;


use Martin\SkyBlock\cobblegenerator\CobbleGeneratorManager;
use Martin\SkyBlock\command\JobCommand;
use Martin\SkyBlock\command\SkyBlockCommand;
use Martin\SkyBlock\cooldown\CooldownManager;
use Martin\SkyBlock\database\DatabaseManager;
use Martin\SkyBlock\island\IslandManager;
use Martin\SkyBlock\listeners\DamageListener;
use Martin\SkyBlock\listeners\PlayerManagerListener;
use Martin\SkyBlock\message\MessageManager;
use Martin\SkyBlock\player\PlayerManager;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{
	private MessageManager $messageManager;
	private IslandManager $islandManager;
	private DatabaseManager $databaseManager;
	private CobbleGeneratorManager $cobbleGeneratorManager;
	private CooldownManager $cooldownManager;
	private PlayerManager $playerManager;

	public function onEnable() : void{
		$this->saveDefaultConfig();

		$this->initCommand();
		$this->initManagers();
		$this->initListeners();
	}

	public function initCommand() : void{
		$this->getServer()->getCommandMap()->register("skyblock", new SkyBlockCommand($this));
		$this->getServer()->getCommandMap()->register("job", new JobCommand($this));
	}

	public function initManagers() : void{
		$this->messageManager = new MessageManager($this);
		$this->databaseManager = new DatabaseManager($this);
		$this->islandManager = new IslandManager($this);
		$this->cobbleGeneratorManager = new CobbleGeneratorManager($this);
		$this->cooldownManager = new CooldownManager($this);
		$this->playerManager = new PlayerManager($this);
	}

	public function initListeners() : void{
		foreach([
			new DamageListener(),
			new PlayerManagerListener($this)
		] as $listener){
			if($listener instanceof Listener){
				$this->getServer()->getPluginManager()->registerEvents($listener, $this);
			}
		}
	}

	public function onDisable() : void{
		if($this->databaseManager->getConnection() !== null){
			$this->databaseManager->getConnection()->close();
		}
	}

	public function getMessageManager() : MessageManager{
		return $this->messageManager;
	}

	public function getIslandManager() : IslandManager{
		return $this->islandManager;
	}

	public function getDatabaseManager() : DatabaseManager{
		return $this->databaseManager;
	}

	public function getCobbleGeneratorManager() : CobbleGeneratorManager{
		return $this->cobbleGeneratorManager;
	}

	public function getCooldownManager() : CooldownManager{
		return $this->cooldownManager;
	}

	public function getPlayerManager() : PlayerManager{
		return $this->playerManager;
	}
}