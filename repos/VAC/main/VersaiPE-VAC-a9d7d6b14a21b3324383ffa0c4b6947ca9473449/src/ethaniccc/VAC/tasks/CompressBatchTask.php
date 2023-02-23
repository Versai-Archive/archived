<?php

namespace ethaniccc\VAC\tasks;

use ethaniccc\VAC\PMListener;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CompressBatchTask extends AsyncTask{

	public int $level = 7;
	public $data;

	public function __construct(BatchPacket $batch, Player $target) {
		$this->data = $batch->payload;
		$this->level = $batch->getCompressionLevel();
		$this->storeLocal($target);
	}

	public function onRun() {
		$batch = new BatchPacket();
		$batch->payload = $this->data;

		$batch->setCompressionLevel($this->level);
		$batch->encode();

		$this->setResult($batch->buffer);
	}

	public function onCompletion(Server $server) {
		$pk = new BatchPacket($this->getResult());
		$pk->isEncoded = true;

		/** @var Player $target */
		$target = $this->fetchLocal();
		PMListener::$isACPacket = true;
		$target->sendDataPacket($pk, false, true);
	}
}