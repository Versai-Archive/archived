<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine;

use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\UnknownBlock;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\inventory\CraftingGrid;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\item\Consumable;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\item\WritableBook;
use pocketmine\item\WrittenBook;
use pocketmine\lang\TextContainer;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\ChunkListener;
use pocketmine\level\ChunkLoader;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\CompressBatchPromise;
use pocketmine\network\mcpe\NetworkCipher;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\ProcessLoginTask;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkChunkPublisherUpdatePacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\types\CommandData;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\Plugin;
use pocketmine\tile\Tile;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;
use function abs;
use function array_merge;
use function assert;
use function ceil;
use function count;
use function explode;
use function floor;
use function fmod;
use function get_class;
use function in_array;
use function is_int;
use function json_encode;
use function json_last_error_msg;
use function max;
use function microtime;
use function min;
use function preg_match;
use function round;
use function spl_object_id;
use function sqrt;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use function ucfirst;
use const M_PI;
use const M_SQRT3;
use const PHP_INT_MAX;


/**
 * Main class that handles networking, recovery, and packet sending to the server part
 */
class Player extends Human implements CommandSender, ChunkLoader, ChunkListener, IPlayer{

	/**
	 * Checks a supplied username and checks it is valid.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function isValidUserName(?string $name) : bool{
		if($name === null){
			return false;
		}

		$lname = strtolower($name);
		$len = strlen($name);
		return $lname !== "rcon" and $lname !== "console" and $len >= 1 and $len <= 16 and preg_match("/[^A-Za-z0-9_ ]/", $name) === 0;
	}

	/** @var NetworkSession */
	protected $networkSession;

	/** @var bool */
	protected $loggedIn = false;

	/** @var bool */
	public $spawned = false;

	/** @var string */
	protected $username = "";
	/** @var string */
	protected $iusername = "";
	/** @var string */
	protected $displayName = "";
	/** @var int */
	protected $randomClientId;
	/** @var string */
	protected $xuid = "";
	/** @var bool */
	protected $authenticated = false;
	/** @var PlayerInfo|null */
	protected $playerInfo = null;

	protected $windowCnt = 2;
	/** @var int[] */
	protected $windows = [];
	/** @var Inventory[] */
	protected $windowIndex = [];
	/** @var bool[] */
	protected $permanentWindows = [];
	/** @var PlayerCursorInventory */
	protected $cursorInventory;
	/** @var CraftingGrid */
	protected $craftingGrid = null;

	/** @var int */
	protected $messageCounter = 2;
	/** @var bool */
	protected $removeFormat = true;

	/** @var bool[] name of achievement => bool */
	protected $achievements = [];
	/** @var int */
	protected $firstPlayed;
	/** @var int */
	protected $lastPlayed;
	/** @var int */
	protected $gamemode;

	/** @var bool[] chunkHash => bool (true = sent, false = needs sending) */
	public $usedChunks = [];
	/** @var bool[] chunkHash => dummy */
	protected $loadQueue = [];
	/** @var int */
	protected $nextChunkOrderRun = 5;

	/** @var int */
	protected $viewDistance = -1;
	/** @var int */
	protected $spawnThreshold;
	/** @var int */
	protected $spawnChunkLoadCount = 0;
	/** @var int */
	protected $chunksPerTick;

	/** @var bool[] map: raw UUID (string) => bool */
	protected $hiddenPlayers = [];

	/** @var Vector3|null */
	protected $newPosition;
	/** @var bool */
	protected $isTeleporting = false;
	/** @var int */
	protected $inAirTicks = 0;
	/** @var float */
	protected $stepHeight = 0.6;
	/** @var bool */
	protected $allowMovementCheats = false;

	/** @var Vector3|null */
	protected $sleeping = null;
	/** @var Position|null */
	private $spawnPosition = null;

	//TODO: Abilities
	/** @var bool */
	protected $autoJump = true;
	/** @var bool */
	protected $allowFlight = false;
	/** @var bool */
	protected $flying = false;

	/** @var PermissibleBase */
	private $perm = null;

	/** @var int|null */
	protected $lineHeight = null;
	/** @var string */
	protected $locale = "en_US";

	/** @var int */
	protected $startAction = -1;
	/** @var int[] ID => ticks map */
	protected $usedItemsCooldown = [];

	/** @var int */
	protected $formIdCounter = 0;
	/** @var Form[] */
	protected $forms = [];

