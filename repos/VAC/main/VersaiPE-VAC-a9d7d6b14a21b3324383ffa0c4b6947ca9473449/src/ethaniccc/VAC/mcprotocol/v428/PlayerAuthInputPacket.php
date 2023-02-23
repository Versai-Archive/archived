<?php

namespace ethaniccc\VAC\mcprotocol\v428;

use ethaniccc\VAC\mcprotocol\InputConstants;
use ethaniccc\VAC\mcprotocol\LegacyItemSlot;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequest;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;

class PlayerAuthInputPacket extends \pocketmine\network\mcpe\protocol\PlayerAuthInputPacket {

	public ?UseItemInteractionData $itemInteractionData = null;
	public ?ItemStackRequest $stackRequest = null;
	/** @var PlayerBlockAction[]|null */
	public ?array $blockActions = null;

	public static function from(\pocketmine\network\mcpe\protocol\PlayerAuthInputPacket $packet): self {
		$self = new self($packet->getBuffer());
		$self->decode();
		return $self;
	}

	protected function decodePayload(): void {
		parent::decodePayload();
		if (InputConstants::hasFlag($this, InputConstants::PERFORM_ITEM_INTERACTION)) {
			$this->itemInteractionData = new UseItemInteractionData();
			$this->itemInteractionData->legacyRequestId = $this->getVarInt();
			if ($this->itemInteractionData->legacyRequestId !== 0) {
				$k = $this->getUnsignedVarInt();
				for ($i = 0; $i < $k; ++$i) {
					$sl = new LegacyItemSlot();
					$sl->containerId = $this->getByte();
					$sl->slots = $this->getString();
					$this->itemInteractionData->legacyItemSlots[] = $sl;
				}
			}
			$l = $this->getUnsignedVarInt();
			for ($i = 0; $i < $l; ++$i) {
				$this->itemInteractionData->actions[] = (new NetworkInventoryAction())->read($this);
			}
			$this->itemInteractionData->actionType = $this->getUnsignedVarInt();
			$x = $y = $z = 0;
			$this->getBlockPosition($x, $y, $z);
			$this->itemInteractionData->blockPos = new Vector3($x, $y, $z);
			$this->itemInteractionData->blockFace = $this->getVarInt();
			$this->itemInteractionData->hotbarSlot = $this->getVarInt();
			$this->itemInteractionData->heldItem = ItemStackWrapper::read($this)->getItemStack();
			$this->itemInteractionData->playerPos = $this->getVector3();
			$this->itemInteractionData->clickPos = $this->getVector3();
			$this->itemInteractionData->blockRuntimeId = $this->getUnsignedVarInt();
		}
		if (InputConstants::hasFlag($this, InputConstants::PERFORM_ITEM_STACK_REQUEST)) {
			$this->stackRequest = ItemStackRequest::read($this);
		}
		if (InputConstants::hasFlag($this, InputConstants::PERFORM_BLOCK_ACTIONS)) {
			$max = $this->getVarInt();
			for ($i = 0; $i < $max; ++$i) {
				$action = new PlayerBlockAction();
				$action->actionType = $this->getVarInt();
				switch ($action->actionType) {
					case PlayerBlockAction::ABORT_BREAK:
					case PlayerBlockAction::START_BREAK:
					case PlayerBlockAction::CRACK_BREAK:
					case PlayerBlockAction::PREDICT_DESTROY:
					case PlayerBlockAction::CONTINUE:
						$action->blockPos = new Vector3($this->getVarInt(), $this->getVarInt(), $this->getVarInt());
						$action->face = $this->getVarInt();
						break;
				}
				$this->blockActions[] = $action;
			}
		}
	}

}