<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/4/2019
 * Time: 12:02 PM
 */
declare(strict_types=1);
namespace ARTulloss\Protector\Events;

use ARTulloss\Groups\Events\RegisteredPlayerJoinEvent;
use ARTulloss\Protector\Constants\Constants;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerJoinEvent;

use ARTulloss\Groups\Groups;
use ARTulloss\Protector\Protector;
use ARTulloss\Protector\Task\VPNCheck;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use zyware\AnyVersion\Packets\v12\AnimatePacket;
use zyware\AnyVersion\Packets\v12\LevelSoundEventPacket;

/**
 * Class Listener
 * @package ARTulloss\Protector\Events
 */
class Listener implements PMListener
{
	/** @var Protector $protector */
	private $protector;
	/** @var string[] $noServerLag */
//	private $noServerLag = [];
	/** @var array $oldTick */
//	private $oldTick;

	public const MAX_PACKET_KICK = 300;
	public const MAX_PACKET_BAN = 500;
	public const MAX_PACKET_BAN_PERM = 800;

	/**
	 * Listener constructor.
	 * @param Protector $protector
	 */
	public function __construct(Protector $protector)
	{
		$this->protector = $protector;
	}

	/**
	 * @param DataPacketReceiveEvent $event
	 */
	public function onDataPacket(DataPacketReceiveEvent $event): void
	{
		$packet = $event->getPacket();

		if($packet instanceof LoginPacket) {

			$model = $packet->clientData['DeviceModel'];

			if(!isset($this->protector->devices[$model])) {
				$this->protector->devices[$model] = [];
				$this->protector->devices[$model][] = $packet->username;
			} elseif(!in_array($packet->username, $this->protector->devices[$model]))
				$this->protector->devices[$model][] = $packet->username;

			$this->protector->getDeviceOS()->setDeviceOS($packet->username, $packet->clientData['DeviceOS'] ?? null);
		}
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority HIGHEST
	 */
	public function onJoin(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();

		$ip = $player->getAddress();

		$name = $player->getName();

		if(isset($this->protector->vpnData[$ip])) {

			$hasVPN = in_array($this->protector->vpnData[$ip], Constants::BLOCK);

			if($hasVPN)
				$player->kick(Constants::VPN_MESSAGE, false);

		} else {

			$pool = $this->protector->getServer()->getAsyncPool();

			if(Groups::getInstance()->playerHandler->isInHandler($player->getName()))
				$pool->submitTask(new VPNCheck($this->protector->config['Blocked-ASNs'], $name, $ip, $this->protector->getRandomKey(), true));
			else
				$pool->submitTask(new VPNCheck($this->protector->config['Blocked-ASNs'], $name, $ip, $this->protector->getRandomKey(), false));

		}
	}

	public function onRegisteredJoin(RegisteredPlayerJoinEvent $event): void
	{
		// Alias registration

		$player = $event->getPlayer();

		$ip = $player->getAddress();

		$cid = $player->getClientID();

		$name = $player->getName();

		$storedIPs = (array) $this->protector->multi_array_search($name, (array) $this->protector->ips);

		$storedCIDs = (array) $this->protector->multi_array_search($name, (array) $this->protector->cids);

		if(!in_array($ip, $storedIPs)) {
			$this->protector->ips[$ip][] = $name;
		}

		if(!in_array($cid, $storedCIDs)) {
			$this->protector->cids[$cid][] = $name;
		}
	}

}