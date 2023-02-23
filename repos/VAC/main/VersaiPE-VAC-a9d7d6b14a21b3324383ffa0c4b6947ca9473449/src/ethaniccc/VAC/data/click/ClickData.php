<?php

namespace ethaniccc\VAC\data\click;

final class ClickData {

	public int $delta = 0;
	public int $lastClickTick = 0;
	public int $clientCPS = 0;
	public int $serverCPS = 0;
	public array $clientClickTicks = [];
	public array $serverClickTicks = [];
	public bool $isClickDataReliable = false;

	public function add(int $currentTick): void {
		$delta = $currentTick - $this->lastClickTick;
		$this->lastClickTick = $currentTick;
		$this->delta = $delta;
		$this->clientClickTicks[] = $currentTick;
		$this->clientClickTicks = array_filter($this->clientClickTicks, function (int $tick) use ($currentTick): bool {
			return $currentTick - $tick <= 20;
		});
		$this->clientCPS = count($this->clientClickTicks);
		$current = microtime(true);
		$this->serverClickTicks[] = $current;
		$this->serverClickTicks = array_filter($this->serverClickTicks, function (int $time) use ($current): bool {
			return $current - $time <= 1;
		});
		$this->serverCPS = count($this->serverClickTicks);
		$this->isClickDataReliable = $this->clientCPS - $this->serverCPS <= 2;
	}

}