<?php

declare(strict_types=1);

namespace Versai\arenas\libs\CortexPE\Commando\libs\muqsit\simplepackethandler\interceptor;

use Closure;
use pocketmine\network\mcpe\NetworkSession;

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