<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/4/2019
 * Time: 10:58 AM
 */
declare(strict_types=1);
namespace ARTulloss\Protector;

use ARTulloss\Groups\Groups;
use ARTulloss\Protector\Commands\Alias;
use ARTulloss\Protector\Commands\PlayerInfo;
use ARTulloss\Protector\Commands\VPNCommand;
use ARTulloss\Protector\Constants\Devices;
use ARTulloss\Protector\Utilities\DeviceOS;
use const DIRECTORY_SEPARATOR;
use pocketmine\plugin\PluginBase;

use ARTulloss\Protector\Events\Listener;

use function file_exists;
use function json_decode;
use function json_encode;
use function file_put_contents;

/**
 * Class Protector
 * @package ARTulloss\Protector
 */
class Protector extends PluginBase
{
	/** @var array $ips */
	public $ips;
	/** @var array $vpnData */
	public $vpnData;
	/** @var array $devices */
	public $devices;
	/** @var array $cids */
	public $cids;
	/** @var array $config */
	public $config;
	/** @var Groups $groups */
	public $groups;
	/** @var DeviceOS $deviceOS */
	private $deviceOS;

	public function onEnable()
	{
		$this->saveDefaultConfig();

		$server = $this->getServer();

		$pluginMgr = $server->getPluginManager();
		$pluginMgr->registerEvents(new Listener($this), $this);
		$this->groups = $pluginMgr->getPlugin('Groups');
		$this->config = $this->getConfig()->getAll();

		$server->getCommandMap()->registerAll('protector', [
			new PlayerInfo('pinfo', $this),
			new Alias('alias', $this),
            new VPNCommand('vpn', $this)
		]);

		$this->registerFiles();
		$this->deviceOS = new DeviceOS();
	}

	public function onDisable()
	{
		$this->saveFiles();
	}

	/**
	 * Data Registration
	 */
	public function registerFiles(): void
	{
		$dataFolder = $this->getDataFolder();

		// IP Logging

		if(!file_exists($dataFolder . DIRECTORY_SEPARATOR . 'ips.json')) {
			file_put_contents($dataFolder . DIRECTORY_SEPARATOR . 'ips.json', json_encode([]));
			$this->registerFiles();
		}

		$this->ips = json_decode(file_get_contents($dataFolder . DIRECTORY_SEPARATOR . 'ips.json'), true);


		// VPN IP data

		if(!file_exists($dataFolder . DIRECTORY_SEPARATOR . 'vpn.json')) {
			file_put_contents($dataFolder . DIRECTORY_SEPARATOR . 'vpn.json', json_encode([]));
			$this->registerFiles();
		}

		$this->vpnData = json_decode(file_get_contents($dataFolder . DIRECTORY_SEPARATOR . 'vpn.json'), true);


		// Cids

		if(!file_exists($dataFolder . DIRECTORY_SEPARATOR . 'cids.json')) {
			file_put_contents($dataFolder . DIRECTORY_SEPARATOR . 'cids.json', json_encode([]));
			$this->registerFiles();
		}

		$this->cids = json_decode(file_get_contents($dataFolder . DIRECTORY_SEPARATOR . 'cids.json'), true);

		// Devices

		if(!file_exists($dataFolder . DIRECTORY_SEPARATOR . 'devices.json')) {
			file_put_contents($dataFolder . DIRECTORY_SEPARATOR . 'devices.json', json_encode([]));
			$this->registerFiles();
		}

		$this->devices = json_decode(file_get_contents($dataFolder . DIRECTORY_SEPARATOR . 'devices.json'), true);
	}

	public function saveFiles(): void
	{
		$dataFolder = $this->getDataFolder() . DIRECTORY_SEPARATOR;

		file_put_contents($dataFolder . 'ips.json', json_encode($this->ips, JSON_PRETTY_PRINT));
		file_put_contents($dataFolder . 'vpn.json', json_encode($this->vpnData, JSON_PRETTY_PRINT));
		file_put_contents($dataFolder . 'cids.json', json_encode($this->cids, JSON_PRETTY_PRINT));
		file_put_contents($dataFolder . 'devices.json', json_encode($this->devices, JSON_PRETTY_PRINT));
	}

	/**
	 * Random proxy API key from configuration
	 * @return string
	 */
	public function getRandomKey(): string
	{
		return $this->config['Keys'][array_rand($this->config['Keys'])];
	}

	/**
	 * @param int $deviceOS
	 * @return string
	 */
	public function translateDeviceOS(int $deviceOS): string
	{
		switch ($deviceOS) {
			case Devices::OS_ANDROID:
				return 'Android';
			case Devices::OS_IOS:
				return 'iOS';
			case Devices::OS_OSX:
				return 'Mac OS';
			case Devices::OS_FIREOS:
				return 'Fire OS';
			case Devices::OS_GEARVR:
				return 'Gear VR';
			case Devices::OS_HOLOLENS:
				return 'Hololens';
			case Devices::OS_WIN10;
				return 'Windows 10';
			case Devices::OS_WIN32;
				return 'Windows 32 bit';
			case Devices::OS_DEDICATED:
				return 'Dedicated';
			case Devices::OS_ORBIS:
				return 'Orbis';
			case Devices::OS_NX:
				return 'OS NX';
		}
		return 'Unknown';
	}

	/**
	 * @param string $ip
	 * @param int $result
	 */
	public function logVPNData(string $ip, int $result): void
	{
		$this->vpnData[$ip] = $result;
	}

	/**
	 * Weird function I made to find the key(s) of an array that a value is in?
	 * Kind of like array search but works on multidimensional arrays
	 * @param string $needle
	 * @param array $haystack
	 * @return array
	 */
	public function multi_array_search(string $needle, array $haystack): ?array
	{
		$found = false;
		$returned = [];
		foreach ($haystack as $key => $stack) {
			foreach ($stack as $value)
				if ($needle === $value) {
					$returned[] = $key;
					$found = true;
				}
		}
		if(!$found)
			return null;
		return $returned;
	}
    /**
     * @return DeviceOS
     */
	public function getDeviceOS(): DeviceOS{
	    return $this->deviceOS;
    }

}