<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 9/4/2018
 * Time: 8:55 PM
 */
declare(strict_types = 1);
namespace ARTulloss\Protector\Task;

use function gettype;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

use ARTulloss\Protector\Constants\Constants;

/**
 * Class VPNCheck
 * @package ARTulloss\Protector\Task
 */
Class VPNCheck extends AsyncTask
{
	/** @var array $asns */
	private $asns;
	/** @var string $name */
	private $name;
	/** @var string $ip */
	private $ip;
	/** @var string $key */
	private $key;
	/** @var bool $cache */
	private $cache;
	/** @var int $block */
	private $block;

	public const RESIDENTIAL = 0;
	public const HOSTING = 1;
	public const BOTH = 2;

	/**
	 * VPNCheck constructor.
	 * @param array $asns
	 * @param string $name
	 * @param string $ip
	 * @param string $key
	 * @param bool $cache
	 */
	public function __construct(array $asns, string $name, string $ip, string $key, bool $cache)
	{
		$this->asns = $asns;
		$this->name = $name;
		$this->ip = $ip;
		$this->key = $key;
		$this->cache = $cache;
	}

	public function onRun()
	{
		// Get data from API
		$curl = curl_init("http://v2.api.iphub.info/ip/" . $this->ip);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-Key: " . $this->key]);
		$response = curl_exec($curl);
		curl_close($curl);
		if(gettype($response) !== 'string')
		    return;
		$data = json_decode($response, true);

		if(isset($data['block']) && isset($data['asn'])) {
			if(in_array($data['asn'], (array) $this->asns))
				$data['block'] = VPNCheck::HOSTING;

			$this->block = $data['block'];
		}
	}

	/**
	 * @param Server $server
	 */
	public function onCompletion(Server $server): void
	{
		if(isset($this->block)) {

			if($this->cache)
				$server->getPluginManager()->getPlugin('Protector')->logVPNData($this->ip, $this->block);

			$hasVPN = in_array($this->block, Constants::BLOCK);

			if($hasVPN)
				$server->getPlayerExact($this->name)->kick(Constants::VPN_MESSAGE, false);

		} else
			$server->getLogger()->error(str_replace('{key}', $this->key, Constants::VPN_REQUEST_LIMIT));
	}
}
