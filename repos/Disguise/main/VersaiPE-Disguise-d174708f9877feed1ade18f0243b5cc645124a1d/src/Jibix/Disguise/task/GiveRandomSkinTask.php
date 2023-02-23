<?php
namespace Jibix\Disguise\task;
use Jibix\Disguise\utils\SkinUtils;
use Jibix\Disguise\utils\Utils;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use Ramsey\Uuid\Uuid;


/**
 * Class GiveRandomSkinTask
 * @package Jibix\Disguise\command
 * @author Jibix
 * @date 08.02.2022 - 23:33
 * @project Disguise
 */
class GiveRandomSkinTask extends AsyncTask{

    /**
     * @param string $playerName
     * @param string $url
     * @param string $targetFile
     */
	public function __construct(private string $playerName, private string $url, private string $targetFile){}

    /**
     * Function onRun
     */
	public function onRun(): void{
		$parse = parse_url($this->url, PHP_URL_PATH);
		if ($parse === null || $parse === false) {
			$this->setResult(null);
			return;
		}
		$data = Internet::getURL($this->url);

		if ($data === false) {
			$this->setResult(null);
			return;
		}
		file_put_contents($this->targetFile, $data->getBody());
		$this->setResult($this->targetFile);
	}

    /**
     * Function onCompletion
     */
    public function onCompletion(): void{
        $player = Server::getInstance()->getPlayerExact($this->playerName);
        if (!$player instanceof Player) return;

        $player->setSkin(SkinUtils::getSkinFromPng($this->getResult(), $player->getSkin()->getSkinId()));
        $player->sendSkin();
        unlink($this->targetFile);
    }
}
