<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/38/2018
 * Time: 10:25 AM
 */
declare(strict_types=1);

namespace ARTulloss\Duels;

use ARTulloss\Duels\Commands\EloRestoreCommand;
use ARTulloss\Duels\Queries\Queries;
use ARTulloss\Duels\Utilities\Utilities;
use ARTulloss\Kits\Kit;
use pocketmine\block\BlockBreakInfo;
use pocketmine\entity\EntityFactory;
use pocketmine\world\Position;
use function ini_set;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;

use pocketmine\block\BlockIdentifierFlattened as BIDFlattened;
use pocketmine\block\BlockLegacyIds as Ids;

use ARTulloss\Cooldown\Cooldown;
use ARTulloss\Duels\Commands\DuelCommand;
use ARTulloss\Duels\Commands\EloCommand;
use ARTulloss\Duels\Elo\Elo;
use ARTulloss\Duels\Events\Listener;
use ARTulloss\Duels\Items\Entity\FishingHook;
use ARTulloss\Duels\Items\FishingRod;
use ARTulloss\Duels\Blocks\FrozenLava;
use ARTulloss\Duels\Blocks\FrozenWater;
use ARTulloss\Duels\Level\DuelLevel;
use ARTulloss\Duels\Level\LevelManager;
use ARTulloss\Duels\Match\DuelManager;
use ARTulloss\Duels\Queue\QueueManager;
use ARTulloss\Duels\Commands\PartyCommand;
use ARTulloss\Duels\Commands\SpectateCommand;
use ARTulloss\Duels\Party\PartyManager;
use ARTulloss\Kits\Kits;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use ARTulloss\Duels\libs\poggit\libasynql\DataConnector;
use ARTulloss\Duels\libs\poggit\libasynql\libasynql;
use const DIRECTORY_SEPARATOR;
use function var_dump;


/**
 * Class Duels
 * @package ARTulloss\Duels
 */
class Duels extends PluginBase{
	/** @var DuelManager $duelManager */
	public $duelManager;
	/** @var PartyManager $partyManager */
	public $partyManager;
	/** @var array $scoreboardArray */
	public $scoreboardArray;
	/** @var array $bossbarArray */
	public $bossbarArray;
	/** @var array $countdownArray */
	public $countdownArray;
	/** @var array $duelConfig */
	public $duelConfig;
	/** @var LevelManager $levelManager */
	public $levelManager;
	/** @var DuelLevel[] $levels */
	public $levels;
	/** @var QueueManager $queueManager */
	public $queueManager;
	/** @var array $kitSettings */
	public $kitSettings;
	/** @var Elo $eloManager */
	public $eloManager;
	/** @var Cooldown $cooldown */
	public $cooldown;
	/** @var DuelCommand $duelCommand */
	public $duelCommand;
	/** @var PartyCommand $partyCommand */
	public $partyCommand;
	/** @var DataConnector $database */
	private $database;

	/** @var Duels $instance */
	private static $instance;

	/**
	 * @return Duels
	 */
	public static function getInstance(): Duels{
		return self::$instance;
	}

