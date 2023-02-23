<?php

declare(strict_types=1);

namespace ethaniccc\BotDuels;

use ethaniccc\BotDuels\command\DuelBotCommand;
use ethaniccc\BotDuels\command\SpectateCommand;
use ethaniccc\BotDuels\game\GameManager;
use ethaniccc\BotDuels\map\MapData;
use ethaniccc\BotDuels\tasks\CleanGarbageTask;
use ethaniccc\BotDuels\tasks\TickingTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;

class BotDuels extends PluginBase implements Listener {

	private static $instance;
	/** @var MapData[] */
	public $nodebuffMaps = [];
    /** @var MapData[] */
    public $sumoMaps = [];
	/** @var bool[] */
	public $isMobile = [];
	/** @var int */
    public $totalMaps = 0;

	public const TYPE_NODEBUFF = 0;
	public const TYPE_SUMO = 1;
	public const TYPE_COMBO = 2;

	public static function getInstance(): self {
		return self::$instance;
	}

	public function onEnable() {
		if (self::$instance !== null) {
			$this->getLogger()->error("Plugin was reloading - disabling plugin.");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		self::$instance = $this;
		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick): void {
		    $this->loadMaps();
			$loadData = $this->getConfig()->getNested("main.load");
			if($loadData["nodebuff"] === true && count($this->nodebuffMaps) === 0){
                $this->getLogger()->critical("No maps were loaded! Cannot run bot duels.");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }elseif($loadData["sumo"] === true && count($this->sumoMaps) === 0){
                $this->getLogger()->critical("No maps were loaded! Cannot run bot duels.");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }elseif($this->totalMaps === 0){
                $this->getLogger()->critical("No maps were loaded! Cannot run bot duels.");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            } else {
				$this->getLogger()->info(TextFormat::GREEN . $this->totalMaps . " maps were loaded!");
				GameManager::init();
				$this->getScheduler()->scheduleRepeatingTask(new TickingTask(), 1);
				$this->getServer()->getCommandMap()->register($this->getName(), new DuelBotCommand());
				$this->getServer()->getCommandMap()->register($this->getName(), new SpectateCommand());
				$this->getServer()->getAsyncPool()->submitTask(new CleanGarbageTask());
				$this->getServer()->getPluginManager()->registerEvents($this, $this);
			}
		}), 1);
	}

	public function getRandomMap(int $type): ?MapData {
	    $map = null;
	    switch($type){
            case self::TYPE_NODEBUFF:
            case self::TYPE_COMBO:
                $map = $this->nodebuffMaps[array_rand($this->nodebuffMaps)];
                break;
            case self::TYPE_SUMO:
                $map = $this->sumoMaps[array_rand($this->sumoMaps)];
                break;
        }
        return $map;
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority LOWEST
	 */
	public function onDeath(PlayerDeathEvent $event): void {
		if ($event->getDeathMessage()->getText() === "death.attack.mob") {
			$event->setDeathMessage("");
		}
	}

	public function onExhaust(PlayerExhaustEvent $event): void {
		if (GameManager::getInstance()->isInGame($event->getPlayer()->getName())) {
			$event->setCancelled();
		}
	}

	public function onBreak(BlockBreakEvent $event): void {
		$player = $event->getPlayer();
		if (GameManager::getInstance()->isInGame($player->getName())) {
			$event->setCancelled();
		}
	}

	public function onDamage(EntityDamageEvent $event): void {
		if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
			$event->setCancelled();
		}
		if ($event->getEntity()->getLevel()->getFolderName() === "world") {
			$event->setCancelled();
		}
        $player = $event->getEntity();
		if($player instanceof Player){
            if($event instanceof EntityDamageByEntityEvent) {
                $gameMngr = GameManager::getInstance();
                if ($gameMngr->isInGame($player->getName())) {
                    if ($gameMngr->getGame($player->getName())->type === self::TYPE_COMBO) {
                        $event->setAttackCooldown(2);  //Combo hit cooldown
                        $event->setKnockBack(0.33);
                    }
                }
            }
        }
	}

	public function onPacket(DataPacketReceiveEvent $event): void {
		$packet = $event->getPacket();
		if ($packet instanceof LoginPacket) {
			$this->isMobile[spl_object_hash($event->getPlayer())] = in_array($packet->clientData["DeviceOS"], [DeviceOS::ANDROID, DeviceOS::ANDROID, DeviceOS::AMAZON]);
		}
	}

	public function onLevelChange(EntityLevelChangeEvent $event): void {
		$entity = $event->getEntity();
		if ($entity instanceof Player) {
			$arr = explode("-", $event->getOrigin()->getFolderName());
			if (strlen($arr[count($arr) - 1]) === 15 && $entity->isSpectator()) {
				$entity->setGamemode(GameMode::SURVIVAL);
			}
		}
	}

	public function executeCommand(CommandEvent $event): void {
		$sender = $event->getSender();
		if ($sender instanceof Player) {
			$command = strtolower(explode(" ", $event->getCommand())[0]);
			$arr = explode("-", $sender->getLevel()->getFolderName());
			if ($command === "kit" && strlen($arr[count($arr) - 1]) === 15) {
				$event->setCancelled();
			}
		}
	}


	public function loadMaps(){
	    $data = $this->getConfig()->getNested("main.load");
	    if($data["nodebuff"] === true){
            Utils::iterate($this->getConfig()->getNested("maps.nodebuff"), function ($key, array $data): void{
                Utils::vadilate($data, function (array $d): bool{
                    return isset($d["folder_name"], $d["authors"], $d["player_position"], $d["bot_position"]) && count($d["player_position"]) === 3 && count($d["bot_position"]) === 3;
                }, new \Exception("Map data for $key is invalid. Please check your config"));
                if (file_exists("./worlds/" . $data["folder_name"])){
                    $this->nodebuffMaps[$key] = new MapData($key, $data["authors"], "./worlds/" . $data["folder_name"], new Vector3($data["player_position"][0], $data["player_position"][1], $data["player_position"][2]), new Vector3($data["bot_position"][0], $data["bot_position"][1], $data["bot_position"][2]));
                    ++$this->totalMaps;
                }else{
                    $this->getLogger()->error("World " . $data["folder_name"] . " does not exist on the server. Please check your worlds folder.");
                }
            });
        }
	    if($data["sumo"] === true){
            Utils::iterate($this->getConfig()->getNested("maps.sumo"), function ($key, array $data): void {
                Utils::vadilate($data, function (array $d): bool{
                    return isset($d["folder_name"], $d["authors"], $d["player_position"], $d["bot_position"]) && count($d["player_position"]) === 3 && count($d["bot_position"]) === 3;
                }, new \Exception("Map data for $key is invalid. Please check your config"));
                if (file_exists("./worlds/" . $data["folder_name"])){
                    $this->sumoMaps[$key] = new MapData($key, $data["authors"], "./worlds/" . $data["folder_name"], new Vector3($data["player_position"][0], $data["player_position"][1], $data["player_position"][2]), new Vector3($data["bot_position"][0], $data["bot_position"][1], $data["bot_position"][2]));
                    ++$this->totalMaps;
                }else{
                    $this->getLogger()->error("World " . $data["folder_name"] . " does not exist on the server. Please check your worlds folder.");
                }
            });
        }
    }

}