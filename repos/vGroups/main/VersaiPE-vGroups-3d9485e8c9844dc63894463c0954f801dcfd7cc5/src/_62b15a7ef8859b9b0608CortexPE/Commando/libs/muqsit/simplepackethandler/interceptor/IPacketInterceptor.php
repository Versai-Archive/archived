<?php

declare(strict_types=1);

namespace _62b15a7ef8859b9b0608CortexPE\Commando\libs\muqsit\simplepackethandler\interceptor;

use Closure;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

interface IPacketInterceptor{

	/**
	 * @param Closure $handler
	 * @return IPacketInterceptor
	 *
	 * @phpstan-template TServerboundPacket of ServerboundPacket
	 * @phpstan-param Closure(TServerboundPacket, NetworkSession) : bool $handler
	 */
	public function interceptIncoming(Closure $handler) : IPacketInterceptor;

	/**
	 * @param Closure $handler
	 * @return IPacketInterceptor
	 *
	 * @phpstan-template TClientboundPacket of ClientboundPacket
	 * @phpstan-param Closure(TClientboundPacket, NetworkSession) : bool $handler
	 */
	public function interceptOutgoing(Closure $handler) : IPacketInterceptor;
}