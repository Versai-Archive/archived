<?php

namespace ethaniccc\VAC\protocol;

use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket as PMPlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use pocketmine\network\mcpe\protocol\types\ItemInteractionData;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayerBlockAction;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionStopBreak;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;

class PlayerAuthInputPacket extends PMPlayerAuthInputPacket {

	public ?ItemInteractionData $itemInteractionData = null;
	public ?ItemStackRequest $stackRequest = null;
	/** @var PlayerBlockAction[]|null */
	public ?array $blockActions = null;

	protected function decodePayload(PacketSerializer $in): void {
		parent::decodePayload($in);
		if ($this->hasFlag(PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION)) {
			$this->itemInteractionData = ItemInteractionData::read($in);
		}
		if ($this->hasFlag(PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST)) {
			$this->stackRequest = ItemStackRequest::read($in);
		}
		if ($this->hasFlag(PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS)) {
			$this->blockActions = [];
			$max = $in->getVarInt();
			for ($i = 0; $i < $max; ++$i) {
				$actionType = $in->getVarInt();
				$this->blockActions[] = match (true) {
					PlayerBlockActionWithBlockInfo::isValidActionType($actionType) => PlayerBlockActionWithBlockInfo::read($in, $actionType),
					$actionType === PlayerAction::STOP_BREAK => new PlayerBlockActionStopBreak(),
					default => throw new PacketDecodeException("Unexpected block action type $actionType")
				};
			}
		}
	}
}