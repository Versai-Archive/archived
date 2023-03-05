<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 12/28/2018
 * Time: 5:21 PM
 */
declare(strict_types=1);

namespace ARTulloss\Duels\Events;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\Player;

use ARTulloss\Duels\libs\jojoe77777\FormAPI\SimpleForm;
use ARTulloss\Duels\libs\muqsit\invmenu\InvMenu;
use ARTulloss\Duels\Duels;
use ARTulloss\Duels\Match\Task\Heartbeat;
use ARTulloss\Duels\Commands\Constants;
use ARTulloss\Duels\Match\FightEndState;
use ARTulloss\Duels\Queries\Queries;
use ARTulloss\Duels\Utilities\Utilities;

use ReflectionException;
use function round;
use function str_replace;
use function call_user_func;
use function count;

class Listener implements PMListener{
	/** @var Duels $duels */
	private $duels;
	/** @var callable[] $respawnCallables */
	private $respawnCallables;
	/** @var callable[] $worldChangeCallables */
	private $worldChangeCallables;
	/** @var bool[] $playerUITypes */
	private $playerUITypes;

	public const POCKET_INVENTORY_ROW = 5;
	public const CLASSIC_INVENTORY_ROW = 9;

	/**
	 * Observer constructor.
	 * @param Duels $duels
	 */
	public function __construct(Duels $duels)
	{
		$this->duels = $duels;
	}

