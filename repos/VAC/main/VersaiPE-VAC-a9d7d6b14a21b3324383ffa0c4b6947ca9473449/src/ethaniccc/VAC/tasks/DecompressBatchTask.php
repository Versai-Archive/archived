<?php

namespace ethaniccc\VAC\tasks;

use ethaniccc\VAC\PMListener;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

final class DecompressBatchTask extends AsyncTask {

	public int $level = 7;
	public $data;

	public function __construct(BatchPacket $batch, callable $future) {
		$this->data = $batch->buffer;
		$this->level = $batch->getCompressionLevel();
		$this->storeLocal($future);
	}

	public function onRun() {
		$batch = new BatchPacket($this->data);
		$batch->setCompressionLevel($this->level);
		$batch->decode();
		$this->setResult($batch->payload);
	}

	public function onCompletion(Server $server) {
		$pk = new BatchPacket();
		$pk->payload = $this->getResult();

		$callable = $this->fetchLocal();
		($callable)($pk);
	}

}
