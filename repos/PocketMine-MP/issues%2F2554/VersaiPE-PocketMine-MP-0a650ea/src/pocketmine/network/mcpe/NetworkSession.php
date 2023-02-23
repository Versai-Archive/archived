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

namespace pocketmine\network\mcpe;

use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\handler\DeathSessionHandler;
use pocketmine\network\mcpe\handler\HandshakeSessionHandler;
use pocketmine\network\mcpe\handler\LoginSessionHandler;
use pocketmine\network\mcpe\handler\PreSpawnSessionHandler;
use pocketmine\network\mcpe\handler\ResourcePacksSessionHandler;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\handler\SimpleSessionHandler;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\network\NetworkInterface;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;

class NetworkSession{
	/** @var Server */
	private $server;
	/** @var Player */
	private $player;
	/** @var NetworkInterface */
	private $interface;
	/** @var string */
	private $ip;
	/** @var int */
	private $port;
	/** @var int */
	private $ping;

	/** @var SessionHandler */
	private $handler;

	/** @var bool */
	private $connected = true;
	/** @var int */
	private $connectTime;

	/** @var NetworkCipher */
	private $cipher;

	/** @var PacketStream|null */
	private $sendBuffer;

	/** @var \SplQueue|CompressBatchPromise[] */
	private $compressedQueue;

	public function __construct(Server $server, NetworkInterface $interface, string $ip, int $port){
		$this->server = $server;
		$this->interface = $interface;
		$this->ip = $ip;
		$this->port = $port;

		$this->compressedQueue = new \SplQueue();

		$this->connectTime = time();
		$this->server->getNetwork()->scheduleSessionTick($this);

		//TODO: this should happen later in the login sequence
		$this->createPlayer();

		$this->setHandler(new LoginSessionHandler($this->player, $this));
	}

	protected function createPlayer() : void{
		$ev = new PlayerCreationEvent($this);
		$ev->call();
		$class = $ev->getPlayerClass();

		/**
		 * @var Player $player
		 * @see Player::__construct()
		 */
		$this->player = new $class($this->server, $this);

		$this->server->addPlayer($this->player);
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function isConnected() : bool{
		return $this->connected;
	}

	public function getInterface() : NetworkInterface{
		return $this->interface;
	}

	/**
	 * @return string
	 */
	public function getIp() : string{
		return $this->ip;
	}

	/**
	 * @return int
	 */
	public function getPort() : int{
		return $this->port;
	}

	/**
	 * Returns the last recorded ping measurement for this session, in milliseconds.
	 *
	 * @return int
	 */
	public function getPing() : int{
		return $this->ping;
	}

	/**
	 * @internal Called by the network interface to update last recorded ping measurements.
	 *
	 * @param int $ping
	 */
	public function updatePing(int $ping) : void{
		$this->ping = $ping;
	}

	public function getHandler() : SessionHandler{
		return $this->handler;
	}

	public function setHandler(SessionHandler $handler) : void{
		$this->handler = $handler;
		$this->handler->setUp();
	}

	public function handleEncoded(string $payload) : void{
		if(!$this->connected){
			return;
		}

		if($this->cipher !== null){
			Timings::$playerNetworkReceiveDecryptTimer->startTiming();
			try{
				$payload = $this->cipher->decrypt($payload);
			}catch(\InvalidArgumentException $e){
				$this->server->getLogger()->debug("Encrypted packet from " . $this->ip . " " . $this->port . ": " . bin2hex($payload));
				$this->disconnect("Packet decryption error: " . $e->getMessage());
				return;
			}finally{
				Timings::$playerNetworkReceiveDecryptTimer->stopTiming();
			}
		}

		Timings::$playerNetworkReceiveDecompressTimer->startTiming();
		try{
			$stream = new PacketStream(NetworkCompression::decompress($payload));
		}catch(\ErrorException $e){
			$this->server->getLogger()->debug("Failed to decompress packet from " . $this->ip . " " . $this->port . ": " . bin2hex($payload));
			$this->disconnect("Compressed packet batch decode error (incompatible game version?)", false);
			return;
		}finally{
			Timings::$playerNetworkReceiveDecompressTimer->stopTiming();
		}

		while(!$stream->feof() and $this->connected){
			$this->handleDataPacket(PacketPool::getPacket($stream->getString()));
		}
	}

	public function handleDataPacket(DataPacket $packet) : void{
		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		$packet->decode();
		if(!$packet->feof() and !$packet->mayHaveUnreadBytes()){
			$remains = substr($packet->buffer, $packet->offset);
			$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": 0x" . bin2hex($remains));
		}

		$ev = new DataPacketReceiveEvent($this->player, $packet);
		$ev->call();
		if(!$ev->isCancelled() and !$packet->handle($this->handler)){
			$this->server->getLogger()->debug("Unhandled " . $packet->getName() . " received from " . $this->player->getName() . ": 0x" . bin2hex($packet->buffer));
		}

		$timings->stopTiming();
	}