	public function onChat(PlayerChatEvent $event): void
	{
		$msg = $event->getMessage();

		if($msg[0] === Constants::PARTY_CHAT_SYMBOL) {

			$player = $event->getPlayer();

			$party = $this->duels->partyManager->getPartyForPlayer($player);

			if($party !== null) {
				$msg = $party->getLeader() === $player ? str_replace(['{player}', '{msg}'], [$player->getDisplayName(), substr($msg, 1)], Constants::PARTY_CHAT_FORMAT_LEADER) : str_replace(['{player}', '{msg}'], [$player->getDisplayName(), substr($msg, 1)], Constants::PARTY_CHAT_FORMAT);
				$party->sendMessageToAll($msg);
			} else
				$player->sendMessage(TextFormat::RED . 'Party chat doesn\'t work because you\'re not in a party!');

			$event->setCancelled();

		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();

		// Remove as asked for duel

		$this->duels->duelCommand->removeAllDuelRequests($player);

		// Remove as a spectator
		// Kill on quit match

		foreach ($this->duels->duelManager->getAllRunningMatchHeartbeats() as $heartbeat)
			if($heartbeat !== null) {
				if(($spectators = $heartbeat->getSpectators()) && $spectators !== null && in_array($player, $spectators, true))
					$heartbeat->removeSpectator($player);
				else
					foreach ($heartbeat->getPlayers() as $p)
						if($p === $player)
							$player->kill();
			}



	//	if (($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null)
	//		$player->kill();

		// Remove from queue

		$this->duels->queueManager->removePlayerFromQueue($player);

		// Delete party if owner disconnects

		$party = $this->duels->partyManager->getPartyForPlayer($player);

		if($party !== null) {

			if($player === $party->getLeader()) {
				$party->sendMessageToNonLeader(Constants::DISBAND_PARTY);
				$this->duels->partyManager->disbandParty($party);
			} else {
				$party->removePlayer($player);
				$party->sendMessageToAll(str_replace('{player}', $player->getName(), Constants::LEFT_PARTY));
			}
		}
	}

	/**
	 * @param EntityLevelChangeEvent $event
	 */
	public function onWorldChange(EntityLevelChangeEvent $event): void{
		$player = $event->getEntity();
		if($player instanceof Player) {
            $this->handleCallable($player, $this->worldChangeCallables);
            if (($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null && $match->getStage() !== Heartbeat::STAGE_FINISHED) {
                unset($this->worldChangeCallables[$player->getName()]); // Remove as a winner so leaving doesn't make the menu trigger
                $player->setImmobile(false);
                $player->kill();
            }
			foreach ($this->duels->duelManager->getAllRunningMatchHeartbeats() as $heartbeat)
				if($heartbeat !== null && ($spectators = $heartbeat->getSpectators()) && $spectators !== null && in_array($player, $spectators, true) && $heartbeat->getTempLevelName() !== $event->getTarget()->getName()) {
					$heartbeat->removeSpectator($player);
					break;
				}
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 *
	 * @priority HIGH
	 * @throws ReflectionException
	 */
	public function onDeath(PlayerDeathEvent $event): void{
		$player = $event->getPlayer();
        $cause = $player->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $killer = $cause->getDamager();
        }

        if (($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null) {
			$event->setDeathMessage('');
			$name = $player->getName();
			$match->removeFromPlayers($name);
			
			if(isset($killer) && $killer instanceof Player) {
			    if(count($match->getPlayers()) === 1) {
                    $this->onFightDeath(new FightEndState($killer, $player), true);
                } else
                    $this->onFightDeath(new FightEndState($killer, $player), false);
            }
			    
		} elseif($this->duels->getConfig()->get('Settings')['Global-Fight-Menu']) {
            if(isset($killer) && $killer instanceof Player) {
                $this->onFightDeath(new FightEndState($killer, $player), false);
            }
        }
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event): void{
		$player = $event->getEntity();
		if ($player instanceof Player)
			if ((($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null && $match->getStage() === Heartbeat::STAGE_COUNTDOWN)) {
				$event->setCancelled();
			}
	}

	/**
	 * @param CommandEvent $event
	 */
	public function onCommand(CommandEvent $event): void{
		$player = $event->getSender();
		if($player instanceof Player) {
			$message = $message = $event->getCommand();
//			if (($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null && $match->getStage() === Heartbeat::STAGE_COUNTDOWN)
//				$event->setCancelled();
			if ($this->duels->queueManager->getQueuePlayerIn($player) !== null) {
				switch ($message) {
					case 'duel a':
					/** @noinspection PhpMissingBreakStatementInspection */
					case 'duel accept':
						$this->duels->queueManager->removePlayerFromQueue($player);
					case strstr($message, 'warp'):
					case strstr($message, 'party'):
                    case strstr($message, 'skywars'):
                    case strstr($message, 'sw'):
                    $player->sendTip("\n\n\n" . TextFormat::RED . 'Disabled - In a queue');
						$event->setCancelled();
						return;
				}
			}

			if($this->duels->partyManager->getPartyForPlayer($player) !== null) {
				switch ($message) {
					case strstr($message, 'warp'):
					case strstr($message, 'duel'):
						$player->sendTip("\n\n\n" . TextFormat::RED . 'Disabled - In a party');
						$event->setCancelled();
				}
			}
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function onInventoryTransaction(InventoryTransactionEvent $event) {
		$player = $event->getTransaction()->getSource();
		if (($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null && $match->getStage() === Heartbeat::STAGE_FINISHED)
			$event->setCancelled();
	}

    /**
     * @param FightEndState $state
     * @param bool $sendToWinner Send to the winner as well as the loser
     */
	public function onFightDeath(FightEndState $state, $sendToWinner): void{
	    $winner = $state->getWinner();
	    $loser = $state->getLoser();

	    $winnerContents = $state->getWinnerInventoryContents();
	    $loserContents = $state->getLoserInventoryContents();
	    $winnerArmor = $state->getWinnerArmor();
	    $loserArmor = $state->getLoserArmor();

	    $winnerHealth = round($state->getWinnerHealth(), 1);

	    // Winner contents go to loser so we need to know if loser has classic or pocket ui, same with $loserContents just reverse
	    $winnerContents = $this->createJoinedInventoryContents($winnerContents, $winnerArmor, (bool) $this->playerUITypes[$loser->getName()]);
	    $loserContents = $this->createJoinedInventoryContents($loserContents, $loserArmor, (bool) $this->playerUITypes[$winner->getName()]);

	//    $this->sendEndGameForm($winner, $loser, true, $winnerContents, $loserContents);

        if($sendToWinner) {
            $this->worldChangeCallables[$winner->getName()] = function () use ($winner, $loser, $winnerContents, $loserContents, $winnerHealth) {
                $this->sendEndGameForm($winner, $loser, true, $winnerContents, $loserContents, $winnerHealth);
            };
        }


	    $this->respawnCallables[$loser->getName()] = function () use ($winner, $loser, $winnerContents, $loserContents, $winnerHealth) {
            $this->sendEndGameForm($winner, $loser, false, $winnerContents, $loserContents, $winnerHealth);
        };
	}

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onPacket(DataPacketReceiveEvent $event): void{
	    $pk = $event->getPacket();
	    if($pk instanceof LoginPacket) {
	        // Pocket UI is 1
            // Classic UI is 0
	        $this->playerUITypes[$pk->username] = $pk->clientData['UIProfile'];
        }
    }

    /**
     * @param $inventory
     * @param $armorInventory
     * @param $pocket
     * @return Item[]
     */
    public function createJoinedInventoryContents($inventory, $armorInventory, $pocket): array {
        if($pocket) {
            $firstArmorSlot = 27;
            if(isset($inventory[$firstArmorSlot]) && $inventory[$firstArmorSlot]->getId() !== Item::AIR) {
                $firstArmorSlot += self::POCKET_INVENTORY_ROW; // Add a row if filled
                if(isset($inventory[$firstArmorSlot]) && $inventory[$firstArmorSlot]->getId() !== Item::AIR)
                    $firstArmorSlot += self::POCKET_INVENTORY_ROW; // And another...
            }
            $inventory[$firstArmorSlot] = $armorInventory[0];
            $inventory[$firstArmorSlot + self::POCKET_INVENTORY_ROW] = $armorInventory[1];
            $inventory[$firstArmorSlot + self::POCKET_INVENTORY_ROW * 2] = $armorInventory[2];
            $inventory[$firstArmorSlot + self::POCKET_INVENTORY_ROW * 3] = $armorInventory[3];
            return $inventory;
        } else {
            $firstArmorSlot = 29;
            if(isset($inventory[$firstArmorSlot]) && $inventory[$firstArmorSlot]->getId() !== Item::AIR) {
                $firstArmorSlot += self::CLASSIC_INVENTORY_ROW; // Add a row if filled, no more rows needed for classic ui
            } elseif(isset($inventory[$firstArmorSlot + 3]) && $inventory[$firstArmorSlot + 3]->getId() !== Item::AIR) {
                $firstArmorSlot += self::CLASSIC_INVENTORY_ROW; // Add a row if filled, no more rows needed for classic ui
            }
            $inventory[$firstArmorSlot] = $armorInventory[0]; // Row 1
            $inventory[$firstArmorSlot + 3] = $armorInventory[1]; // 3 spaces between the items
            $inventory[$firstArmorSlot + self::CLASSIC_INVENTORY_ROW] = $armorInventory[2]; // Row 2
            $inventory[$firstArmorSlot + self::CLASSIC_INVENTORY_ROW + 3] = $armorInventory[3];
            return $inventory;
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onRespawn(PlayerRespawnEvent $event): void{
        $this->handleCallable($event->getPlayer(), $this->respawnCallables);
    }

    /**
     * @param Player $player
     * @param $callableArray
     */
    private function handleCallable(Player $player, &$callableArray): void{
        $name = $player->getName();
            if(isset($callableArray[$name])) {
                call_user_func($callableArray[$name]);
                unset($callableArray[$name]);
            }
    }

    /**
     * @param Player $winner
     * @param Player $loser
     * @param bool $first
     * @param array $winnerContents
     * @param Item[] $loserContents
     * @param float $winnerHealth
     */
    public function sendEndGameForm(Player $winner, Player $loser, bool $first, array $winnerContents, array $loserContents, $winnerHealth): void{
        $party = $this->duels->partyManager->getPartyFor($winner->getName()) !== null;

        $form = new SimpleForm(function (Player $player, ?int $data) use ($winner, $loser, $winnerContents, $loserContents, $party, $first, $winnerHealth): void{
            if(isset($data)) {
                if($first) {
                    if(!$party) {
                        switch ($data) {
                            case 0:
                                $winner->getServer()->dispatchCommand($winner, 'duel queue');
                                break;
                            case 1:
                                $winner->getServer()->dispatchCommand($winner, 'duel ' . $loser->getDisplayName());
                                break;
                            case 2:
                                $this->sendInventoryMenu($winner, $loser, $first, $winnerContents, $loserContents, $winnerHealth);
                                break;
                            case 3:
                                $winner->getServer()->dispatchCommand($winner, 'warp');
                        }
                    } else {
                        switch ($data) {
                            case 0:
                                $winner->getServer()->dispatchCommand($winner, 'party');
                                break;
                            case 1:
                                $this->sendInventoryMenu($winner, $loser, $first, $winnerContents, $loserContents, $winnerHealth);
                                break;
                            case 2:
                                $winner->getServer()->dispatchCommand($winner, 'warp');
                                break;
                        }
                    }
                } else {
                    if(!$party) {
                        switch ($data) {
                            case 0:
                                $winner->getServer()->dispatchCommand($loser, 'duel queue');
                                break;
                            case 1:
                                $winner->getServer()->dispatchCommand($loser, 'duel ' . $winner->getDisplayName());
                                break;
                            case 2:
                                $this->sendInventoryMenu($winner, $loser, $first, $winnerContents, $loserContents, $winnerHealth);
                                break;
                            case 3:
                                $loser->getServer()->dispatchCommand($loser, 'warp');
                        }
                    } else {
                        switch ($data) {
                            case 0:
                                $winner->getServer()->dispatchCommand($loser, 'party');
                                break;
                            case 1:
                                $this->sendInventoryMenu($winner, $loser, $first, $winnerContents, $loserContents, $winnerHealth);
                                break;
                            case 2:
                                $loser->getServer()->dispatchCommand($loser, 'warp');
                        }
                    }
                }

            }
        });

        $form->setTitle('Good game!');

        if(!$party) {
            $form->addButton('Queue');
            $form->addButton('Request rematch');
        } else {
            $form->addButton('Party menu');
        }

        $form->addButton('View opponents inventory');

        $form->addButton('Warp');

        $form->addButton('Exit');

        if($first) {
            $form->setTitle('Good game!');
            $winner->sendForm($form);
        } else {
            $form->setTitle('Good game. Their HP: ' . $winnerHealth);
            $loser->sendForm($form);
        }
    }

    /**
     * @param Player $winner
     * @param Player $loser
     * @param bool $first
     * @param array $winnerContents
     * @param array $loserContents
     * @param float $winnerHealth
     */
    public function sendInventoryMenu(Player $winner, Player $loser, bool $first, array $winnerContents, array $loserContents, float $winnerHealth): void{
        $invMenu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $task = new ClosureTask(function (int $currentTick) use ($invMenu, $winner, $loser, $first, $winnerContents, $loserContents, $winnerHealth): void {
            if($first) {
                $invMenu->setName($loser->getDisplayName() . "'s Inventory");
                $invMenu->getInventory()->setContents($loserContents);
            } else {
                $invMenu->setName($winner->getDisplayName() . "'s Inventory");
                $invMenu->getInventory()->setContents($winnerContents);
            }
            $invMenu->readonly();
            $invMenu->setInventoryCloseListener(function (Player $player) use ($winner, $loser, $first, $winnerContents, $loserContents, $winnerHealth) {
                $this->sendEndGameForm($winner, $loser, $first, $winnerContents, $loserContents, $winnerHealth);
            });
            if($first)
                $invMenu->send($winner);
            else
                $invMenu->send($loser);
        });
        //Give player time to render UI
        $toUse = ($first ? $winner : $loser);
        $this->duels->getScheduler()->scheduleDelayedTask($task, ($toUse->getPing() < 300 ? 5 : 0));
    }
    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        if($player instanceof Player) {
            $database = $this->duels->getDatabase();
            $name = $player->getName();
            $this->duels->gappleCooldownTask->setGappleCooldown($name, 0);
            $database->executeSelect(Queries::SELECT_PLAYER, ['player_name' => $name], function ($result) use ($database, $name, $event): void {
                if (count($result) === 0) {
                    $database->executeInsert(Queries::INSERT_PLAYER, ['player_name' => $name], null, Utilities::getOnError($this->duels));
                }
            }, Utilities::getOnError($this->duels));
        }
    }

    public function onConsume(PlayerItemConsumeEvent $event){
        $player = $event->getPlayer();
        if($player instanceof Player){
            if($event->getItem()->getId() === ItemIds::GOLDEN_APPLE) {
                if ($this->duels->gappleCooldownTask->getGappleCooldown($player->getName()) !== 0) {
                    $event->setCancelled();
                    $remainingTime = $this->duels->gappleCooldownTask->getGappleCooldown($player->getName());
                    $player->sendMessage(TextFormat::RED . "You cannot consume another " . TextFormat::GOLD . "Golden Apple" . TextFormat::RED . " for $remainingTime seconds");
                } else {
                    $this->duels->gappleCooldownTask->setGappleCooldown($player->getName(), $this->duels->duelConfig["Settings"]["Gapple-Cooldown"]);
                }
            }
        }
    }
}