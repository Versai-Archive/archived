<?php
declare(strict_types=1);

namespace Versai\Duels;

use _86696beb29e03785e527poggit\libasynql\DataConnector;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifierFlattened;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use Versai\Duels\Blocks\FrozenLava;
use Versai\Duels\Blocks\FrozenWater;
use Versai\Duels\Commands\DuelCommand;
use Versai\Duels\Commands\EloCommand;
use Versai\Duels\Commands\PartyCommand;
use Versai\Duels\Commands\SpectateCommand;
use Versai\Duels\Events\Listener;
use Versai\Duels\Items\Entity\FishingHook;
use Versai\Duels\Items\GoldenHead;
use Versai\Duels\Items\VersaiRod;
use Versai\Duels\Level\DuelLevel;
use Versai\Duels\Level\LevelManager;
use muqsit\invmenu\InvMenuHandler;
use Versai\Duels\Match\DuelManager;
use Versai\Duels\Party\PartyManager;
use Versai\Duels\Queue\QueueManager;
use Versai\Duels\Tasks\GappleCooldownTask;
use Duo\kits\Kits;
use const DIRECTORY_SEPARATOR;

class Duels extends PluginBase{

    use SingletonTrait;

	public DuelManager $duelManager;
	public PartyManager $partyManager;
    public QueueManager $queueManager;
    public LevelManager $levelManager;

	public array $scoreboardArray = [];
	public array $countdownArray = [];
	public array $duelConfig = [];
	public array $levels = [];
	public array $kitSettings = [];

	public DuelCommand $duelCommand;
	public PartyCommand $partyCommand;
	private DataConnector $database;
    public GappleCooldownTask $gappleCooldownTask;


	public function onEnable(): void{
		self::setInstance($this);

		$this->saveDefaultConfig();
        $this->registerKitTypes();
		$this->duelConfig = $this->getConfig()->getAll();

		$this->duelManager = new DuelManager();
		$this->partyManager = new PartyManager();
		$this->levelManager = new LevelManager($this);
		$this->queueManager = new QueueManager($this);


		# Depends on above. Don't edit

		$this->registerArenas();

		$server = $this->getServer();

		$pluginManager = $server->getPluginManager();

        $this->gappleCooldownTask = new GappleCooldownTask($this);
        $this->getScheduler()->scheduleRepeatingTask($this->gappleCooldownTask, 20);

		$pluginManager->registerEvents(new Listener($this), $this);

		$directory = $this->getDataFolder();

		// Countdown Configuration

		// - in config means not doing anything

		$countdownConfig = new Config($directory . 'countdown.json', Config::JSON,
            [
                10 => '-:Match starting in {seconds}:-',
                9 => '-:Match starting in {seconds}:-',
                8 => '-:Match starting in {seconds}:-',
                7 => '-:Match starting in {seconds}:-',
                6 => '-:Match starting in {seconds}:-',
                5 => '-:Match starting in {seconds}:-',
                4 => '-:Match starting in {seconds}:-',
                3 => '-:-:{seconds}',
                2 => '-:-:{seconds}',
                1 => '-:-:{seconds}',
                0 => '[Duel] You\'re playing on map {map} by {author}:-:Duel!',
            ]);

		$this->countdownArray = $countdownConfig->getAll();

		// Scoreboard Configuration

		$scoreboardConfig = new Config($directory . 'scoreboard.json', Config::JSON,[
                '1v1' =>
                    [
                        0 => '§8─────────────',
                        1 => '§bFighting  §9{fighting}',
                        2 => '§bTime  §9{time}',
                        3 => '',
                        4 => '§bTheir Ping  §9{oping}',
                        5 => '§bYour Ping  §9{ping}',
                        6 => '',
                        7 => '§bversai.pro',
                        8 => '§8─────────────'
                    ],
                '1v1-Win' =>
                    [
                        0 => '§8─────────────',
                        1 => '§bCongrats, you won!',
                        2 => '',
                        3 => '§bAgainst  §9{opponent}',
                        4 => '',
                        5 => '§bversai.pro',
                        6 => '§8─────────────'
                    ],
                '1v1-Spectate' =>
                    [
                        0 => '§8─────────────',
                        1 => '§bTime  §9{time}',
                        2 => '§bSpectators  §9{spectators}',
                        3 => '',
                        4 => '§bversai.pro',
                        5 => '§8─────────────'
                    ],
                'Party' =>
                    [
                        0 => '§8─────────────',
                        1 => '§bTime  §9{time}',
                        2 => '§bPlayers  {remaining}|{total}',
                        3 => '§bYour Ping  §9{ping}',
                        4 => '',
                        5 => '§bversai.pro',
                        6 => '§8─────────────'
                    ],
                'Party-Win' =>
                    [
                        0 => '§8─────────────',
                        1 => '§bCongrats, you won!',
                        2 => '',
                        3 => '§bversai.pro',
                        4 => '§8─────────────'
                    ],
                'Party-Spectate' =>
                    [
                        0 => '§8─────────────',
                        1 => '§bPlayers  §9{remaining} | {total}',
                        2 => '§bTime  §9{time}',
                        3 => '§bSpectators  §9{spectators}',
                        4 => '',
                        5 => '§bversai.pro',
                        6 => '§8─────────────'
                    ]
            ]);

		$this->scoreboardArray = $scoreboardConfig->getAll();


        $kits = Kits::getInstance();

		// Register Duel Command

		$server->getCommandMap()->registerAll("duels",
			[
				$this->duelCommand = new DuelCommand('duel', $kits),
				new EloCommand('elo', $kits),
				new PartyCommand('party', $kits),
				new SpectateCommand('spectate')
			]
		);

        BlockFactory::getInstance()->register(new FrozenWater(new BlockIdentifierFlattened(BlockLegacyIds::FLOWING_WATER, [BlockLegacyIds::STILL_WATER], 0), "Water", BlockBreakInfo::indestructible(500.0)), true);
		BlockFactory::getInstance()->register(new FrozenLava(new BlockIdentifierFlattened(BlockLegacyIds::FLOWING_LAVA, [BlockLegacyIds::STILL_LAVA], 0), "Lava", BlockBreakInfo::indestructible(500.0)), true);

        ItemFactory::getInstance()->register(new GoldenHead(new ItemIdentifier(ItemIds::GOLDEN_APPLE, 10)), false);
        ItemFactory::getInstance()->register(new VersaiRod(new ItemIdentifier(ItemIds::FISHING_ROD, 0)), true);

        EntityFactory::getInstance()->register(FishingHook::class, function(World $world, CompoundTag $nbt) : FishingHook{
            return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['FishingHook', 'minecraft:fishing_hook'], EntityLegacyIds::FISHING_HOOK);

        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }

	}