	/**
	 * @return TranslationContainer|string
	 */
	public function getLeaveMessage(){
		if($this->spawned){
			return new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.left", [
				$this->getDisplayName()
			]);
		}

		return "";
	}

	/**
	 * This might disappear in the future. Please use getUniqueId() instead.
	 * @deprecated
	 *
	 * @return int
	 */
	public function getClientId(){
		return $this->randomClientId;
	}

	public function isBanned() : bool{
		return $this->server->getNameBans()->isBanned($this->username);
	}

	public function setBanned(bool $banned) : void{
		if($banned){
			$this->server->getNameBans()->addBan($this->getName(), null, null, null);
			$this->kick("You have been banned");
		}else{
			$this->server->getNameBans()->remove($this->getName());
		}
	}

	public function isWhitelisted() : bool{
		return $this->server->isWhitelisted($this->username);
	}

	public function setWhitelisted(bool $value) : void{
		if($value){
			$this->server->addWhitelist($this->username);
		}else{
			$this->server->removeWhitelist($this->username);
		}
	}

	public function isAuthenticated() : bool{
		return $this->authenticated;
	}

	/**
	 * If the player is logged into Xbox Live, returns their Xbox user ID (XUID) as a string. Returns an empty string if
	 * the player is not logged into Xbox Live.
	 *
	 * @return string
	 */
	public function getXuid() : string{
		return $this->xuid;
	}

	/**
	 * Returns the player's UUID. This should be preferred over their Xbox user ID (XUID) because UUID is a standard
	 * format which will never change, and all players will have one regardless of whether they are logged into Xbox
	 * Live.
	 *
	 * The UUID is comprised of:
	 * - when logged into XBL: a hash of their XUID (and as such will not change for the lifetime of the XBL account)
	 * - when NOT logged into XBL: a hash of their name + clientID + secret device ID.
	 *
	 * WARNING: UUIDs of players **not logged into Xbox Live** CAN BE FAKED and SHOULD NOT be trusted!
	 *
	 * (In the olden days this method used to return a fake UUID computed by the server, which was used by plugins such
	 * as SimpleAuth for authentication. This is NOT SAFE anymore as this UUID is now what was given by the client, NOT
	 * a server-computed UUID.)
	 *
	 * @return UUID
	 */
	public function getUniqueId() : UUID{
		return parent::getUniqueId();
	}

	public function getPlayer() : ?Player{
		return $this;
	}

	/**
	 * TODO: not sure this should be nullable
	 * @return int|null
	 */
	public function getFirstPlayed() : ?int{
		return $this->firstPlayed;
	}

	/**
	 * TODO: not sure this should be nullable
	 * @return int|null
	 */
	public function getLastPlayed() : ?int{
		return $this->lastPlayed;
	}

	public function hasPlayedBefore() : bool{
		return $this->lastPlayed - $this->firstPlayed > 1; // microtime(true) - microtime(true) may have less than one millisecond difference
	}

	public function setAllowFlight(bool $value){
		$this->allowFlight = $value;
		$this->sendSettings();
	}

	public function getAllowFlight() : bool{
		return $this->allowFlight;
	}

	public function setFlying(bool $value){
		if($this->flying !== $value){
			$this->flying = $value;
			$this->resetFallDistance();
			$this->sendSettings();
		}
	}

	public function isFlying() : bool{
		return $this->flying;
	}

	public function setAutoJump(bool $value){
		$this->autoJump = $value;
		$this->sendSettings();
	}

	public function hasAutoJump() : bool{
		return $this->autoJump;
	}

	public function allowMovementCheats() : bool{
		return $this->allowMovementCheats;
	}

	public function setAllowMovementCheats(bool $value = true){
		$this->allowMovementCheats = $value;
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player) : void{
		if($this->spawned and $player->spawned and $this->isAlive() and $player->isAlive() and $player->getLevel() === $this->level and $player->canSee($this) and !$this->isSpectator()){
			parent::spawnTo($player);
		}
	}

	/**
	 * @return Server
	 */
	public function getServer() : Server{
		return $this->server;
	}

	/**
	 * @return bool
	 */
	public function getRemoveFormat() : bool{
		return $this->removeFormat;
	}

	/**
	 * @param bool $remove
	 */
	public function setRemoveFormat(bool $remove = true){
		$this->removeFormat = $remove;
	}

	public function getScreenLineHeight() : int{
		return $this->lineHeight ?? 7;
	}

	public function setScreenLineHeight(?int $height) : void{
		if($height !== null and $height < 1){
			throw new \InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function canSee(Player $player) : bool{
		return !isset($this->hiddenPlayers[$player->getRawUniqueId()]);
	}

	/**
	 * @param Player $player
	 */
	public function hidePlayer(Player $player){
		if($player === $this){
			return;
		}
		$this->hiddenPlayers[$player->getRawUniqueId()] = true;
		$player->despawnFrom($this);
	}

	/**
	 * @param Player $player
	 */
	public function showPlayer(Player $player){
		if($player === $this){
			return;
		}
		unset($this->hiddenPlayers[$player->getRawUniqueId()]);
		if($player->isOnline()){
			$player->spawnTo($this);
		}
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeCollidedWith() : bool{
		return !$this->isSpectator() and parent::canBeCollidedWith();
	}

	public function resetFallDistance() : void{
		parent::resetFallDistance();
		$this->inAirTicks = 0;
	}

	public function getViewDistance() : int{
		return $this->viewDistance;
	}

	public function setViewDistance(int $distance){
		$this->viewDistance = $this->server->getAllowedViewDistance($distance);

		$this->spawnThreshold = (int) (min($this->viewDistance, $this->server->getProperty("chunk-sending.spawn-radius", 4)) ** 2 * M_PI);

		$this->nextChunkOrderRun = 0;

		$pk = new ChunkRadiusUpdatedPacket();
		$pk->radius = $this->viewDistance;
		$this->sendDataPacket($pk);

		$this->server->getLogger()->debug("Setting view distance for " . $this->getName() . " to " . $this->viewDistance . " (requested " . $distance . ")");
	}

	/**
	 * @return bool
	 */
	public function isOnline() : bool{
		return $this->isConnected() and $this->loggedIn;
	}

	/**
	 * @return bool
	 */
	public function isOp() : bool{
		return $this->server->isOp($this->getName());
	}

	/**
	 * @param bool $value
	 */
	public function setOp(bool $value) : void{
		if($value === $this->isOp()){
			return;
		}

		if($value){
			$this->server->addOp($this->getName());
		}else{
			$this->server->removeOp($this->getName());
		}

		$this->sendSettings();
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 */
	public function isPermissionSet($name) : bool{
		return $this->perm->isPermissionSet($name);
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 *
	 * @throws \InvalidStateException if the player is closed
	 */
	public function hasPermission($name) : bool{
		if($this->closed){
			throw new \InvalidStateException("Trying to get permissions of closed player");
		}
		return $this->perm->hasPermission($name);
	}

	/**
	 * @param Plugin $plugin
	 * @param string $name
	 * @param bool   $value
	 *
	 * @return PermissionAttachment
	 */
	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment{
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	/**
	 * @param PermissionAttachment $attachment
	 */
	public function removeAttachment(PermissionAttachment $attachment) : void{
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions() : void{
		$permManager = PermissionManager::getInstance();
		$permManager->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		$permManager->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

		if($this->perm === null){
			return;
		}

		$this->perm->recalculatePermissions();

		if($this->spawned){
			if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
				$permManager->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			}
			if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
				$permManager->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
			}

			$this->sendCommandData();
		}
	}

	/**
	 * @return PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions() : array{
		return $this->perm->getEffectivePermissions();
	}

	public function sendCommandData(){
		$pk = new AvailableCommandsPacket();
		foreach($this->server->getCommandMap()->getCommands() as $name => $command){
			if(isset($pk->commandData[$command->getName()]) or $command->getName() === "help" or !$command->testPermissionSilent($this)){
				continue;
			}

			$data = new CommandData();
			//TODO: commands containing uppercase letters in the name crash 1.9.0 client
			$data->commandName = strtolower($command->getName());
			$data->commandDescription = $this->server->getLanguage()->translateString($command->getDescription());
			$data->flags = 0;
			$data->permission = 0;

			$parameter = new CommandParameter();
			$parameter->paramName = "args";
			$parameter->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
			$parameter->isOptional = true;
			$data->overloads[0][0] = $parameter;

			$aliases = $command->getAliases();
			if(!empty($aliases)){
				if(!in_array($data->commandName, $aliases, true)){
					//work around a client bug which makes the original name not show when aliases are used
					$aliases[] = $data->commandName;
				}
				$data->aliases = new CommandEnum();
				$data->aliases->enumName = ucfirst($command->getName()) . "Aliases";
				$data->aliases->enumValues = $aliases;
			}

			$pk->commandData[$command->getName()] = $data;
		}

		$this->sendDataPacket($pk);

	}

	/**
	 * @param Server         $server
	 * @param NetworkSession $session
	 */
	public function __construct(Server $server, NetworkSession $session){
		$this->server = $server;
		$this->networkSession = $session;

		$this->perm = new PermissibleBase($this);
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
		$this->spawnThreshold = (int) (($this->server->getProperty("chunk-sending.spawn-radius", 4) ** 2) * M_PI);

		$this->allowMovementCheats = (bool) $this->server->getProperty("player.anti-cheat.allow-movement-cheats", false);
	}

	/**
	 * @return bool
	 */
	public function isConnected() : bool{
		return $this->networkSession !== null;
	}

	/**
	 * @return NetworkSession
	 */
	public function getNetworkSession() : NetworkSession{
		return $this->networkSession;
	}

	/**
	 * Gets the username
	 * @return string
	 */
	public function getName() : string{
		return $this->username;
	}

	/**
	 * @return string
	 */
	public function getLowerCaseName() : string{
		return $this->iusername;
	}

	/**
	 * Gets the "friendly" name to display of this player to use in the chat.
	 *
	 * @return string
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	/**
	 * @param string $name
	 */
	public function setDisplayName(string $name){
		$this->displayName = $name;
		if($this->spawned){
			$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $this->getSkin(), $this->getXuid());
		}
	}

	/**
	 * Returns the player's locale, e.g. en_US.
	 * @return string
	 */
	public function getLocale() : string{
		return $this->locale;
	}

	/**
	 * Called when a player changes their skin.
	 * Plugin developers should not use this, use setSkin() and sendSkin() instead.
	 *
	 * @param Skin   $skin
	 * @param string $newSkinName
	 * @param string $oldSkinName
	 *
	 * @return bool
	 */
	public function changeSkin(Skin $skin, string $newSkinName, string $oldSkinName) : bool{
		if(!$skin->isValid()){
			return false;
		}

		$ev = new PlayerChangeSkinEvent($this, $this->getSkin(), $skin);
		$ev->call();

		if($ev->isCancelled()){
			$this->sendSkin([$this]);
			return true;
		}

		$this->setSkin($ev->getNewSkin());
		$this->sendSkin($this->server->getOnlinePlayers());
		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * If null is given, will additionally send the skin to the player itself as well as its viewers.
	 */
	public function sendSkin(?array $targets = null) : void{
		parent::sendSkin($targets ?? $this->server->getOnlinePlayers());
	}

	/**
	 * Gets the player IP address
	 *
	 * @return string
	 */
	public function getAddress() : string{
		return $this->networkSession->getIp();
	}

	/**
	 * @return int
	 */
	public function getPort() : int{
		return $this->networkSession->getPort();
	}

	/**
	 * Returns the last measured latency for this player, in milliseconds. This is measured automatically and reported
	 * back by the network interface.
	 *
	 * @return int
	 */
	public function getPing() : int{
		return $this->networkSession->getPing();
	}

	/**
	 * @return Position
	 */
	public function getNextPosition() : Position{
		return $this->newPosition !== null ? Position::fromObject($this->newPosition, $this->level) : $this->getPosition();
	}

	public function getInAirTicks() : int{
		return $this->inAirTicks;
	}

	/**
	 * Returns whether the player is currently using an item (right-click and hold).
	 * @return bool
	 */
	public function isUsingItem() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_ACTION) and $this->startAction > -1;
	}

	public function setUsingItem(bool $value){
		$this->startAction = $value ? $this->server->getTick() : -1;
		$this->setGenericFlag(self::DATA_FLAG_ACTION, $value);
	}

	/**
	 * Returns how long the player has been using their currently-held item for. Used for determining arrow shoot force
	 * for bows.
	 *
	 * @return int
	 */
	public function getItemUseDuration() : int{
		return $this->startAction === -1 ? -1 : ($this->server->getTick() - $this->startAction);
	}

	/**
	 * Returns whether the player has a cooldown period left before it can use the given item again.
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function hasItemCooldown(Item $item) : bool{
		$this->checkItemCooldowns();
		return isset($this->usedItemsCooldown[$item->getId()]);
	}

	/**
	 * Resets the player's cooldown time for the given item back to the maximum.
	 *
	 * @param Item $item
	 */
	public function resetItemCooldown(Item $item) : void{
		$ticks = $item->getCooldownTicks();
		if($ticks > 0){
			$this->usedItemsCooldown[$item->getId()] = $this->server->getTick() + $ticks;
		}
	}

	protected function checkItemCooldowns() : void{
		$serverTick = $this->server->getTick();
		foreach($this->usedItemsCooldown as $itemId => $cooldownUntil){
			if($cooldownUntil <= $serverTick){
				unset($this->usedItemsCooldown[$itemId]);
			}
		}
	}

	protected function switchLevel(Level $targetLevel) : bool{
		$oldLevel = $this->level;
		if(parent::switchLevel($targetLevel)){
			if($oldLevel !== null){
				foreach($this->usedChunks as $index => $d){
					Level::getXZ($index, $X, $Z);
					$this->unloadChunk($X, $Z, $oldLevel);
				}
			}

			$this->usedChunks = [];
			$this->loadQueue = [];
			$this->level->sendTime($this);
			$this->level->sendDifficulty($this);

			return true;
		}

		return false;
	}

	protected function unloadChunk(int $x, int $z, ?Level $level = null){
		$level = $level ?? $this->level;
		$index = Level::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			foreach($level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}

			unset($this->usedChunks[$index]);
		}
		$level->unregisterChunkLoader($this, $x, $z);
		$level->unregisterChunkListener($this, $x, $z);
		unset($this->loadQueue[$index]);
	}

	public function sendChunk(int $x, int $z, CompressBatchPromise $promise){
		if(!$this->isConnected()){
			return;
		}

		$this->usedChunks[Level::chunkHash($x, $z)] = true;

		$this->networkSession->queueCompressed($promise);

		if($this->spawned){
			foreach($this->level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this and !$entity->isClosed() and $entity->isAlive()){
					$entity->spawnTo($this);
				}
			}
		}elseif(++$this->spawnChunkLoadCount >= $this->spawnThreshold){
			$this->spawnChunkLoadCount = -1;
			$this->spawned = true;

			foreach($this->usedChunks as $index => $c){
				Level::getXZ($index, $chunkX, $chunkZ);
				foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
					if($entity !== $this and !$entity->isClosed() and $entity->isAlive() and !$entity->isFlaggedForDespawn()){
						$entity->spawnTo($this);
					}
				}
			}

			$this->networkSession->onTerrainReady();
		}
	}

	protected function sendNextChunk(){
		if(!$this->isConnected()){
			return;
		}

		Timings::$playerChunkSendTimer->startTiming();

		$count = 0;
		foreach($this->loadQueue as $index => $distance){
			if($count >= $this->chunksPerTick){
				break;
			}

			$X = null;
			$Z = null;
			Level::getXZ($index, $X, $Z);
			assert(is_int($X) and is_int($Z));

			++$count;

			$this->usedChunks[$index] = false;
			$this->level->registerChunkLoader($this, $X, $Z, true);
			$this->level->registerChunkListener($this, $X, $Z);

			if(!$this->level->populateChunk($X, $Z)){
				continue;
			}

			unset($this->loadQueue[$index]);
			$this->level->requestChunk($X, $Z, $this);
		}

		Timings::$playerChunkSendTimer->stopTiming();
	}

	public function doFirstSpawn(){
		$this->networkSession->onSpawn();

		if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		}
		if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
		}

		$ev = new PlayerJoinEvent($this,
			new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.joined", [
				$this->getDisplayName()
			])
		);
		$ev->call();
		if(strlen(trim((string) $ev->getJoinMessage())) > 0){
			$this->server->broadcastMessage($ev->getJoinMessage());
		}

		$this->noDamageTicks = 60;

		$this->spawnToAll();

		if($this->server->getUpdater()->hasUpdate() and $this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE) and $this->server->getProperty("auto-updater.on-update.warn-ops", true)){
			$this->server->getUpdater()->showPlayerUpdate($this);
		}

		if($this->getHealth() <= 0){
			$this->respawn();
		}
	}

	protected function selectChunks() : \Generator{
		$radius = $this->server->getAllowedViewDistance($this->viewDistance);
		$radiusSquared = $radius ** 2;

		$centerX = $this->getFloorX() >> 4;
		$centerZ = $this->getFloorZ() >> 4;

		for($x = 0; $x < $radius; ++$x){
			for($z = 0; $z <= $x; ++$z){
				if(($x ** 2 + $z ** 2) > $radiusSquared){
					break; //skip to next band
				}

				//If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

				/* Top right quadrant */
				yield Level::chunkHash($centerX + $x, $centerZ + $z);
				/* Top left quadrant */
				yield Level::chunkHash($centerX - $x - 1, $centerZ + $z);
				/* Bottom right quadrant */
				yield Level::chunkHash($centerX + $x, $centerZ - $z - 1);
				/* Bottom left quadrant */
				yield Level::chunkHash($centerX - $x - 1, $centerZ - $z - 1);

				if($x !== $z){
					/* Top right quadrant mirror */
					yield Level::chunkHash($centerX + $z, $centerZ + $x);
					/* Top left quadrant mirror */
					yield Level::chunkHash($centerX - $z - 1, $centerZ + $x);
					/* Bottom right quadrant mirror */
					yield Level::chunkHash($centerX + $z, $centerZ - $x - 1);
					/* Bottom left quadrant mirror */
					yield Level::chunkHash($centerX - $z - 1, $centerZ - $x - 1);
				}
			}
		}
	}

	protected function orderChunks() : void{
		if(!$this->isConnected() or $this->viewDistance === -1){
			return;
		}

		Timings::$playerChunkOrderTimer->startTiming();

		$newOrder = [];
		$unloadChunks = $this->usedChunks;

		foreach($this->selectChunks() as $hash){
			if(!isset($this->usedChunks[$hash]) or $this->usedChunks[$hash] === false){
				$newOrder[$hash] = true;
			}
			unset($unloadChunks[$hash]);
		}

		foreach($unloadChunks as $index => $bool){
			Level::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$this->loadQueue = $newOrder;
		if(!empty($this->loadQueue) or !empty($unloadChunks)){
			$pk = new NetworkChunkPublisherUpdatePacket();
			$pk->x = $this->getFloorX();
			$pk->y = $this->getFloorY();
			$pk->z = $this->getFloorZ();
			$pk->radius = $this->viewDistance * 16; //blocks, not chunks >.>
			$this->sendDataPacket($pk);
		}

		Timings::$playerChunkOrderTimer->stopTiming();
	}

	public function doChunkRequests(){
		if(!$this->isOnline()){
			return;
		}

		if($this->nextChunkOrderRun !== PHP_INT_MAX and $this->nextChunkOrderRun-- <= 0){
			$this->nextChunkOrderRun = PHP_INT_MAX;
			$this->orderChunks();
		}

		if(count($this->loadQueue) > 0){
			$this->sendNextChunk();
		}
	}

	/**
	 * @return Position
	 */
	public function getSpawn(){
		if($this->hasValidSpawnPosition()){
			return $this->spawnPosition;
		}else{
			$level = $this->server->getLevelManager()->getDefaultLevel();

			return $level->getSafeSpawn();
		}
	}

	/**
	 * @return bool
	 */
	public function hasValidSpawnPosition() : bool{
		return $this->spawnPosition !== null and $this->spawnPosition->isValid();
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a
	 * Position object
	 *
	 * @param Vector3|Position $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$level = $this->level;
		}else{
			$level = $pos->getLevel();
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $level);
		$pk = new SetSpawnPositionPacket();
		$pk->x = $this->spawnPosition->getFloorX();
		$pk->y = $this->spawnPosition->getFloorY();
		$pk->z = $this->spawnPosition->getFloorZ();
		$pk->spawnType = SetSpawnPositionPacket::TYPE_PLAYER_SPAWN;
		$pk->spawnForced = false;
		$this->sendDataPacket($pk);
	}

	/**
	 * @return bool
	 */
	public function isSleeping() : bool{
		return $this->sleeping !== null;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return bool
	 */
	public function sleepOn(Vector3 $pos) : bool{
		if(!$this->isOnline()){
			return false;
		}

		$pos = $pos->floor();
		$b = $this->level->getBlock($pos);

		$ev = new PlayerBedEnterEvent($this, $b);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		if($b instanceof Bed){
			$b->setOccupied();
		}

		$this->sleeping = clone $pos;

		$this->propertyManager->setBlockPos(self::DATA_PLAYER_BED_POSITION, $pos);
		$this->setPlayerFlag(self::DATA_PLAYER_FLAG_SLEEP, true);

		$this->setSpawn($pos);

		$this->level->setSleepTicks(60);

		return true;
	}

	public function stopSleep(){
		if($this->sleeping instanceof Vector3){
			$b = $this->level->getBlock($this->sleeping);
			if($b instanceof Bed){
				$b->setOccupied(false);
			}
			(new PlayerBedLeaveEvent($this, $b))->call();

			$this->sleeping = null;
			$this->propertyManager->setBlockPos(self::DATA_PLAYER_BED_POSITION, null);
			$this->setPlayerFlag(self::DATA_PLAYER_FLAG_SLEEP, false);

			$this->level->setSleepTicks(0);

			$this->broadcastAnimation([$this], AnimatePacket::ACTION_STOP_SLEEP);
		}
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function hasAchievement(string $achievementId) : bool{
		if(!isset(Achievement::$list[$achievementId])){
			return false;
		}

		return $this->achievements[$achievementId] ?? false;
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function awardAchievement(string $achievementId) : bool{
		if(isset(Achievement::$list[$achievementId]) and !$this->hasAchievement($achievementId)){
			foreach(Achievement::$list[$achievementId]["requires"] as $requirementId){
				if(!$this->hasAchievement($requirementId)){
					return false;
				}
			}
			$ev = new PlayerAchievementAwardedEvent($this, $achievementId);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->achievements[$achievementId] = true;
				Achievement::broadcast($this, $achievementId);

				return true;
			}else{
				return false;
			}
		}

		return false;
	}

	/**
	 * @param string $achievementId
	 */
	public function removeAchievement(string $achievementId){
		if($this->hasAchievement($achievementId)){
			$this->achievements[$achievementId] = false;
		}
	}

	/**
	 * @return int
	 */
	public function getGamemode() : int{
		return $this->gamemode;
	}

	/**
	 * @internal
	 *
	 * Returns a client-friendly gamemode of the specified real gamemode
	 * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
	 *
	 * TODO: remove this when Spectator Mode gets added properly to MCPE
	 *
	 * @param int $gamemode
	 *
	 * @return int
	 */
	public static function getClientFriendlyGamemode(int $gamemode) : int{
		$gamemode &= 0x03;
		if($gamemode === GameMode::SPECTATOR){
			return GameMode::CREATIVE;
		}

		return $gamemode;
	}

	/**
	 * Sets the gamemode, and if needed, kicks the Player.
	 *
	 * @param int  $gm
	 * @param bool $client if the client made this change in their GUI
	 *
	 * @return bool
	 */
	public function setGamemode(int $gm, bool $client = false) : bool{
		if($gm < 0 or $gm > 3 or $this->gamemode === $gm){
			return false;
		}

		$ev = new PlayerGameModeChangeEvent($this, $gm);
		$ev->call();
		if($ev->isCancelled()){
			if($client){ //gamemode change by client in the GUI
				$this->sendGamemode();
			}
			return false;
		}

		$this->gamemode = $gm;

		$this->allowFlight = $this->isCreative();
		if($this->isSpectator()){
			$this->setFlying(true);
			$this->keepMovement = true;
			$this->despawnFromAll();
		}else{
			$this->keepMovement = $this->allowMovementCheats;
			if($this->isSurvival()){
				$this->setFlying(false);
			}
			$this->spawnToAll();
		}

		if(!$client){ //Gamemode changed by server, do not send for client changes
			$this->sendGamemode();
		}else{
			Command::broadcastCommandMessage($this, new TranslationContainer("commands.gamemode.success.self", [GameMode::toTranslation($gm)]));
		}

		$this->sendSettings();
		$this->inventory->sendCreativeContents();

		return true;
	}

	/**
	 * @internal
	 * Sends the player's gamemode to the client.
	 */
	public function sendGamemode(){
		$pk = new SetPlayerGameTypePacket();
		$pk->gamemode = Player::getClientFriendlyGamemode($this->gamemode);
		$this->sendDataPacket($pk);
	}

	/**
	 * Sends all the option flags
	 */
	public function sendSettings(){
		$pk = new AdventureSettingsPacket();

		$pk->setFlag(AdventureSettingsPacket::WORLD_IMMUTABLE, $this->isSpectator());
		$pk->setFlag(AdventureSettingsPacket::NO_PVP, $this->isSpectator());
		$pk->setFlag(AdventureSettingsPacket::AUTO_JUMP, $this->autoJump);
		$pk->setFlag(AdventureSettingsPacket::ALLOW_FLIGHT, $this->allowFlight);
		$pk->setFlag(AdventureSettingsPacket::NO_CLIP, $this->isSpectator());
		$pk->setFlag(AdventureSettingsPacket::FLYING, $this->flying);

		$pk->commandPermission = ($this->isOp() ? AdventureSettingsPacket::PERMISSION_OPERATOR : AdventureSettingsPacket::PERMISSION_NORMAL);
		$pk->playerPermission = ($this->isOp() ? PlayerPermissions::OPERATOR : PlayerPermissions::MEMBER);
		$pk->entityUniqueId = $this->getId();

		$this->sendDataPacket($pk);
	}

	/**
	 * NOTE: Because Survival and Adventure Mode share some similar behaviour, this method will also return true if the player is
	 * in Adventure Mode. Supply the $literal parameter as true to force a literal Survival Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 *
	 * @return bool
	 */
	public function isSurvival(bool $literal = false) : bool{
		return $this->gamemode === GameMode::SURVIVAL or (!$literal and $this->gamemode === GameMode::ADVENTURE);
	}

	/**
	 * NOTE: Because Creative and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Creative Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 *
	 * @return bool
	 */
	public function isCreative(bool $literal = false) : bool{
		return $this->gamemode === GameMode::CREATIVE or (!$literal and $this->gamemode === GameMode::SPECTATOR);
	}

	/**
	 * NOTE: Because Adventure and Spectator Mode share some similar behaviour, this method will also return true if the player is
	 * in Spectator Mode. Supply the $literal parameter as true to force a literal Adventure Mode check.
	 *
	 * @param bool $literal whether a literal check should be performed
	 *
	 * @return bool
	 */
	public function isAdventure(bool $literal = false) : bool{
		return $this->gamemode === GameMode::ADVENTURE or (!$literal and $this->gamemode === GameMode::SPECTATOR);
	}

	/**
	 * @return bool
	 */
	public function isSpectator() : bool{
		return $this->gamemode === GameMode::SPECTATOR;
	}

	public function isFireProof() : bool{
		return $this->isCreative();
	}

	public function getDrops() : array{
		if(!$this->isCreative()){
			return parent::getDrops();
		}

		return [];
	}

	public function getXpDropAmount() : int{
		if(!$this->isCreative()){
			return parent::getXpDropAmount();
		}

		return 0;
	}

	protected function checkGroundState(float $movX, float $movY, float $movZ, float $dx, float $dy, float $dz) : void{
		$bb = clone $this->boundingBox;
		$bb->minY = $this->y - 0.2;
		$bb->maxY = $this->y + 0.2;

		$this->onGround = $this->isCollided = count($this->level->getCollisionBlocks($bb, true)) > 0;
	}

	public function canBeMovedByCurrents() : bool{
		return false; //currently has no server-side movement
	}

	protected function checkNearEntities(){
		foreach($this->level->getNearbyEntities($this->boundingBox->expandedCopy(1, 0.5, 1), $this) as $entity){
			$entity->scheduleUpdate();

			if(!$entity->isAlive() or $entity->isFlaggedForDespawn()){
				continue;
			}

			$entity->onCollideWithPlayer($this);
		}
	}

	protected function processMovement(int $tickDiff){
		if($this->newPosition === null or $this->isSleeping()){
			return;
		}

		assert($this->x !== null and $this->y !== null and $this->z !== null);
		assert($this->newPosition->x !== null and $this->newPosition->y !== null and $this->newPosition->z !== null);

		$newPos = $this->newPosition;
		$distanceSquared = $newPos->distanceSquared($this);

		$revert = false;

		if(($distanceSquared / ($tickDiff ** 2)) > 100){
			/* !!! BEWARE YE WHO ENTER HERE !!!
			 *
			 * This is NOT an anti-cheat check. It is a safety check.
			 * Without it hackers can teleport with freedom on their own and cause lots of undesirable behaviour, like
			 * freezes, lag spikes and memory exhaustion due to sync chunk loading and collision checks across large distances.
			 * Not only that, but high-latency players can trigger such behaviour innocently.
			 *
			 * If you must tamper with this code, be aware that this can cause very nasty results. Do not waste our time
			 * asking for help if you suffer the consequences of messing with this.
			 */
			$this->server->getLogger()->warning($this->getName() . " moved too fast, reverting movement");
			$this->server->getLogger()->debug("Old position: " . $this->asVector3() . ", new position: " . $this->newPosition);
			$revert = true;
		}elseif(!$this->level->isInLoadedTerrain($newPos) or !$this->level->isChunkGenerated($newPos->getFloorX() >> 4, $newPos->getFloorZ() >> 4)){
			$revert = true;
			$this->nextChunkOrderRun = 0;
		}

		if(!$revert and $distanceSquared != 0){
			$dx = $newPos->x - $this->x;
			$dy = $newPos->y - $this->y;
			$dz = $newPos->z - $this->z;

			$this->move($dx, $dy, $dz);

			$diff = $this->distanceSquared($newPos) / $tickDiff ** 2;

			if($this->isSurvival() and !$revert and $diff > 0.0625){
				$ev = new PlayerIllegalMoveEvent($this, $newPos, $this->lastLocation->asVector3());
				$ev->setCancelled($this->allowMovementCheats);

				$ev->call();

				if(!$ev->isCancelled()){
					$revert = true;
					$this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidMove", [$this->getName()]));
					$this->server->getLogger()->debug("Old position: " . $this->asVector3() . ", new position: " . $this->newPosition);
				}
			}

			if($diff > 0 and !$revert){
				$this->setPosition($newPos);
			}
		}

		$from = clone $this->lastLocation;
		$to = $this->asLocation();

		$delta = $to->distanceSquared($from);
		$deltaAngle = abs($this->lastLocation->yaw - $to->yaw) + abs($this->lastLocation->pitch - $to->pitch);

		if(!$revert and ($delta > 0.0001 or $deltaAngle > 1.0)){
			$this->lastLocation = clone $to; //avoid PlayerMoveEvent modifying this

			$ev = new PlayerMoveEvent($this, $from, $to);

			$ev->call();

			if(!($revert = $ev->isCancelled())){ //Yes, this is intended
				if($to->distanceSquared($ev->getTo()) > 0.01){ //If plugins modify the destination
					$this->teleport($ev->getTo());
				}else{
					$this->broadcastMovement();

					$distance = sqrt((($from->x - $to->x) ** 2) + (($from->z - $to->z) ** 2));
					//TODO: check swimming (adds 0.015 exhaustion in MCPE)
					if($this->isSprinting()){
						$this->exhaust(0.1 * $distance, PlayerExhaustEvent::CAUSE_SPRINTING);
					}else{
						$this->exhaust(0.01 * $distance, PlayerExhaustEvent::CAUSE_WALKING);
					}
				}
			}
		}

		if($revert){
			$this->lastLocation = $from;

			$this->setPosition($from);
			$this->sendPosition($from, $from->yaw, $from->pitch, MovePlayerPacket::MODE_RESET);
		}else{
			if($distanceSquared != 0 and $this->nextChunkOrderRun > 20){
				$this->nextChunkOrderRun = 20;
			}
		}

		$this->newPosition = null;
	}

	public function fall(float $fallDistance) : void{
		if(!$this->flying){
			parent::fall($fallDistance);
		}
	}

	public function jump() : void{
		(new PlayerJumpEvent($this))->call();
		parent::jump();
	}

	public function setMotion(Vector3 $motion) : bool{
		if(parent::setMotion($motion)){
			$this->broadcastMotion();

			return true;
		}
		return false;
	}

	protected function updateMovement(bool $teleport = false) : void{

	}

	protected function tryChangeMovement() : void{

	}

	public function sendAttributes(bool $sendAll = false){
		$entries = $sendAll ? $this->attributeMap->getAll() : $this->attributeMap->needSend();
		if(count($entries) > 0){
			$pk = new UpdateAttributesPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->entries = $entries;
			$this->sendDataPacket($pk);
			foreach($entries as $entry){
				$entry->markSynchronized();
			}
		}
	}

	public function onUpdate(int $currentTick) : bool{
		$tickDiff = $currentTick - $this->lastUpdate;

		if($tickDiff <= 0){
			return true;
		}

		$this->messageCounter = 2;

		$this->lastUpdate = $currentTick;

		$this->sendAttributes();

		if(!$this->isAlive() and $this->spawned){
			$this->onDeathUpdate($tickDiff);
			return true;
		}

		$this->timings->startTiming();

		if($this->spawned){
			$this->processMovement($tickDiff);
			$this->motion->x = $this->motion->y = $this->motion->z = 0; //TODO: HACK! (Fixes player knockback being messed up)
			if($this->onGround){
				$this->inAirTicks = 0;
			}else{
				$this->inAirTicks += $tickDiff;
			}

			Timings::$timerEntityBaseTick->startTiming();
			$this->entityBaseTick($tickDiff);
			Timings::$timerEntityBaseTick->stopTiming();

			if(!$this->isSpectator() and $this->isAlive()){
				Timings::$playerCheckNearEntitiesTimer->startTiming();
				$this->checkNearEntities();
				Timings::$playerCheckNearEntitiesTimer->stopTiming();
			}
		}

		$this->timings->stopTiming();

		return true;
	}

	protected function doFoodTick(int $tickDiff = 1) : void{
		if($this->isSurvival()){
			parent::doFoodTick($tickDiff);
		}
	}

	public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
		if($this->isSurvival()){
			return parent::exhaust($amount, $cause);
		}

		return 0.0;
	}

	public function isHungry() : bool{
		return $this->isSurvival() and parent::isHungry();
	}

	public function canBreathe() : bool{
		return $this->isCreative() or parent::canBreathe();
	}

	protected function sendEffectAdd(EffectInstance $effect, bool $replacesOldEffect) : void{
		$pk = new MobEffectPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->eventId = $replacesOldEffect ? MobEffectPacket::EVENT_MODIFY : MobEffectPacket::EVENT_ADD;
		$pk->effectId = $effect->getId();
		$pk->amplifier = $effect->getAmplifier();
		$pk->particles = $effect->isVisible();
		$pk->duration = $effect->getDuration();

		$this->sendDataPacket($pk);
	}

	protected function sendEffectRemove(EffectInstance $effect) : void{
		$pk = new MobEffectPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->eventId = MobEffectPacket::EVENT_REMOVE;
		$pk->effectId = $effect->getId();

		$this->sendDataPacket($pk);
	}

	/**
	 * Returns whether the player can interact with the specified position. This checks distance and direction.
	 *
	 * @param Vector3 $pos
	 * @param float   $maxDistance
	 * @param float   $maxDiff defaults to half of the 3D diagonal width of a block
	 *
	 * @return bool
	 */
	public function canInteract(Vector3 $pos, float $maxDistance, float $maxDiff = M_SQRT3 / 2) : bool{
		$eyePos = $this->getPosition()->add(0, $this->getEyeHeight(), 0);
		if($eyePos->distanceSquared($pos) > $maxDistance ** 2){
			return false;
		}

		$dV = $this->getDirectionVector();
		$eyeDot = $dV->dot($eyePos);
		$targetDot = $dV->dot($pos);
		return ($targetDot - $eyeDot) >= -$maxDiff;
	}

	public function handleLogin(LoginPacket $packet) : bool{
		$this->playerInfo = $packet->playerInfo;
		$this->username = TextFormat::clean($this->playerInfo->getUsername());
		$this->displayName = $this->username;
		$this->iusername = strtolower($this->username);
		$this->locale = $this->playerInfo->getLocale();
		$this->randomClientId = $this->playerInfo->getClientId();

		$this->uuid = $this->playerInfo->getUuid();
		$this->rawUUID = $this->uuid->toBinary();
		$this->xuid = $this->playerInfo->getXuid();

		$this->setSkin($this->playerInfo->getSkin());

		$ev = new PlayerPreLoginEvent(
			$this->playerInfo,
			$this->networkSession->getIp(),
			$this->networkSession->getPort(),
			$this->server->requiresAuthentication()
		);
		if($this->server->getNetwork()->getConnectionCount() > $this->server->getMaxPlayers()){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_FULL, "disconnectionScreen.serverFull");
		}
		if(!$this->server->isWhitelisted($this->username)){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_WHITELISTED, "Server is whitelisted");
		}
		if($this->isBanned() or $this->server->getIPBans()->isBanned($this->getAddress())){
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_BANNED, "You are banned");
		}

		$ev->call();
		if(!$ev->isAllowed()){
			$this->close("", $ev->getFinalKickMessage());
			return true;
		}

		if(!$packet->skipVerification){
			$this->server->getAsyncPool()->submitTask(new ProcessLoginTask($this, $packet, $ev->isAuthRequired(), NetworkCipher::$ENABLED));
		}else{
			$this->setAuthenticationStatus(false, false, null);
			$this->networkSession->onLoginSuccess();
		}

		return true;
	}

	public function setAuthenticationStatus(bool $authenticated, bool $authRequired, ?string $error) : bool{
		if($this->networkSession === null){
			return false;
		}

		if($authenticated and $this->xuid === ""){
			$error = "Expected XUID but none found";
		}

		if($error !== null){
			$this->close("", $this->server->getLanguage()->translateString("pocketmine.disconnect.invalidSession", [$error]));

			return false;
		}

		$this->authenticated = $authenticated;

		if(!$this->authenticated){
			if($authRequired){
				$this->close("", "disconnectionScreen.notAuthenticated");
				return false;
			}

			$this->server->getLogger()->debug($this->getName() . " is NOT logged into Xbox Live");
			if($this->xuid !== ""){
				$this->server->getLogger()->warning($this->getName() . " has an XUID, but their login keychain is not signed by Mojang");
				$this->xuid = "";
			}
		}else{
			$this->server->getLogger()->debug($this->getName() . " is logged into Xbox Live");
		}

		return $this->server->getNetwork()->getSessionManager()->kickDuplicates($this->networkSession);
	}

	public function onLoginSuccess() : void{
		$this->loggedIn = true;
	}

	public function _actuallyConstruct(){
		$namedtag = $this->server->getOfflinePlayerData($this->username); //TODO: make this async

		$spawnReset = false;

		if($namedtag !== null and ($level = $this->server->getLevelManager()->getLevelByName($namedtag->getString("Level", "", true))) !== null){
			/** @var float[] $pos */
			$pos = $namedtag->getListTag("Pos")->getAllValues();
			$spawn = new Vector3($pos[0], $pos[1], $pos[2]);
		}else{
			$level = $this->server->getLevelManager()->getDefaultLevel(); //TODO: default level might be null
			$spawn = $level->getSpawnLocation();
			$spawnReset = true;
		}

		//load the spawn chunk so we can see the terrain
		$level->registerChunkLoader($this, $spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4, true);
		$level->registerChunkListener($this, $spawn->getFloorX() >> 4, $spawn->getFloorZ() >> 4);
		if($spawnReset){
			$spawn = $level->getSafeSpawn($spawn);
		}

		if($namedtag === null){
			$namedtag = EntityFactory::createBaseNBT($spawn);

			$namedtag->setByte("OnGround", 1); //TODO: this hack is needed for new players in-air ticks - they don't get detected as on-ground until they move
			//TODO: old code had a TODO for SpawnForced

		}elseif($spawnReset){
			$namedtag->setTag("Pos", new ListTag([
				new DoubleTag($spawn->x),
				new DoubleTag($spawn->y),
				new DoubleTag($spawn->z)
			]));
		}

		parent::__construct($level, $namedtag);
		$ev = new PlayerLoginEvent($this, "Plugin reason");
		$ev->call();
		if($ev->isCancelled()){
			$this->close($this->getLeaveMessage(), $ev->getKickMessage());

			return;
		}

		$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
			TextFormat::AQUA . $this->username . TextFormat::WHITE,
			$this->networkSession->getIp(),
			$this->networkSession->getPort(),
			$this->id,
			$this->level->getDisplayName(),
			round($this->x, 4),
			round($this->y, 4),
			round($this->z, 4)
		]));

		$this->server->addOnlinePlayer($this);
	}

	protected function initHumanData(CompoundTag $nbt) : void{
		$this->setNameTag($this->username);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->addDefaultWindows();

		$this->firstPlayed = $nbt->getLong("firstPlayed", $now = (int) (microtime(true) * 1000));
		$this->lastPlayed = $nbt->getLong("lastPlayed", $now);

		$this->gamemode = $this->server->getForceGamemode() ? $this->server->getGamemode() : $nbt->getInt("playerGameType", $this->server->getGamemode()) & 0x03;

		$this->allowFlight = $this->isCreative();
		$this->keepMovement = $this->isSpectator() || $this->allowMovementCheats();
		if($this->isOp()){
			$this->setRemoveFormat(false);
		}

		$this->setNameTagVisible();
		$this->setNameTagAlwaysVisible();
		$this->setCanClimb();

		$this->achievements = [];
		$achievements = $nbt->getCompoundTag("Achievements");
		if($achievements !== null){
			/** @var ByteTag $tag */
			foreach($achievements as $name => $tag){
				$this->achievements[$name] = $tag->getValue() !== 0;
			}
		}

		if(!$this->hasValidSpawnPosition()){
			if(($level = $this->server->getLevelManager()->getLevelByName($nbt->getString("SpawnLevel", ""))) instanceof Level){
				$this->spawnPosition = new Position($nbt->getInt("SpawnX"), $nbt->getInt("SpawnY"), $nbt->getInt("SpawnZ"), $level);
			}else{
				$this->spawnPosition = $this->level->getSafeSpawn();
			}
		}
	}

	/**
	 * Sends a chat message as this player. If the message begins with a / (forward-slash) it will be treated
	 * as a command.
	 *
	 * @param string $message
	 *
	 * @return bool
	 */
	public function chat(string $message) : bool{
		$this->doCloseInventory();

		$message = TextFormat::clean($message, $this->removeFormat);
		foreach(explode("\n", $message) as $messagePart){
			if(trim($messagePart) !== "" and strlen($messagePart) <= 255 and $this->messageCounter-- > 0){
				if(strpos($messagePart, './') === 0){
					$messagePart = substr($messagePart, 1);
				}

				$ev = new PlayerCommandPreprocessEvent($this, $messagePart);
				$ev->call();

				if($ev->isCancelled()){
					break;
				}

				if(strpos($ev->getMessage(), "/") === 0){
					Timings::$playerCommandTimer->startTiming();
					$this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
					Timings::$playerCommandTimer->stopTiming();
				}else{
					$ev = new PlayerChatEvent($this, $ev->getMessage());
					$ev->call();
					if(!$ev->isCancelled()){
						$this->server->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [$ev->getPlayer()->getDisplayName(), $ev->getMessage()]), $ev->getRecipients());
					}
				}
			}
		}

		return true;
	}

	public function handleMovePlayer(MovePlayerPacket $packet) : bool{
		$newPos = $packet->position->subtract(0, $this->baseOffset, 0);

		if($this->isTeleporting and $newPos->distanceSquared($this) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
			$this->sendPosition($this, null, null, MovePlayerPacket::MODE_RESET);
			$this->server->getLogger()->debug("Got outdated pre-teleport movement from " . $this->getName() . ", received " . $newPos . ", expected " . $this->asVector3());
			//Still getting movements from before teleport, ignore them
		}else{
			// Once we get a movement within a reasonable distance, treat it as a teleport ACK and remove position lock
			if($this->isTeleporting){
				$this->isTeleporting = false;
			}

			$packet->yaw = fmod($packet->yaw, 360);
			$packet->pitch = fmod($packet->pitch, 360);

			if($packet->yaw < 0){
				$packet->yaw += 360;
			}

			$this->setRotation($packet->yaw, $packet->pitch);
			$this->newPosition = $newPos;
		}

		return true;
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool{
		//TODO: add events so plugins can change this
		$this->getLevel()->broadcastPacketToViewers($this, $packet);
		return true;
	}

	public function handleEntityEvent(EntityEventPacket $packet) : bool{
		$this->doCloseInventory();

		switch($packet->event){
			case EntityEventPacket::EATING_ITEM:
				if($packet->data === 0){
					return false;
				}

				$this->sendDataPacket($packet);
				$this->server->broadcastPacket($this->getViewers(), $packet);
				break;
			default:
				return false;
		}

		return true;
	}

	public function equipItem(int $hotbarSlot) : bool{
		if(!$this->inventory->isHotbarSlot($hotbarSlot)){
			$this->inventory->sendContents($this);
			return false;
		}

		$ev = new PlayerItemHeldEvent($this, $this->inventory->getItem($hotbarSlot), $hotbarSlot);
		$ev->call();
		if($ev->isCancelled()){
			$this->inventory->sendHeldItem($this);
			return false;
		}

		$this->inventory->setHeldItemIndex($hotbarSlot, false);
		$this->setUsingItem(false);

		return true;
	}

	/**
	 * Activates the item in hand, for example throwing a projectile.
	 *
	 * @return bool if it did something
	 */
	public function useHeldItem() : bool{
		$directionVector = $this->getDirectionVector();
		$item = $this->inventory->getItemInHand();

		$ev = new PlayerItemUseEvent($this, $item, $directionVector);
		if($this->hasItemCooldown($item)){
			$ev->setCancelled();
		}

		$ev->call();

		if($ev->isCancelled()){
			$this->inventory->sendHeldItem($this);
			return false;
		}

		$result = $item->onClickAir($this, $directionVector);
		if($result === ItemUseResult::SUCCESS()){
			$this->resetItemCooldown($item);
			if($this->isSurvival()){
				$this->inventory->setItemInHand($item);
			}
		}elseif($result === ItemUseResult::FAIL()){
			$this->inventory->sendHeldItem($this);
		}

		//TODO: check if item has a release action - if it doesn't, this shouldn't be set
		$this->setUsingItem(true);

		return true;
	}

	/**
	 * Consumes the currently-held item.
	 *
	 * @return bool
	 */
	public function consumeHeldItem() : bool{
		$slot = $this->inventory->getItemInHand();
		if($slot instanceof Consumable){
			$ev = new PlayerItemConsumeEvent($this, $slot);
			if($this->hasItemCooldown($slot)){
				$ev->setCancelled();
			}
			$ev->call();

			if($ev->isCancelled() or !$this->consumeObject($slot)){
				$this->inventory->sendContents($this);
				return true;
			}

			$this->resetItemCooldown($slot);

			if($this->isSurvival()){
				$slot->pop();
				$this->inventory->setItemInHand($slot);
				$this->inventory->addItem($slot->getResidue());
			}

			return true;
		}

		return false;
	}

	/**
	 * Releases the held item, for example to fire a bow. This should be preceded by a call to useHeldItem().
	 *
	 * @return bool if it did something.
	 */
	public function releaseHeldItem() : bool{
		try{
			if($this->isUsingItem()){
				$item = $this->inventory->getItemInHand();
				if($this->hasItemCooldown($item)){
					$this->inventory->sendContents($this);
					return false;
				}
				$result = $item->onReleaseUsing($this);
				if($result === ItemUseResult::SUCCESS()){
					$this->resetItemCooldown($item);
					$this->inventory->setItemInHand($item);
					return true;
				}
				if($result === ItemUseResult::FAIL()){
					$this->inventory->sendContents($this);
					return true;
				}
			}

			return false;
		}finally{
			$this->setUsingItem(false);
		}
	}

	public function pickBlock(Vector3 $pos, bool $addTileNBT) : bool{
		$block = $this->level->getBlock($pos);
		if($block instanceof UnknownBlock){
			return true;
		}

		$item = $block->getPickedItem();
		if($addTileNBT){
			$tile = $this->getLevel()->getTile($block);
			if($tile instanceof Tile){
				$nbt = $tile->getCleanedNBT();
				if($nbt instanceof CompoundTag){
					$item->setCustomBlockData($nbt);
					$item->setLore(["+(DATA)"]);
				}
			}
		}

		$ev = new PlayerBlockPickEvent($this, $block, $item);
		$ev->call();

		if(!$ev->isCancelled()){
			$existing = $this->inventory->first($item);
			if($existing !== -1){
				if($existing < $this->inventory->getHotbarSize()){
					$this->inventory->setHeldItemIndex($existing);
				}else{
					$this->inventory->swap($this->inventory->getHeldItemIndex(), $existing);
				}
			}elseif($this->isCreative(true)){ //TODO: plugins won't know this isn't going to execute
				$firstEmpty = $this->inventory->firstEmpty();
				if($firstEmpty === -1){ //full inventory
					$this->inventory->setItemInHand($item);
				}elseif($firstEmpty < $this->inventory->getHotbarSize()){
					$this->inventory->setItem($firstEmpty, $item);
					$this->inventory->setHeldItemIndex($firstEmpty);
				}else{
					$this->inventory->swap($this->inventory->getHeldItemIndex(), $firstEmpty);
					$this->inventory->setItemInHand($item);
				}
			}
		}

		return true;
	}

	/**
	 * Performs a left-click (attack) action on the block.
	 *
	 * @param Vector3 $pos
	 * @param int     $face
	 *
	 * @return bool
	 */
	public function attackBlock(Vector3 $pos, int $face) : bool{
		if($pos->distanceSquared($this) > 10000){
			return false; //TODO: maybe this should throw an exception instead?
		}

		$target = $this->level->getBlock($pos);

		$ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, null, $face, PlayerInteractEvent::LEFT_CLICK_BLOCK);
		$ev->call();
		if($ev->isCancelled()){
			$this->level->sendBlocks([$this], [$target]);
			$this->inventory->sendHeldItem($this);
			return true;
		}
		if($target->onAttack($this->inventory->getItemInHand(), $face, $this)){
			return true;
		}

		$block = $target->getSide($face);
		if($block->getId() === Block::FIRE){
			$this->level->setBlock($block, BlockFactory::get(Block::AIR));
			return true;
		}

		if(!$this->isCreative()){
			//TODO: improve this to take stuff like swimming, ladders, enchanted tools into account, fix wrong tool break time calculations for bad tools (pmmp/PocketMine-MP#211)
			$breakTime = ceil($target->getBreakTime($this->inventory->getItemInHand()) * 20);
			if($breakTime > 0){
				$this->level->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_START_BREAK, (int) (65535 / $breakTime));
			}
		}

		return true;
	}

	public function continueBreakBlock(Vector3 $pos, int $face) : void{
		$block = $this->level->getBlock($pos);
		$this->level->broadcastLevelEvent(
			$pos,
			LevelEventPacket::EVENT_PARTICLE_PUNCH_BLOCK,
			$block->getRuntimeId() | ($face << 24)
		);

		//TODO: destroy-progress level event
	}

	public function stopBreakBlock(Vector3 $pos) : void{
		$this->level->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_STOP_BREAK);
	}

	/**
	 * Breaks the block at the given position using the currently-held item.
	 *
	 * @param Vector3 $pos
	 *
	 * @return bool if the block was successfully broken.
	 */
	public function breakBlock(Vector3 $pos) : bool{
		$this->doCloseInventory();

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), $this->isCreative() ? 13 : 7) and !$this->isSpectator()){
			$item = $this->inventory->getItemInHand();
			$oldItem = clone $item;
			if($this->level->useBreakOn($pos, $item, $this, true)){
				if($this->isSurvival()){
					if(!$item->equalsExact($oldItem)){
						$this->inventory->setItemInHand($item);
					}
					$this->exhaust(0.025, PlayerExhaustEvent::CAUSE_MINING);
				}
				return true;
			}
		}

		$this->inventory->sendContents($this);
		$this->inventory->sendHeldItem($this);

		$target = $this->level->getBlock($pos);
		/** @var Block[] $blocks */
		$blocks = $target->getAllSides();
		$blocks[] = $target;

		$this->level->sendBlocks([$this], $blocks);

		return false;
	}

	/**
	 * Touches the block at the given position with the currently-held item.
	 *
	 * @param Vector3 $pos
	 * @param int     $face
	 * @param Vector3 $clickOffset
	 *
	 * @return bool if it did something
	 */
	public function interactBlock(Vector3 $pos, int $face, Vector3 $clickOffset) : bool{
		$this->setUsingItem(false);

		if($this->canInteract($pos->add(0.5, 0.5, 0.5), 13) and !$this->isSpectator()){
			$item = $this->inventory->getItemInHand(); //this is a copy of the real item
			$oldItem = clone $item;
			if($this->level->useItemOn($pos, $item, $face, $clickOffset, $this, true)){
				if($this->isSurvival() and !$item->equalsExact($oldItem)){
					$this->inventory->setItemInHand($item);
				}
				return true;
			}
		}

		$this->inventory->sendHeldItem($this);

		if($pos->distanceSquared($this) > 10000){
			return true;
		}

		$target = $this->level->getBlock($pos);
		$block = $target->getSide($face);

		/** @var Block[] $blocks */
		$blocks = array_merge($target->getAllSides(), $block->getAllSides()); //getAllSides() on each of these will include $target and $block because they are next to each other

		$this->level->sendBlocks([$this], $blocks);

		return false;
	}

	/**
	 * Attacks the given entity with the currently-held item.
	 * TODO: move this up the class hierarchy
	 *
	 * @param Entity $entity
	 *
	 * @return bool if the entity was dealt damage
	 */
	public function attackEntity(Entity $entity) : bool{
		if(!$entity->isAlive()){
			return false;
		}
		if($entity instanceof ItemEntity or $entity instanceof Arrow){
			$this->kick("Attempting to attack an invalid entity");
			$this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidEntity", [$this->getName()]));
			return false;
		}

		$heldItem = $this->inventory->getItemInHand();

		$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());
		if(!$this->canInteract($entity, 8) or ($entity instanceof Player and !$this->server->getConfigBool("pvp"))){
			$ev->setCancelled();
		}

		$meleeEnchantmentDamage = 0;
		/** @var EnchantmentInstance[] $meleeEnchantments */
		$meleeEnchantments = [];
		foreach($heldItem->getEnchantments() as $enchantment){
			$type = $enchantment->getType();
			if($type instanceof MeleeWeaponEnchantment and $type->isApplicableTo($entity)){
				$meleeEnchantmentDamage += $type->getDamageBonus($enchantment->getLevel());
				$meleeEnchantments[] = $enchantment;
			}
		}
		$ev->setModifier($meleeEnchantmentDamage, EntityDamageEvent::MODIFIER_WEAPON_ENCHANTMENTS);

		if(!$this->isSprinting() and !$this->isFlying() and $this->fallDistance > 0 and !$this->hasEffect(Effect::BLINDNESS()) and !$this->isUnderwater()){
			$ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
		}

		$entity->attack($ev);

		if($ev->isCancelled()){
			if($heldItem instanceof Durable and $this->isSurvival()){
				$this->inventory->sendContents($this);
			}
			return false;
		}

		if($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0){
			$entity->broadcastAnimation(null, AnimatePacket::ACTION_CRITICAL_HIT);
		}

		foreach($meleeEnchantments as $enchantment){
			$type = $enchantment->getType();
			assert($type instanceof MeleeWeaponEnchantment);
			$type->onPostAttack($this, $entity, $enchantment->getLevel());
		}

		if($this->isAlive()){
			//reactive damage like thorns might cause us to be killed by attacking another mob, which
			//would mean we'd already have dropped the inventory by the time we reached here
			if($heldItem->onAttackEntity($entity) and $this->isSurvival()){ //always fire the hook, even if we are survival
				$this->inventory->setItemInHand($heldItem);
			}

			$this->exhaust(0.3, PlayerExhaustEvent::CAUSE_ATTACK);
		}

		return true;
	}

	/**
	 * Interacts with the given entity using the currently-held item.
	 *
	 * @param Entity  $entity
	 * @param Vector3 $clickPos
	 *
	 * @return bool
	 */
	public function interactEntity(Entity $entity, Vector3 $clickPos) : bool{
		//TODO
		return false;
	}

	public function toggleSprint(bool $sprint) : void{
		$ev = new PlayerToggleSprintEvent($this, $sprint);
		$ev->call();
		if($ev->isCancelled()){
			$this->sendData($this);
		}else{
			$this->setSprinting($sprint);
		}
	}

	public function toggleSneak(bool $sneak) : void{
		$ev = new PlayerToggleSneakEvent($this, $sneak);
		$ev->call();
		if($ev->isCancelled()){
			$this->sendData($this);
		}else{
			$this->setSneaking($sneak);
		}
	}

	public function toggleFlight(bool $fly) : void{
		$ev = new PlayerToggleFlightEvent($this, $fly);
		$ev->call();
		if($ev->isCancelled()){
			$this->sendSettings();
		}else{ //don't use setFlying() here, to avoid feedback loops
			$this->flying = $fly;
			$this->resetFallDistance();
		}
	}

	public function animate(int $action) : bool{
		$ev = new PlayerAnimationEvent($this, $action);
		$ev->call();
		if($ev->isCancelled()){
			return true;
		}

		$this->broadcastAnimation($this->getViewers(), $ev->getAnimationType());
		return true;
	}

	/**
	 * Drops an item on the ground in front of the player.
	 *
	 * @param Item $item
	 */
	public function dropItem(Item $item) : void{
		$this->level->dropItem($this->add(0, 1.3, 0), $item, $this->getDirectionVector()->multiply(0.4), 40);
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool{
		if($packet->entityUniqueId !== $this->getId()){
			return false; //TODO
		}

		$handled = false;

		$isFlying = $packet->getFlag(AdventureSettingsPacket::FLYING);
		if($isFlying and !$this->allowFlight){
			$this->kick($this->server->getLanguage()->translateString("kick.reason.cheat", ["%ability.flight"]));
			return true;
		}elseif($isFlying !== $this->isFlying()){
			$this->toggleFlight($isFlying);
			$handled = true;
		}

		if($packet->getFlag(AdventureSettingsPacket::NO_CLIP) and !$this->allowMovementCheats and !$this->isSpectator()){
			$this->kick($this->server->getLanguage()->translateString("kick.reason.cheat", ["%ability.noclip"]));
			return true;
		}

		//TODO: check other changes

		return $handled;
	}

	public function handleBookEdit(BookEditPacket $packet) : bool{
		/** @var WritableBook $oldBook */
		$oldBook = $this->inventory->getItem($packet->inventorySlot);
		if($oldBook->getId() !== Item::WRITABLE_BOOK){
			return false;
		}

		$newBook = clone $oldBook;
		$modifiedPages = [];

		switch($packet->type){
			case BookEditPacket::TYPE_REPLACE_PAGE:
				$newBook->setPageText($packet->pageNumber, $packet->text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_ADD_PAGE:
				$newBook->insertPage($packet->pageNumber, $packet->text);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_DELETE_PAGE:
				$newBook->deletePage($packet->pageNumber);
				$modifiedPages[] = $packet->pageNumber;
				break;
			case BookEditPacket::TYPE_SWAP_PAGES:
				$newBook->swapPages($packet->pageNumber, $packet->secondaryPageNumber);
				$modifiedPages = [$packet->pageNumber, $packet->secondaryPageNumber];
				break;
			case BookEditPacket::TYPE_SIGN_BOOK:
				/** @var WrittenBook $newBook */
				$newBook = Item::get(Item::WRITTEN_BOOK, 0, 1, $newBook->getNamedTag());
				$newBook->setAuthor($packet->author);
				$newBook->setTitle($packet->title);
				$newBook->setGeneration(WrittenBook::GENERATION_ORIGINAL);
				break;
			default:
				return false;
		}

		$event = new PlayerEditBookEvent($this, $oldBook, $newBook, $packet->type, $modifiedPages);
		$event->call();
		if($event->isCancelled()){
			return true;
		}

		$this->getInventory()->setItem($packet->inventorySlot, $event->getNewBook());

		return true;
	}

	/**
	 * @param ClientboundPacket $packet
	 * @param bool              $immediate
	 *
	 * @return bool
	 */
	public function sendDataPacket(ClientboundPacket $packet, bool $immediate = false) : bool{
		if(!$this->isConnected()){
			return false;
		}

		return $this->networkSession->sendDataPacket($packet, $immediate);
	}

	/**
	 * @deprecated This is a proxy for sendDataPacket() and will be removed in the next major release.
	 * @see Player::sendDataPacket()
	 *
	 * @param ClientboundPacket $packet
	 *
	 * @return bool
	 */
	public function dataPacket(ClientboundPacket $packet) : bool{
		return $this->sendDataPacket($packet, false);
	}

	/**
	 * Transfers a player to another server.
	 *
	 * @param string $address The IP address or hostname of the destination server
	 * @param int    $port The destination port, defaults to 19132
	 * @param string $message Message to show in the console when closing the player
	 *
	 * @return bool if transfer was successful.
	 */
	public function transfer(string $address, int $port = 19132, string $message = "transfer") : bool{
		$ev = new PlayerTransferEvent($this, $address, $port, $message);
		$ev->call();
		if(!$ev->isCancelled()){
			$pk = new TransferPacket();
			$pk->address = $ev->getAddress();
			$pk->port = $ev->getPort();
			$this->sendDataPacket($pk, true);
			$this->close("", $ev->getMessage(), false);

			return true;
		}

		return false;
	}

	/**
	 * Kicks a player from the server
	 *
	 * @param string               $reason
	 * @param bool                 $isAdmin
	 * @param TextContainer|string $quitMessage
	 *
	 * @return bool
	 */
	public function kick(string $reason = "", bool $isAdmin = true, $quitMessage = null) : bool{
		$ev = new PlayerKickEvent($this, $reason, $quitMessage ?? $this->getLeaveMessage());
		$ev->call();
		if(!$ev->isCancelled()){
			$reason = $ev->getReason();
			$message = $reason;
			if($isAdmin){
				if(!$this->isBanned()){
					$message = "Kicked by admin." . ($reason !== "" ? " Reason: " . $reason : "");
				}
			}else{
				if($reason === ""){
					$message = "disconnectionScreen.noReason";
				}
			}
			$this->close($ev->getQuitMessage(), $message);

			return true;
		}

		return false;
	}

	/**
	 * Adds a title text to the user's screen, with an optional subtitle.
	 *
	 * @param string $title
	 * @param string $subtitle
	 * @param int    $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int    $stay Duration in ticks to stay on screen for
	 * @param int    $fadeOut Duration in ticks for fade-out.
	 */
	public function addTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1){
		$this->setTitleDuration($fadeIn, $stay, $fadeOut);
		if($subtitle !== ""){
			$this->addSubTitle($subtitle);
		}
		$this->sendTitleText($title, SetTitlePacket::TYPE_SET_TITLE);
	}

	/**
	 * Sets the subtitle message, without sending a title.
	 *
	 * @param string $subtitle
	 */
	public function addSubTitle(string $subtitle){
		$this->sendTitleText($subtitle, SetTitlePacket::TYPE_SET_SUBTITLE);
	}

	/**
	 * Adds small text to the user's screen.
	 *
	 * @param string $message
	 */
	public function addActionBarMessage(string $message){
		$this->sendTitleText($message, SetTitlePacket::TYPE_SET_ACTIONBAR_MESSAGE);
	}

	/**
	 * Removes the title from the client's screen.
	 */
	public function removeTitles(){
		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TYPE_CLEAR_TITLE;
		$this->sendDataPacket($pk);
	}

	/**
	 * Resets the title duration settings.
	 */
	public function resetTitles(){
		$pk = new SetTitlePacket();
		$pk->type = SetTitlePacket::TYPE_RESET_TITLE;
		$this->sendDataPacket($pk);
	}

	/**
	 * Sets the title duration.
	 *
	 * @param int $fadeIn Title fade-in time in ticks.
	 * @param int $stay Title stay time in ticks.
	 * @param int $fadeOut Title fade-out time in ticks.
	 */
	public function setTitleDuration(int $fadeIn, int $stay, int $fadeOut){
		if($fadeIn >= 0 and $stay >= 0 and $fadeOut >= 0){
			$pk = new SetTitlePacket();
			$pk->type = SetTitlePacket::TYPE_SET_ANIMATION_TIMES;
			$pk->fadeInTime = $fadeIn;
			$pk->stayTime = $stay;
			$pk->fadeOutTime = $fadeOut;
			$this->sendDataPacket($pk);
		}
	}

	/**
	 * Internal function used for sending titles.
	 *
	 * @param string $title
	 * @param int    $type
	 */
	protected function sendTitleText(string $title, int $type){
		$pk = new SetTitlePacket();
		$pk->type = $type;
		$pk->text = $title;
		$this->sendDataPacket($pk);
	}

	/**
	 * Sends a direct chat message to a player
	 *
	 * @param TextContainer|string $message
	 */
	public function sendMessage($message) : void{
		if($message instanceof TextContainer){
			if($message instanceof TranslationContainer){
				$this->sendTranslation($message->getText(), $message->getParameters());
				return;
			}
			$message = $message->getText();
		}

		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_RAW;
		$pk->message = $this->server->getLanguage()->translateString($message);
		$this->sendDataPacket($pk);
	}

	/**
	 * @param string   $message
	 * @param string[] $parameters
	 */
	public function sendTranslation(string $message, array $parameters = []){
		$pk = new TextPacket();
		if(!$this->server->isLanguageForced()){
			$pk->type = TextPacket::TYPE_TRANSLATION;
			$pk->needsTranslation = true;
			$pk->message = $this->server->getLanguage()->translateString($message, $parameters, "pocketmine.");
			foreach($parameters as $i => $p){
				$parameters[$i] = $this->server->getLanguage()->translateString($p, $parameters, "pocketmine.");
			}
			$pk->parameters = $parameters;
		}else{
			$pk->type = TextPacket::TYPE_RAW;
			$pk->message = $this->server->getLanguage()->translateString($message, $parameters);
		}
		$this->sendDataPacket($pk);
	}

	/**
	 * Sends a popup message to the player
	 *
	 * TODO: add translation type popups
	 *
	 * @param string $message
	 * @param string $subtitle @deprecated
	 */
	public function sendPopup(string $message, string $subtitle = ""){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_POPUP;
		$pk->message = $message;
		$this->sendDataPacket($pk);
	}

	public function sendTip(string $message){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_TIP;
		$pk->message = $message;
		$this->sendDataPacket($pk);
	}

	/**
	 * @param string $sender
	 * @param string $message
	 */
	public function sendWhisper(string $sender, string $message){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_WHISPER;
		$pk->sourceName = $sender;
		$pk->message = $message;
		$this->sendDataPacket($pk);
	}

	/**
	 * Sends a Form to the player, or queue to send it if a form is already open.
	 *
	 * @param Form $form
	 */
	public function sendForm(Form $form) : void{
		$id = $this->formIdCounter++;
		$pk = new ModalFormRequestPacket();
		$pk->formId = $id;
		$pk->formData = json_encode($form);
		if($pk->formData === false){
			throw new \InvalidArgumentException("Failed to encode form JSON: " . json_last_error_msg());
		}
		if($this->sendDataPacket($pk)){
			$this->forms[$id] = $form;
		}
	}

	/**
	 * @param int   $formId
	 * @param mixed $responseData
	 *
	 * @return bool
	 */
	public function onFormSubmit(int $formId, $responseData) : bool{
		if(!isset($this->forms[$formId])){
			$this->server->getLogger()->debug("Got unexpected response for form $formId");
			return false;
		}

		try{
			$this->forms[$formId]->handleResponse($this, $responseData);
		}catch(FormValidationException $e){
			$this->server->getLogger()->critical("Failed to validate form " . get_class($this->forms[$formId]) . ": " . $e->getMessage());
			$this->server->getLogger()->logException($e);
		}finally{
			unset($this->forms[$formId]);
		}

		return true;
	}

	/**
	 * Note for plugin developers: use kick() with the isAdmin
	 * flag set to kick without the "Kicked by admin" part instead of this method.
	 *
	 * @param TextContainer|string $message Message to be broadcasted
	 * @param string               $reason Reason showed in console
	 * @param bool                 $notify
	 */
	final public function close($message = "", string $reason = "generic reason", bool $notify = true) : void{
		if($this->isConnected() and !$this->closed){
			$ip = $this->networkSession->getIp();
			$port = $this->networkSession->getPort();
			$this->networkSession->onPlayerDestroyed($reason, $notify);
			$this->networkSession = null;

			PermissionManager::getInstance()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			PermissionManager::getInstance()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

			$this->stopSleep();

			if($this->spawned){
				$ev = new PlayerQuitEvent($this, $message, $reason);
				$ev->call();
				if($ev->getQuitMessage() != ""){
					$this->server->broadcastMessage($ev->getQuitMessage());
				}

				$this->save();
			}

			if($this->isValid()){
				foreach($this->usedChunks as $index => $d){
					Level::getXZ($index, $chunkX, $chunkZ);
					$this->level->unregisterChunkLoader($this, $chunkX, $chunkZ);
					$this->level->unregisterChunkListener($this, $chunkX, $chunkZ);
					foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
						$entity->despawnFrom($this);
					}
					unset($this->usedChunks[$index]);
				}
			}
			$this->usedChunks = [];
			$this->loadQueue = [];

			if($this->loggedIn){
				foreach($this->server->getOnlinePlayers() as $player){
					if(!$player->canSee($this)){
						$player->showPlayer($this);
					}
				}
				$this->hiddenPlayers = [];
			}

			$this->removeAllWindows(true);
			$this->windows = [];
			$this->windowIndex = [];
			$this->cursorInventory = null;
			$this->craftingGrid = null;

			if($this->constructed){
				parent::close();
			}else{
				$this->closed = true;
			}
			$this->spawned = false;

			if($this->loggedIn){
				$this->loggedIn = false;
				$this->server->removeOnlinePlayer($this);
			}

			$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logOut", [
				TextFormat::AQUA . $this->getName() . TextFormat::WHITE,
				$ip,
				$port,
				$this->getServer()->getLanguage()->translateString($reason)
			]));

			$this->spawnPosition = null;

			if($this->perm !== null){
				$this->perm->clearPermissions();
				$this->perm = null;
			}
		}
	}

	public function __debugInfo(){
		return [];
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public function setCanSaveWithChunk(bool $value) : void{
		throw new \BadMethodCallException("Players can't be saved with chunks");
	}

	/**
	 * Handles player data saving
	 *
	 * @throws \InvalidStateException if the player is closed
	 */
	public function save(){
		if($this->closed){
			throw new \InvalidStateException("Tried to save closed player");
		}

		$nbt = $this->saveNBT();

		if($this->isValid()){
			$nbt->setString("Level", $this->level->getFolderName());
		}

		if($this->hasValidSpawnPosition()){
			$nbt->setString("SpawnLevel", $this->spawnPosition->getLevel()->getFolderName());
			$nbt->setInt("SpawnX", $this->spawnPosition->getFloorX());
			$nbt->setInt("SpawnY", $this->spawnPosition->getFloorY());
			$nbt->setInt("SpawnZ", $this->spawnPosition->getFloorZ());

			if(!$this->isAlive()){
				//hack for respawn after quit
				$nbt->setTag("Pos", new ListTag([
					new DoubleTag($this->spawnPosition->x),
					new DoubleTag($this->spawnPosition->y),
					new DoubleTag($this->spawnPosition->z)
				]));
			}
		}

		$achievements = new CompoundTag();
		foreach($this->achievements as $achievement => $status){
			$achievements->setByte($achievement, $status ? 1 : 0);
		}
		$nbt->setTag("Achievements", $achievements);

		$nbt->setInt("playerGameType", $this->gamemode);
		$nbt->setLong("firstPlayed", $this->firstPlayed);
		$nbt->setLong("lastPlayed", (int) floor(microtime(true) * 1000));

		$this->server->saveOfflinePlayerData($this->username, $nbt);
	}

	protected function onDeath() : void{
		if(!$this->spawned){ //TODO: drop this hack
			return;
		}
		//Crafting grid must always be evacuated even if keep-inventory is true. This dumps the contents into the
		//main inventory and drops the rest on the ground.
		$this->doCloseInventory();

		$ev = new PlayerDeathEvent($this, $this->getDrops(), $this->getXpDropAmount());
		$ev->call();

		if(!$ev->getKeepInventory()){
			foreach($ev->getDrops() as $item){
				$this->level->dropItem($this, $item);
			}

			if($this->inventory !== null){
				$this->inventory->setHeldItemIndex(0);
				$this->inventory->clearAll();
			}
			if($this->armorInventory !== null){
				$this->armorInventory->clearAll();
			}
		}

		$this->level->dropExperience($this, $ev->getXpDropAmount());
		$this->setXpAndProgress(0, 0);

		if($ev->getDeathMessage() != ""){
			$this->server->broadcastMessage($ev->getDeathMessage());
		}

		$this->startDeathAnimation();

		$this->networkSession->onDeath();
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		parent::onDeathUpdate($tickDiff);
		return false; //never flag players for despawn
	}

	public function respawn() : void{
		if($this->server->isHardcore()){
			$this->setBanned(true);
			return;
		}

		$ev = new PlayerRespawnEvent($this, $this->getSpawn());
		$ev->call();

		$realSpawn = Position::fromObject($ev->getRespawnPosition()->add(0.5, 0, 0.5), $ev->getRespawnPosition()->getLevel());
		$this->teleport($realSpawn);

		$this->setSprinting(false);
		$this->setSneaking(false);

		$this->extinguish();
		$this->setAirSupplyTicks($this->getMaxAirSupplyTicks());
		$this->deadTicks = 0;
		$this->noDamageTicks = 60;

		$this->removeAllEffects();
		$this->setHealth($this->getMaxHealth());

		foreach($this->attributeMap->getAll() as $attr){
			$attr->resetToDefault();
		}

		$this->sendData($this);
		$this->sendData($this->getViewers());

		$this->sendSettings();
		$this->sendAllInventories();

		$this->spawnToAll();
		$this->scheduleUpdate();

		$this->networkSession->onRespawn();
	}

	protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
		parent::applyPostDamageEffects($source);

		$this->exhaust(0.3, PlayerExhaustEvent::CAUSE_DAMAGE);
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$this->isAlive()){
			return;
		}

		if($this->isCreative()
			and $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
			and $source->getCause() !== EntityDamageEvent::CAUSE_VOID
		){
			$source->setCancelled();
		}elseif($this->allowFlight and $source->getCause() === EntityDamageEvent::CAUSE_FALL){
			$source->setCancelled();
		}

		parent::attack($source);
	}

	public function broadcastEntityEvent(int $eventId, ?int $eventData = null, ?array $players = null) : void{
		if($this->spawned and $players === null){
			$players = $this->getViewers();
			$players[] = $this;
		}
		parent::broadcastEntityEvent($eventId, $eventData, $players);
	}

	public function broadcastAnimation(?array $players, int $animationId) : void{
		if($this->spawned and $players === null){
			$players = $this->getViewers();
			$players[] = $this;
		}
		parent::broadcastAnimation($players, $animationId);
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		$result = parent::getOffsetPosition($vector3);
		$result->y += 0.001; //Hack for MCPE falling underground for no good reason (TODO: find out why it's doing this)
		return $result;
	}

	public function sendPosition(Vector3 $pos, ?float $yaw = null, ?float $pitch = null, int $mode = MovePlayerPacket::MODE_NORMAL, ?array $targets = null){
		$yaw = $yaw ?? $this->yaw;
		$pitch = $pitch ?? $this->pitch;

		$pk = new MovePlayerPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->getOffsetPosition($pos);
		$pk->pitch = $pitch;
		$pk->headYaw = $yaw;
		$pk->yaw = $yaw;
		$pk->mode = $mode;

		if($targets !== null){
			$this->server->broadcastPacket($targets, $pk);
		}else{
			$this->sendDataPacket($pk);
		}

		$this->newPosition = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if(parent::teleport($pos, $yaw, $pitch)){

			$this->removeAllWindows();

			$this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_TELEPORT);
			$this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_TELEPORT, $this->getViewers());

			$this->spawnToAll();

			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			$this->newPosition = null;
			$this->stopSleep();

			$this->isTeleporting = true;

			//TODO: workaround for player last pos not getting updated
			//Entity::updateMovement() normally handles this, but it's overridden with an empty function in Player
			$this->resetLastMovements();

			return true;
		}

		return false;
	}

	protected function addDefaultWindows(){
		$this->addWindow($this->getInventory(), ContainerIds::INVENTORY, true);

		$this->addWindow($this->getArmorInventory(), ContainerIds::ARMOR, true);

		$this->cursorInventory = new PlayerCursorInventory($this);
		$this->addWindow($this->cursorInventory, ContainerIds::CURSOR, true);

		$this->craftingGrid = new CraftingGrid($this, CraftingGrid::SIZE_SMALL);

		//TODO: more windows
	}

	public function getCursorInventory() : PlayerCursorInventory{
		return $this->cursorInventory;
	}

	public function getCraftingGrid() : CraftingGrid{
		return $this->craftingGrid;
	}

	/**
	 * @param CraftingGrid $grid
	 */
	public function setCraftingGrid(CraftingGrid $grid) : void{
		$this->craftingGrid = $grid;
	}

	/**
	 * @internal Called to clean up crafting grid and cursor inventory when it is detected that the player closed their
	 * inventory.
	 */
	public function doCloseInventory() : void{
		/** @var Inventory[] $inventories */
		$inventories = [$this->craftingGrid, $this->cursorInventory];
		foreach($inventories as $inventory){
			$contents = $inventory->getContents();
			if(count($contents) > 0){
				$drops = $this->inventory->addItem(...$contents);
				foreach($drops as $drop){
					$this->dropItem($drop);
				}

				$inventory->clearAll();
			}
		}

		if($this->craftingGrid->getGridWidth() > CraftingGrid::SIZE_SMALL){
			$this->craftingGrid = new CraftingGrid($this, CraftingGrid::SIZE_SMALL);
		}
	}

	/**
	 * @internal Called by the network session when a player closes a window.
	 *
	 * @param int $windowId
	 *
	 * @return bool
	 */
	public function doCloseWindow(int $windowId) : bool{
		if($windowId === 0){
			return false;
		}

		$this->doCloseInventory();

		if(isset($this->windowIndex[$windowId])){
			(new InventoryCloseEvent($this->windowIndex[$windowId], $this))->call();
			$this->removeWindow($this->windowIndex[$windowId]);
			return true;
		}
		if($windowId === 255){
			//Closed a fake window
			return true;
		}

		return false;
	}

	/**
	 * Returns the window ID which the inventory has for this player, or -1 if the window is not open to the player.
	 *
	 * @param Inventory $inventory
	 *
	 * @return int
	 */
	public function getWindowId(Inventory $inventory) : int{
		return $this->windows[spl_object_id($inventory)] ?? ContainerIds::NONE;
	}

	/**
	 * Returns the inventory window open to the player with the specified window ID, or null if no window is open with
	 * that ID.
	 *
	 * @param int $windowId
	 *
	 * @return Inventory|null
	 */
	public function getWindow(int $windowId) : ?Inventory{
		return $this->windowIndex[$windowId] ?? null;
	}

	/**
	 * Opens an inventory window to the player. Returns the ID of the created window, or the existing window ID if the
	 * player is already viewing the specified inventory.
	 *
	 * @param Inventory $inventory
	 * @param int|null  $forceId Forces a special ID for the window
	 * @param bool      $isPermanent Prevents the window being removed if true.
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException if a forceID which is already in use is specified
	 * @throws \InvalidStateException if trying to add a window without forceID when no slots are free
	 */
	public function addWindow(Inventory $inventory, ?int $forceId = null, bool $isPermanent = false) : int{
		if(($id = $this->getWindowId($inventory)) !== ContainerIds::NONE){
			return $id;
		}

		if($forceId === null){
			$cnt = $this->windowCnt;
			do{
				$cnt = max(ContainerIds::FIRST, ($cnt + 1) % ContainerIds::LAST);
				if($cnt === $this->windowCnt){ //wraparound, no free slots
					throw new \InvalidStateException("No free window IDs found");
				}
			}while(isset($this->windowIndex[$cnt]));
			$this->windowCnt = $cnt;
		}else{
			$cnt = $forceId;
			if(isset($this->windowIndex[$cnt])){
				throw new \InvalidArgumentException("Requested force ID $forceId already in use");
			}
		}

		$this->windowIndex[$cnt] = $inventory;
		$this->windows[spl_object_id($inventory)] = $cnt;
		if($inventory->open($this)){
			if($isPermanent){
				$this->permanentWindows[$cnt] = true;
			}
			return $cnt;
		}else{
			$this->removeWindow($inventory);

			return -1;
		}
	}

	/**
	 * Removes an inventory window from the player.
	 *
	 * @param Inventory $inventory
	 * @param bool      $force Forces removal of permanent windows such as normal inventory, cursor
	 *
	 * @throws \InvalidArgumentException if trying to remove a fixed inventory window without the `force` parameter as true
	 */
	public function removeWindow(Inventory $inventory, bool $force = false){
		$id = $this->windows[$hash = spl_object_id($inventory)] ?? null;

		if($id !== null and !$force and isset($this->permanentWindows[$id])){
			throw new \InvalidArgumentException("Cannot remove fixed window $id (" . get_class($inventory) . ") from " . $this->getName());
		}

		if($id !== null){
			$inventory->close($this);
			unset($this->windows[$hash], $this->windowIndex[$id], $this->permanentWindows[$id]);
		}
	}

	/**
	 * Removes all inventory windows from the player. By default this WILL NOT remove permanent windows.
	 *
	 * @param bool $removePermanentWindows Whether to remove permanent windows.
	 */
	public function removeAllWindows(bool $removePermanentWindows = false){
		foreach($this->windowIndex as $id => $window){
			if(!$removePermanentWindows and isset($this->permanentWindows[$id])){
				continue;
			}

			$this->removeWindow($window, $removePermanentWindows);
		}
	}

	public function sendAllInventories(){
		foreach($this->windowIndex as $id => $inventory){
			$inventory->sendContents($this);
		}
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue) : void{
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin) : void{
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
	}

	public function onChunkChanged(Chunk $chunk) : void{
		if(isset($this->usedChunks[$hash = Level::chunkHash($chunk->getX(), $chunk->getZ())])){
			$this->usedChunks[$hash] = false;
			$this->nextChunkOrderRun = 0;
		}
	}

	public function onChunkLoaded(Chunk $chunk) : void{

	}

	public function onChunkPopulated(Chunk $chunk) : void{

	}

	public function onChunkUnloaded(Chunk $chunk) : void{

	}

	public function onBlockChanged(Vector3 $block) : void{

	}
}
