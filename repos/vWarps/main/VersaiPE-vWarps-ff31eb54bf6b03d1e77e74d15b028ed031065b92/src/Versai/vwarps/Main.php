<?php

declare(strict_types=1);

namespace Versai\vwarps;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Versai\vwarps\Commands\CreateWarpCommand;
use Versai\vwarps\Commands\RemoveWarpCommand;
use Versai\vwarps\Commands\WarpCommand;
use Versai\vwarps\Events\Listener;
use Versai\vwarps\Utilities\DeviceOS;
use function explode;
use function implode;

class Main extends PluginBase{

    public const PERMISSION_ROOT = 'warps.';
    public const PERMISSION_USE_ALL = self::PERMISSION_ROOT . 'use';
    public const PERMISSION_USE = self::PERMISSION_USE_ALL . '.{warp}';

    use SingletonTrait;

    private Warps $warps;
    private DeviceOS $deviceOS;

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void {
	    self::setInstance($this);
        $this->getServer()->getPluginManager()->registerEvents(new Listener($this), $this);
        $this->warps = new Warps();
	    $config = $this->getConfig()->getAll();
	    $server = $this->getServer();
	    foreach ($config['Warps'] as $name => $warpData) {
	        list($x, $y, $z) = explode(':', $warpData['Position']);
	        if($server->getWorldManager()->loadWorld($warpData['Level'])) {
                $level = $server->getWorldManager()->getWorldByName($warpData['Level']);
                if($level !== null) {
                    $pos = new Position((float) $x, (float) $y, (float) $z, $level);
                    $format = implode(TextFormat::EOL, $warpData['Format']);
                    $warp = new Warp($pos, $warpData['Yaw'], $warpData['Pitch'], $name, $format, $warpData['Pocket'], $warpData['Image']);
                    $this->warps->addWarp($warp);
                }
            }
        }
	    $server->getCommandMap()->registerAll('warps', [
            new WarpCommand($this, 'warp', 'Warp to places!'),
            new CreateWarpCommand($this, 'createwarp', 'Create a new warp!', ['addwarp']),
            new RemoveWarpCommand($this, 'removewarp', 'Remove a warp!', ['delwarp']),
        ]);
	    PacketHooker::isRegistered() ? : PacketHooker::register($this);
        $this->deviceOS = new DeviceOS();
	}

    /**
     * @return Warps
     */
	public function getWarpsContainer(): Warps {
	    return $this->warps;
    }

    /**
     * @return DeviceOS
     */
    public function getDeviceOS(): DeviceOS {
	    return $this->deviceOS;
    }
}