	public function onEnable(): void{
		self::$instance = $this;

		$this->saveDefaultConfig();
		$this->duelConfig = $this->getConfig()->getAll();

		$this->duelManager = new DuelManager();
		$this->partyManager = new PartyManager();
		$this->levelManager = new LevelManager($this);
		$this->queueManager = new QueueManager($this);

		$this->registerKitTypes();

		# Depends on above. Don't edit

		$this->eloManager = new Elo($this);

		$this->registerArenas();

		$server = $this->getServer();

		$pluginManager = $server->getPluginManager();

		$this->cooldown = $pluginManager->getPlugin("Cooldown");

		$pluginManager->registerEvents(new Listener($this), $this);

		$directory = $this->getDataFolder();

		// Countdown Configuration

		//$folder = $this->getDataFolder();

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
                        0 => '§b{ranked} §9{kit}',
                        1 => '§bTime: §9{time}',
                        2 => '§bTheir Ping: §9{oping}',
                        3 => '§bYour Ping: §9{ping}',
                        4 => '§bCombat: §9{combat}',
                        5 => '§b    versai.pro    ',
                    ],
                '1v1-Win' =>
                    [
                        0 => '§bCongrats, you won!',
                        1 => '§b{ranked} §9{kit}',
                        2 => '§bAgainst: §9{opponent}',
                        3 => '§b    versai.pro    ',
                    ],
                'Party' =>
                    [
                        0 => '§bParty §9{kit}',
                        1 => '§bTime: §9{time}',
                        2 => '§bPlayers: {remaining}|{total}',
                        3 => '§bYour Ping: §9{ping}',
                        4 => '§bCombat: §9{combat}',
                        5 => '§b    versai.pro    ',
                    ],
                'Party-Win' =>
                    [
                        0 => '§bCongrats, you won!',
                        1 => '§bParty §9{kit}',
                        2 => '§b    versai.pro    ',
                    ],
                'Party-Spectate' =>
                    [
                        0 => '\u00a7bSpectating',
                        1 => '\u00a7bParty \u00a79{kit}',
                        2 => '\u00a7bPlayers: \u00a79{remaining} | {total}',
                        3 => '\u00a7bTime: \u00a79{time}',
                        4 => '\u00a7bSpectators: \u00a79{spectators}',
                        5 => '\u00a7b    versai.pro    ',
                    ]
            ]);

		$this->scoreboardArray = $scoreboardConfig->getAll();

		# Bossbar Configuration


		$bossbarConfig = new Config($directory . 'bossbar.json', Config::JSON
			,[
                '1v1' => '§bTime remaining: §9{time}',
                '1v1-Win' => '§9You won!',
                '1v1-Spectate' => '§bTime remaining: §9{time}',
                'Party' => '§bTime remaining: §9{time}',
                'Party-Win' => '§9You won!',
                'Party-Spectate' => '§bTime remaining: §9{time}',
            ]);

		$this->bossbarArray = $bossbarConfig->getAll();

		// Register Duel Command

		/** @var Kits $kits */
		$kits = $this->getServer()->getPluginManager()->getPlugin('Kits');

		$server->getCommandMap()->registerAll("duels",
			[
				$this->duelCommand = new DuelCommand('duel', $this, $kits),
				new EloCommand('elo', $this, $kits),
				new PartyCommand('party', $this, $kits),
				new SpectateCommand('spectate', $this),
                new EloRestoreCommand('elorestore', $this)
			]
		);

		// Freeze water and lava

        BlockFactory::getInstance()->register((new FrozenLava(new BIDFlattened(Ids::FLOWING_LAVA, [Ids::STILL_LAVA], 0), "Lava", BlockBreakInfo::indestructible(500.0))), true);
        BlockFactory::getInstance()->register((new FrozenWater(new BIDFlattened(Ids::FLOWING_WATER, [Ids::STILL_WATER], 0), "Water", BlockBreakInfo::indestructible(500.0))), true);



        ItemFactory::getInstance()->register(new FishingRod(), true);

		EntityFactory::getInstance()->register(FishingHook::class);

		// Register InvMenu

   /*     if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        } */

        // Register database

        $this->saveResource('database.yml');
        $this->database = libasynql::create($this, (new Config($directory . 'database.yml'))->get("database"), [
            "mysql" => "mysql.sql",
            "sqlite" => "sqlite.sql"
        ]);
        $this->database->executeGeneric(Queries::INIT_PLAYERS, [], null, Utilities::getOnError($this));
        $this->database->executeGeneric(Queries::INIT_PLAYER_ELO, [], null, Utilities::getOnError($this));
        $this->database->waitAll();

	}
	public function onDisable(): void{
        if(isset($this->database))
            $this->database->close();
    }
    public function registerKitTypes(): void{
		foreach (Kits::getInstance()->kitTypes as $kitType) {

			$directory = $this->getDataFolder();

			$directory = $directory . 'kits' . DIRECTORY_SEPARATOR;

			if (!file_exists($directory))
				mkdir($directory);

			$kitConfig = new Config($directory . $kitType . '.json', Config::JSON, [
                'knockback' => 0.4,
                'hitCooldown' => 10,
                'pickup-items' => false,
                'hunger' => false,
                'fallDamage' => false,
                'breakable' => false,
                'placeable' => false,
                'lightning' => false,
                'explosion' => false,
            ]);

			$this->kitSettings[$kitType] = $kitConfig->getAll();
		}
	}

	public function registerArenas(): void{
		$i = 0;

		$server = $this->getServer()->getWorldManager();

		foreach ($this->duelConfig['Arenas'] as $arenaName => $arena) {

			$server->loadWorld($arena["Level"]);

			$level = $server->getWorldByName($arena["Level"]);

			if ($level === null) {
				$this->getLogger()->error("Invalid map in config for " . $arenaName);
				continue;
			}

			$positions = [];

			foreach ($arena["Positions"] as $pos) {
				$bang = explode(':', $pos);
				$positions[] = new Position((float)$bang[0], (float)$bang[1], (float)$bang[2], $level);
			}

			$this->levels[$arenaName] = new DuelLevel($arenaName, $positions, $level, $arena["Author"], $arena["Kit-IDs"]);
			$i++;

			if($level !== $server->getDefaultWorld())
			    $server->unloadWorld($level);
		}

		if ($i === 0) {
			$this->getLogger()->emergency("No arenas setup!");
		$this->getServer()->getPluginManager()->disablePlugin($this);
		} else
			$this->getLogger()->info(TextFormat::GREEN . "Loaded $i arenas!");

	}
	public function reloadConfig(): void{
	    parent::reloadConfig();
        $this->duelConfig = $this->getConfig()->getAll();
    }
    /**
     * @return DataConnector
     */
    public function getDatabase(): DataConnector {
	    return $this->database;
    }
    /**
     * @return Elo
     */
    public function getEloManager(): Elo{
        return $this->eloManager;
    }
}
