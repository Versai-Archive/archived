<?php

namespace ethaniccc\VAC\mcprotocol\v428;

use pocketmine\math\Vector3;

class PlayerBlockAction {

	public const START_BREAK = 0;
	public const ABORT_BREAK = 1;
	public const STOP_BREAK = 2;
	public const CRACK_BREAK = 18;
	public const PREDICT_DESTROY = 26;
	public const CONTINUE = 27;

	public int $actionType;
	public Vector3 $blockPos;
	public int $face;

}