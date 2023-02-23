<?php

declare(strict_types=1);

namespace vOT\libsmuqsit\simplepackethandler;

use InvalidArgumentException;
use vOT\libsmuqsit\simplepackethandler\interceptor\IPacketInterceptor;
use vOT\libsmuqsit\simplepackethandler\interceptor\PacketInterceptor;
use vOT\libsmuqsit\simplepackethandler\monitor\IPacketMonitor;
use vOT\libsmuqsit\simplepackethandler\monitor\PacketMonitor;
use pocketmine\event\EventPriority;
use pocketmine\plugin\Plugin;

final class SimplePacketHandler{

	public static function createInterceptor(Plugin $registerer, int $priority = EventPriority::NORMAL, bool $handleCancelled = false) : IPacketInterceptor{
		if($priority === EventPriority::MONITOR){
			throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		}
		return new PacketInterceptor($registerer, $priority, $handleCancelled);
	}

	public static function createMonitor(Plugin $registerer, bool $handleCancelled = false) : IPacketMonitor{
		return new PacketMonitor($registerer, $handleCancelled);
	}
}