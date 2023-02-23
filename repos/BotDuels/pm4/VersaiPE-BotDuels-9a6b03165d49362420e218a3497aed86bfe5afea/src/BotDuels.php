<?php

declare(strict_types=1);

namespace ethaniccc\BotDuels;

use ethaniccc\BotDuels\bots\NoDebuffBot;
use ethaniccc\BotDuels\bots\SumoBot;
use ethaniccc\BotDuels\command\DuelBotCommand;
use ethaniccc\BotDuels\command\SpectateCommand;
use ethaniccc\BotDuels\game\GameManager;
use ethaniccc\BotDuels\map\MapData;
use ethaniccc\BotDuels\tasks\CleanGarbageTask;
use ethaniccc\BotDuels\tasks\TickingTask;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use function in_array;
use function spl_object_hash;

class BotDuels extends PluginBase implements Listener {

    use SingletonTrait;

	/** @var MapData[] */
	public array $nodebuffMaps = [];
    /** @var MapData[] */
    public array $sumoMaps = [];
	/** @var bool[] */
	public array $isMobile = [];
	/** @var int */
    public int $totalMaps = 0;

	public const TYPE_NODEBUFF = 0;
	public const TYPE_SUMO = 1;

	public function onEnable(): void {
		self::setInstance($this);
		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
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

				$eFact = EntityFactory::getInstance();
				$eFact->register(SumoBot::class, function(World $world, CompoundTag $nbt): Entity {
				    return new SumoBot(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), null);
                }, [SumoBot::class]);

				$eFact->register(NoDebuffBot::class, function(World $world, CompoundTag $nbt): Entity {
                    return new NoDebuffBot(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), null);
                }, [NoDebuffBot::class]);
			}
		}), 1);
	}

	public function getRandomMap(int $type): ?MapData {
	    $map = null;
	    switch($type){
            case self::TYPE_NODEBUFF:
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
			$event->cancel();
		}
	}

	public function onBreak(BlockBreakEvent $event): void {
		$player = $event->getPlayer();
		if (GameManager::getInstance()->isInGame($player->getName())) {
			$event->cancel();
		}
	}

	public function onDamage(EntityDamageEvent $event): void {
		if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
			$event->cancel();
		}
		if ($event->getEntity()->getWorld()->getFolderName() === "world") {
			$event->cancel();
		}
	}

    public function onLogin(PlayerLoginEvent $event){
	    if(($player = $event->getPlayer()) !== null){
            $data = $player->getPlayerInfo()->getExtraData();
            $this->isMobile[spl_object_hash($player)] = in_array($data["DeviceOS"], [DeviceOS::ANDROID, DeviceOS::IOS, DeviceOS::AMAZON]);
        }
    }

	public function onLevelChange(EntityTeleportEvent $event): void {
		$entity = $event->getEntity();
		$from = $event->getFrom();
		$to = $event->getTo();
		if ($entity instanceof Player) {
		    if($from->getWorld()->getFolderName() !== $to->getWorld()->getFolderName()) {
                $arr = explode("-", $from->getWorld()->getFolderName());
                if (strlen($arr[count($arr) - 1]) === 15 && $entity->isSpectator()) {
                    $entity->setGamemode(GameMode::SURVIVAL());
                }
            }
		}
	}

	public function executeCommand(CommandEvent $event): void {
		$sender = $event->getSender();
		if ($sender instanceof Player) {
			$command = strtolower(explode(" ", $event->getCommand())[0]);
			$arr = explode("-", $sender->getWorld()->getFolderName());
			if ($command === "kit" && strlen($arr[count($arr) - 1]) === 15) {
				$event->cancel();
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