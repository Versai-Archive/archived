<?php
declare(strict_types=1);

namespace Versai\Disguise\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Versai\Disguise\Task\GetNamesTask;
use Versai\Disguise\DisguiseAccessor;
use Versai\Disguise\Events\EnableDisguiseEvent;
use Versai\Disguise\Events\ReDisguiseEvent;
use Versai\Disguise\Events\DisableDisguiseEvent;
use Versai\Disguise\Events\DisableButNotDisguisedEvent;
use Versai\Disguise\Main;
use Versai\Disguise\NameAccessor;
use Versai\Disguise\Disguise;
use ReflectionException;
use function str_replace;
use function array_rand;

class DisguiseCommand extends Command
{
	private const LIST_FORMAT = TextFormat::BLUE . '{player} ' . TextFormat::WHITE . 'as' . TextFormat::BLUE . ' {disguise}';

	private const PERMISSION_PREFIX = 'disguise.';
	private const PERMISSION_USE = self::PERMISSION_PREFIX . 'use';
	private const PERMISSION_LIST = self::PERMISSION_PREFIX . 'list';

	/** @var DisguiseAccessor $disguiseAccessor */
	private DisguiseAccessor $disguiseAccessor;
	/** @var NameAccessor $nameAccessor */
	private NameAccessor $nameAccessor;
	/** @var Main $plugin */
	private Main $plugin;

    /**
     * DisguiseCommand constructor.
     * @param Main $main
     * @param DisguiseAccessor $disguiseAccessor
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
	public function __construct(Main $main, DisguiseAccessor $disguiseAccessor, string $name, string $description = "", string $usageMessage = null, $aliases = []) {
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = $main;
		$this->disguiseAccessor = $disguiseAccessor;
		$this->nameAccessor = $main->getNameAccessor();
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 * @throws ReflectionException
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void{
		if($sender instanceof Player) {

			if(isset($args[0])) {
				switch (strtolower($args[0])) {

					case 'me':

						if($sender->hasPermission(self::PERMISSION_USE)) {
							$senderName = $sender->getName();

							// Check if the player already has a disguise

							$disguisedPlayers = $this->disguiseAccessor->getDisguisedPlayers();

							$alreadyDisguised = isset($disguisedPlayers[$senderName]);

							// If they have a disguise, then their old skin should be fetched from the DisguisedPlayer object, otherwise grab current skin

							$oldSkin = $alreadyDisguised ? $disguisedPlayers[$senderName]->getOldSkin() : $sender->getSkin();

							// Get a random skin, but exclude the sender from the pool of players skins to grab from

							$newSkin = $this->getRandomPlayerSkin($sender);

							// Objects can't be passed to the callable since it's used in an async task so using an array to rebuild it later

							$oldSkin = $this->skinToArray($oldSkin);

							$newSkin = $this->skinToArray($newSkin);

							// Old name

							$oldName = $sender->getDisplayName();

							/**
							 * The function to do after the plugin has fetched a name
							 * @param string $name
							 * @param array|null $scraped
							 */
							$do = function (string $name) use ($alreadyDisguised, $senderName, $oldName, $oldSkin, $newSkin): void{
								$server = Server::getInstance();
								$player = $server->getPlayerExact($senderName);
								/** @var Main $plugin */
								$plugin =  $server->getPluginManager()->getPlugin('Disguise');
								if($player !== null) {

									if($oldSkin !== null) {
										$oldSkinObject = new Skin(
											$oldSkin['skinId'],
											$oldSkin['skinData'],
											$oldSkin['capeData'],
											$oldSkin['geometryName'],
											$oldSkin['geometryData']
										);
									} else
										$oldSkinObject = null;

									if($newSkin !== null) {
										$newSkinObject = new Skin(
											$newSkin['skinId'],
											$newSkin['skinData'],
											$newSkin['capeData'],
											$newSkin['geometryName'],
											$newSkin['geometryData']
										);
									} else
										$newSkinObject = null;

									$disguisedPlayerFactory = $plugin->getDisguisedPlayerFactory();
									$newDisguise = new Disguise($name, $newSkinObject);
									$oldDisguise = new Disguise($oldName, $oldSkinObject);
									// Create a new DisguisedPlayer object and a new Disguise
									$disguisedPlayer = $disguisedPlayerFactory->new($player, $newDisguise, $oldDisguise, $oldSkinObject);
								} else {
									$server->getLogger()->error('Tried to set disguise for null player!');
									return;
								}

								// Events

								if($alreadyDisguised)
									(new ReDisguiseEvent($disguisedPlayer))->call();
								else
									(new EnableDisguiseEvent($disguisedPlayer))->call();

								$disguisedPlayer->register();
							};

							$uname = $this->nameAccessor->getUniqueName();

							if($uname === null) {
							    $value = null;
                                $config = $this->plugin->getConfig();
							    if($config->get('fromFile'))
							        $value = $config->get('names');
								$name = $value[array_rand($value)];
								$do($name);
							} else
								$do($uname);
						} else
							$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

						break;

					case 'off':
					case 'disable':

						if($sender->hasPermission(self::PERMISSION_USE)) {
							$senderName = $sender->getName();

							$disguisedPlayers = $this->disguiseAccessor->getDisguisedPlayers();

							if(isset($disguisedPlayers[$senderName])) {
								$disguisedPlayer = $disguisedPlayers[$senderName];

								// Set their disguise to say the new data

								$disguisedPlayer->setDisguise(new Disguise($senderName, $disguisedPlayer->getOldSkin()));
								$disguisedPlayer->unregister();
								(new DisableDisguiseEvent($disguisedPlayer))->call();
							} else
								(new DisableButNotDisguisedEvent($sender))->call();
						} else
							$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

					break;

					case 'list':
						if($sender->hasPermission(self::PERMISSION_LIST)) {
							$disguisedPlayers = $this->disguiseAccessor->getDisguisedPlayers();
							if($disguisedPlayers === [])
								$sender->sendMessage(TextFormat::RED . 'No one is disguised!');
							else {
								$sender->sendMessage(TextFormat::BLUE . 'Active Disguises');
								foreach ($disguisedPlayers as $disguisedPlayer)
									$sender->sendMessage(str_replace(['{player}', '{disguise}'], [$disguisedPlayer->getPlayer()->getName(), $disguisedPlayer->getDisguise()->getName()], self::LIST_FORMAT));
							}
						} else
							$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

						break;

					default:
						throw new InvalidCommandSyntaxException();
				}
			} else
				throw new InvalidCommandSyntaxException();

		} else
			$sender->sendMessage(TextFormat::RED . 'You have to be a player to execute that command!');
	}

	/**
	 * Grabs a random players skin
	 * @param Player|null $exceptFor
	 * @return Skin|null
	 */
	public function getRandomPlayerSkin(Player $exceptFor = null): ?Skin
	{
		$players = Server::getInstance()->getOnlinePlayers();
		if($exceptFor !== null)
			unset($players[$exceptFor->getUniqueId()->getBytes()]);
		if($players === [])
			return null;

		$player = $players[array_rand($players)];

		return $player->getSkin();
	}

	/**
	 * Turns a Skin into an array because objects don't carry across threads
	 * @param null|Skin $skin
	 * @return array|null
	 */
	public function skinToArray(?Skin $skin): ?array
	{
		if($skin === null)
			return null;
		return [
			'skinId' => $skin->getSkinId(),
			'skinData' => $skin->getSkinData(),
			'capeData' => $skin->getCapeData(),
			'geometryName' => $skin->getGeometryName(),
			'geometryData' => $skin->getGeometryData()
		];
	}
}