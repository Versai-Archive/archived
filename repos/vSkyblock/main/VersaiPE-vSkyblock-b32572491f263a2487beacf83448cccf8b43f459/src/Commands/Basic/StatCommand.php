<?php

declare(strict_types=1);

namespace Skyblock\Commands\Basic;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use Skyblock\Translator\Translator;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;

class StatCommand extends BaseCommand {

	public function prepare(): void {
		$this->setDescription(Translator::translate("commands.stat.description"));
		// $this->setPermission("oqex.skyblock.commands.stat");
		$this->setPermission("skyblock");
		$this->registerArgument(0, new RawStringArgument("player", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$inv = $menu->getInventory();
		$slots = 54;
		for($i = 0; $i < $slots; $i++) {
			if($i == 20) {
				$inv->setItem($i, VanillaItems::PLAYER_HEAD()->setCustomName(Translator::translate("commands.stat.personal")));
				continue;
			}
			if ($i == 22) {
				$inv->setItem($i, VanillaItems::NETHER_STAR()->setCustomName(Translator::translate("commands.stat.server")));
				continue;
			}
			if ($i == 24) {
				$inv->setItem($i, VanillaBlocks::GRASS()->asItem()->setCustomName(Translator::translate("commands.stat.island")));
				continue;
			}
			$inv->setItem($i, VanillaBlocks::BARRIER()->asItem());
		}

		$menu->setListener(function(InvMenuTransaction $transaction) : InvMenuTransactionResult{
			$player = $transaction->getPlayer();
			$itemClicked = $transaction->getItemClicked();
			$itemClickedWith = $transaction->getItemClickedWith();
			if($itemClicked->getId() === VanillaBlocks::BARRIER()->asItem()->getId()){
				return $transaction->discard();
			}
			if($itemClicked->getId() === VanillaItems::PLAYER_HEAD()->getId()){
				// TODO: Implement the logic of canceling the cureent transaction and creating a new window based off of the personal stats. maybe just change the inv?
				$personalMenu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
				$personalInv = $personalMenu->getInventory();
				foreach(range(0, 54) as $a){
					$slots = [44, 45, 46, 47, 48, 49, 50, 51, 52, 53];
					if (in_array($a, $slots)) {
						$personalInv->setItem($a, VanillaBlocks::BARRIER()->asItem());
						continue;
					}
					$personalInv->setItem(9, VanillaItems::DIAMOND_SWORD()->setCustomName(Translator::translate("commands.stat.personal.sword")));
				}
				$personalMenu->send($player, "Personal Stats");
			}
			$action = $transaction->getAction();
			$invTransaction = $transaction->getTransaction();
			return $transaction->continue();
		});

		$menu->send($sender, "Stats");
	}
}