	public function onDisable(): void{
        $this->levelManager->deleteAllTempLevels();
    }

    public function registerKitTypes(): void{
		foreach (Kits::getInstance()->getKitTypes() as $kitType) {

			$directory = $this->getDataFolder();
			$directory = $directory . 'kits' . DIRECTORY_SEPARATOR;

			if (!file_exists($directory)) {
                mkdir($directory);
            }

			$kitConfig = new Config($directory . $kitType . '.json', Config::JSON, [
                'Knockback' => 0.4,
                'Hit-Cooldown' => 10,
                'Item-Pickup' => false,
                'Hunger-Loss' => false,
                'Fall-Damage' => false,
                'Breakable' => false,
                'Placeable' => false,
                'Block-Decay' => false,
                'Build-Limit' => 256,
                'Allowed-Block-List' => []
            ]);

			$this->kitSettings[$kitType] = $kitConfig->getAll();
		}
	}

	public function registerArenas(): void{
		$i = 0;

		$server = $this->getServer();

		foreach ($this->duelConfig['Arenas'] as $arenaName => $arena) {
			$server->getWorldManager()->loadWorld($arena["Level"]);
			$level = $server->getWorldManager()->getWorldByName($arena["Level"]);
			if ($level === null) {
				$this->getLogger()->error("Invalid map in config for " . $arenaName);
				continue;
			}

			$this->levels[$arenaName] = new DuelLevel($arenaName, $arena["Positions"], $level, $arena["Author"], $arena["Kit-IDs"]);
			$i++;

			if($level !== $server->getWorldManager()->getDefaultWorld()) {
                $server->getWorldManager()->unloadWorld($level);
            }
		}

		if ($i === 0) {
			$this->getLogger()->emergency("No arenas setup!");
			$server->getPluginManager()->disablePlugin($this);
		} else {
            $this->getLogger()->info(TextFormat::GREEN . "Loaded $i arenas!");
        }

	}
	public function reloadConfig(): void{
	    parent::reloadConfig();
        $this->duelConfig = $this->getConfig()->getAll();
    }
}
