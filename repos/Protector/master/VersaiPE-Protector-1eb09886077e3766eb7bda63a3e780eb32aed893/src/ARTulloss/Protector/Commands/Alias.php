<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 1/4/2019
 * Time: 9:14 PM
 */
declare(strict_types=1);
namespace ARTulloss\Protector\Commands;

use ARTulloss\Groups\Groups;
use ARTulloss\Protector\Constants\Constants;
use ARTulloss\Protector\Protector;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;

/**
 * Class Alias
 * @package ARTulloss\Protector\Commands
 */
class Alias extends PluginCommand
{
    /**
     * Alias constructor.
     * @param string $name
     * @param Protector $protector
     */
	public function __construct(string $name, Protector $protector)
	{
	    parent::__construct($name, $protector);
	    $this->setDescription(Constants::ALIAS_DESCRIPTION);
	    $this->setUsage(Constants::ALIAS_USAGE);
	    $this->setPermission(Constants::ALIAS_PERMISSION);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if($this->testPermission($sender)) {

			if (isset($args[0])) {

			    /** @var Protector $protector */
			    $protector = $this->getPlugin();

				$player = $sender->getServer()->getPlayer($args[0]);

				$ip = false;
				$cid = false;
				$device = false;

				if(isset($args[1])) {
					if(strpos($args[1], 'i') !== false)
						$ip = true;
					if(strpos($args[1], 'c') !== false)
						$cid = true;
					if(strpos($args[1], 'd') !== false)
						$device = true;
				} else {
					$ip = true;
					$cid = true;
					$device = true;
				}

				if($player !== null)
					$name = $player->getName();
				else {

					$data = Groups::getInstance()->playerHandler->getPlayerData($args[0]);

					if($data === null) {
						$sender->sendMessage(TextFormat::RED . Constants::NO_DATA_EXISTS);
						return;
					}

					$name = $data->getUsername();
				}

				$sender->sendMessage(str_replace('{player}', $name, Constants::ALIAS_TITLE_FORMAT));

				$ips = null;
				$uuids = null;
				$devices = null;

				$maxSimilarities = 0;

				if($ip === true) {
					$ips = $protector->multi_array_search($name, $protector->ips);
					$maxSimilarities++;
				}
				if($cid === true) {
					$uuids = $protector->multi_array_search($name, $protector->cids);
					$maxSimilarities++;
				}
				if($device === true) {
					$devices = $protector->multi_array_search($name, $protector->devices);
					$maxSimilarities++;
				}

				// Set the players and remove the player from the players

				if($ips !== null) {
					$sameIPPlayers = [];
					foreach ($ips as $ip)
						$sameIPPlayers += $protector->ips[$ip];
					$sameIPPlayers = array_diff($sameIPPlayers, [$name]);
				//	var_dump($sameIPPlayers);
				}

				if($uuids !== null) {
					$sameCIDPlayers = [];
					foreach ($uuids as $cid)
						$sameCIDPlayers += $protector->cids[$cid];
					$sameCIDPlayers = array_diff($sameCIDPlayers, [$name]);
				//	var_dump($sameUUIDPlayers);
				}

				if($devices !== null) {
					$sameDevicePlayers = [];
					foreach ($devices as $device)
						$sameDevicePlayers += $protector->devices[$device];
					$sameDevicePlayers = array_diff($sameDevicePlayers, [$name]);
				//	var_dump($sameDevicePlayers);
				}

				$possiblePlayers = [];

				// Add To Possible Players Array

				if(isset($sameIPPlayers)) {
				//	var_dump($sameIPPlayers);
					foreach ((array)$sameIPPlayers as $playerName)
						isset($possiblePlayers[$playerName]) ? $possiblePlayers[$playerName]++ : $possiblePlayers[$playerName] = 1;
				}

				if(isset($sameCIDPlayers)) {
				//	var_dump($sameUUIDPlayers);
					foreach ((array)$sameCIDPlayers as $playerName)
						isset($possiblePlayers[$playerName]) ? $possiblePlayers[$playerName]++ : $possiblePlayers[$playerName] = 1;
				}

				if(isset($sameDevicePlayers)) {
				//	var_dump($sameDevicePlayers);
					foreach ((array)$sameDevicePlayers as $playerName)
						isset($possiblePlayers[$playerName]) ? $possiblePlayers[$playerName]++ : $possiblePlayers[$playerName] = 1;
				}

			//	var_dump($possiblePlayers);

                $server = $sender->getServer();
                $banList = $server->getNameBans();

				if(!empty($possiblePlayers)) {
					foreach ($possiblePlayers as $possiblePlayer => $similarityLevel) {
					    if($banList->isBanned($possiblePlayer))
					        $possiblePlayer = TextFormat::RED . $possiblePlayer . TextFormat::WHITE;
						$sender->sendMessage(str_replace(['{player}', '{matches}', '{possible}'], [$possiblePlayer, $similarityLevel, $maxSimilarities], Constants::POSSIBLE_PLAYER_FORMAT));
					}
				}

			} else
				throw new InvalidCommandSyntaxException();

		}
	}


}