	public function sendDataPacket(DataPacket $packet, bool $immediate = false) : bool{
		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			$ev = new DataPacketSendEvent($this->player, $packet);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			$this->addToSendBuffer($packet);
			if($immediate){
				$this->flushSendBuffer(true);
			}

			return true;
		}finally{
			$timings->stopTiming();
		}
	}

	/**
	 * @internal
	 * @param DataPacket $packet
	 */
	public function addToSendBuffer(DataPacket $packet) : void{
		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			if($this->sendBuffer === null){
				$this->sendBuffer = new PacketStream();
			}
			$this->sendBuffer->putPacket($packet);
			$this->server->getNetwork()->scheduleSessionTick($this);
		}finally{
			$timings->stopTiming();
		}
	}

	private function flushSendBuffer(bool $immediate = false) : void{
		if($this->sendBuffer !== null){
			$promise = $this->server->prepareBatch($this->sendBuffer, $immediate);
			$this->sendBuffer = null;
			$this->queueCompressed($promise, $immediate);
		}
	}

	public function queueCompressed(CompressBatchPromise $payload, bool $immediate = false) : void{
		$this->flushSendBuffer($immediate); //Maintain ordering if possible
		if($immediate){
			//Skips all queues
			$this->sendEncoded($payload->getResult(), true);
		}else{
			$this->compressedQueue->enqueue($payload);
			$payload->onResolve(function(CompressBatchPromise $payload) : void{
				if($this->connected and $this->compressedQueue->bottom() === $payload){
					$this->compressedQueue->dequeue(); //result unused
					$this->sendEncoded($payload->getResult());

					while(!$this->compressedQueue->isEmpty()){
						/** @var CompressBatchPromise $current */
						$current = $this->compressedQueue->bottom();
						if($current->hasResult()){
							$this->compressedQueue->dequeue();

							$this->sendEncoded($current->getResult());
						}else{
							//can't send any more queued until this one is ready
							break;
						}
					}
				}
			});
		}
	}

	private function sendEncoded(string $payload, bool $immediate = false) : void{
		if($this->cipher !== null){
			Timings::$playerNetworkSendEncryptTimer->startTiming();
			$payload = $this->cipher->encrypt($payload);
			Timings::$playerNetworkSendEncryptTimer->stopTiming();
		}
		$this->interface->putPacket($this, $payload, $immediate);
	}

	/**
	 * Disconnects the session, destroying the associated player (if it exists).
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	public function disconnect(string $reason, bool $notify = true) : void{
		if($this->connected){
			$this->connected = false;
			$this->player->close($this->player->getLeaveMessage(), $reason);
			$this->doServerDisconnect($reason, $notify);
		}
	}

	/**
	 * Called by the Player when it is closed (for example due to getting kicked).
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	public function onPlayerDestroyed(string $reason, bool $notify = true) : void{
		if($this->connected){
			$this->connected = false;
			$this->doServerDisconnect($reason, $notify);
		}
	}

	/**
	 * Internal helper function used to handle server disconnections.
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	private function doServerDisconnect(string $reason, bool $notify = true) : void{
		if($notify){
			$pk = new DisconnectPacket();
			$pk->message = $reason;
			$pk->hideDisconnectionScreen = $reason === "";
			$this->sendDataPacket($pk, true);
		}

		$this->interface->close($this, $notify ? $reason : "");
		$this->disconnectCleanup();
	}

	/**
	 * Called by the network interface to close the session when the client disconnects without server input, for
	 * example in a timeout condition or voluntary client disconnect.
	 *
	 * @param string $reason
	 */
	public function onClientDisconnect(string $reason) : void{
		if($this->connected){
			$this->connected = false;
			$this->player->close($this->player->getLeaveMessage(), $reason);
			$this->disconnectCleanup();
		}
	}

	private function disconnectCleanup() : void{
		$this->handler = null;
		$this->interface = null;
		$this->player = null;
		$this->sendBuffer = null;
		$this->compressedQueue = null;
	}

	public function enableEncryption(string $encryptionKey, string $handshakeJwt) : void{
		$pk = new ServerToClientHandshakePacket();
		$pk->jwt = $handshakeJwt;
		$this->sendDataPacket($pk, true); //make sure this gets sent before encryption is enabled

		$this->cipher = new NetworkCipher($encryptionKey);

		$this->setHandler(new HandshakeSessionHandler($this));
		$this->server->getLogger()->debug("Enabled encryption for $this->ip $this->port");
	}

	public function onLoginSuccess() : void{
		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::LOGIN_SUCCESS;
		$this->sendDataPacket($pk);

		$this->player->onLoginSuccess();
		$this->setHandler(new ResourcePacksSessionHandler($this->player, $this, $this->server->getResourcePackManager()));
	}

	public function onResourcePacksDone() : void{
		$this->player->_actuallyConstruct();

		$this->setHandler(new PreSpawnSessionHandler($this->server, $this->player, $this));
	}

	public function onTerrainReady() : void{
		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::PLAYER_SPAWN;
		$this->sendDataPacket($pk);
	}

	public function onSpawn() : void{
		$this->setHandler(new SimpleSessionHandler($this->player));
	}

	public function onDeath() : void{
		$this->setHandler(new DeathSessionHandler($this->player, $this));
	}

	public function onRespawn() : void{
		$this->setHandler(new SimpleSessionHandler($this->player));
	}

	public function tick() : bool{
		if($this->handler instanceof LoginSessionHandler){
			if(time() >= $this->connectTime + 10){
				$this->disconnect("Login timeout");
				return false;
			}

			return true; //keep ticking until timeout
		}

		if($this->sendBuffer !== null){
			$this->flushSendBuffer();
		}

		return false;
	}
}
