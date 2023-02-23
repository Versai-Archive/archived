<?php
declare(strict_types=1);

namespace Versai\Duels\Events;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use ReflectionException;
use Versai\Duels\Commands\Constants;
use Versai\Duels\Duels;
use jojoe77777\FormAPI\SimpleForm;
use Versai\Duels\Match\FightEndState;
use Versai\Duels\Match\Task\Heartbeat;
use function call_user_func;
use function count;
use function round;
use function str_replace;
use function strtolower;

class Listener implements PMListener{

	/** @var Duels $duels */
	private Duels $duels;
	/** @var callable[] $respawnCallables */
	private array $respawnCallables = [];
	/** @var callable[] $worldChangeCallables */
	private array $worldChangeCallables = [];

	/**
	 * Observer constructor.
	 * @param Duels $duels
	 */
	public function __construct(Duels $duels) {
		$this->duels = $duels;
	}

	public function onChat(PlayerChatEvent $event): void {
		$msg = $event->getMessage();

		if($msg[0] === Constants::PARTY_CHAT_SYMBOL) {
			$player = $event->getPlayer();
			$party = $this->duels->partyManager->getPartyForPlayer($player);

			if($party !== null) {
				$msg = $party->getLeader() === $player ? str_replace(['{player}', '{msg}'], [$player->getDisplayName(), substr($msg, 1)], Constants::PARTY_CHAT_FORMAT_LEADER) : str_replace(['{player}', '{msg}'], [$player->getDisplayName(), substr($msg, 1)], Constants::PARTY_CHAT_FORMAT);
				$party->sendMessageToAll($msg);
			} else {
                $player->sendMessage(TextFormat::RED . 'Party chat doesn\'t work because you\'re not in a party!');
            }
			$event->cancel();

		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();
		$this->duels->duelCommand->removeAllDuelRequests($player);

		foreach ($this->duels->duelManager->getAllRunningMatchHeartbeats() as $heartbeat) {
            if ($heartbeat !== null) {
                if (($spectators = $heartbeat->getSpectators()) && $spectators !== null && in_array($player, $spectators, true)) {
                    $heartbeat->removeSpectator($player);
                } else {
                    foreach ($heartbeat->getPlayers() as $p) {
                        if ($p === $player) {
                            $player->kill();
                        }
                    }
                }
            }
        }

		$this->duels->queueManager->removePlayerFromQueue($player);

		$party = $this->duels->partyManager->getPartyForPlayer($player);

		if($party !== null) {
			if($player === $party->getLeader()) {
				$party->sendMessageToNonLeader(Constants::DISBAND_PARTY);
				$this->duels->partyManager->disbandParty($party);
			} else {
				$party->removePlayer($player);
				$party->sendMessageToAll(str_replace('{player}', $player->getDisplayName(), Constants::LEFT_PARTY));
			}
		}
	}

	/**
	 * @param EntityTeleportEvent $event
	 */
	public function onWorldChange(EntityTeleportEvent $event): void
    {
        $player = $event->getEntity();
        if ($event->getFrom()->getWorld() !== $event->getTo()->getWorld()) {
            if ($player instanceof Player) {
                $this->handleCallable($player, $this->worldChangeCallables);
                if (($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null && $match->getStage() !== Heartbeat::STAGE_FINISHED) {
                    unset($this->worldChangeCallables[$player->getName()]); // Remove as a winner so leaving doesn't make the menu trigger
                    $player->setImmobile(false);
                    $player->kill();
                }
                foreach ($this->duels->duelManager->getAllRunningMatchHeartbeats() as $heartbeat) {
                    if ($heartbeat !== null && $heartbeat->getSpectators() !== null && in_array($player, $heartbeat->getSpectators(), true) && $heartbeat->getTempLevelName() !== $event->getTo()->getWorld()->getFolderName()) {
                        $heartbeat->removeSpectator($player);
                        break;
                    }
                }
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
                if (count($match->getPlayers()) === 1) {
                    $killer->getEffects()->clear();
                    $player->getEffects()->clear();
                    $this->onFightDeath(new FightEndState($killer, $player), true);
                } else {
                    $killer->getEffects()->clear();
                    $player->getEffects()->clear();
                    $this->onFightDeath(new FightEndState($killer, $player), false);
                }
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
		if ($player instanceof Player) {
            if ((($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null && $match->getStage() === Heartbeat::STAGE_COUNTDOWN)) {
                $event->cancel();
            }
        }
	}

	/**
	 * @param CommandEvent $event
	 */
	public function onCommand(CommandEvent $event): void{
		$player = $event->getSender();
		if($player instanceof Player) {
			$message = $event->getCommand();
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
						$event->cancel();
						return;
				}
			}

			if($this->duels->partyManager->getPartyForPlayer($player) !== null) {
				switch ($message) {
					case strstr($message, 'warp'):
					case strstr($message, 'duel'):
						$player->sendTip("\n\n\n" . TextFormat::RED . 'Disabled - In a party');
						$event->cancel();
						break;
				}
			}

			if($this->duels->duelManager->getPlayersMatch($player) !== null && !$player->hasPermission("duels.bypass")){
                $player->sendTip("\n\n\n" . TextFormat::RED . 'You cannot execute commands in a duel!');
			    $event->cancel();
            }
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function onInventoryTransaction(InventoryTransactionEvent $event) {
		$player = $event->getTransaction()->getSource();
		if (($match = $this->duels->duelManager->getPlayersMatch($player)) && $match !== null && $match->getStage() === Heartbeat::STAGE_FINISHED) {
            $event->cancel();
        }
	}

    /**
     * @param FightEndState $state
     * @param bool $sendToWinner Send to the winner as well as the loser
     */
	public function onFightDeath(FightEndState $state, bool $sendToWinner): void{
	    $winner = $state->getWinner();
	    $loser = $state->getLoser();

	    $winnerHealth = round($state->getWinnerHealth(), 1);


        if($sendToWinner) {
            $this->worldChangeCallables[$winner->getName()] = function () use ($winner, $loser, $winnerHealth) {
                $this->sendEndGameForm($winner, $loser, true, $winnerHealth);
            };
        }


	    $this->respawnCallables[$loser->getName()] = function () use ($winner, $loser, $winnerHealth) {
            $this->sendEndGameForm($winner, $loser, false, $winnerHealth);
        };
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
     * @param float $winnerHealth
     */
    public function sendEndGameForm(Player $winner, Player $loser, bool $first, float $winnerHealth): void{
        $party = $this->duels->partyManager->getPartyFor($winner->getName()) !== null;

        $form = new SimpleForm(function (Player $player, ?int $data) use ($winner, $loser, $party, $first, $winnerHealth): void{
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
                                $winner->getServer()->dispatchCommand($winner, 'warp');
                        }
                    } else {
                        switch ($data) {
                            case 0:
                                $winner->getServer()->dispatchCommand($winner, 'party');
                                break;
                            case 1:
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
                                $loser->getServer()->dispatchCommand($loser, 'warp');
                        }
                    } else {
                        switch ($data) {
                            case 0:
                                $winner->getServer()->dispatchCommand($loser, 'party');
                                break;
                            case 1:
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

        $form->addButton('Warp');
        $form->addButton('Exit');

        if($first) {
            $form->setTitle('Good game!');
            if($winner instanceof Player && $winner->isConnected()) {
                $winner->sendForm($form);
            }
        } else {
            $form->setTitle('Good game. Their HP: ' . $winnerHealth);
            if($loser instanceof Player && $loser->isConnected()) {
                $loser->sendForm($form);
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        if($player instanceof Player) {
            $name = $player->getName();
            $this->duels->gappleCooldownTask->setGappleCooldown($name, 0);
        }
    }

    public function onConsume(PlayerItemConsumeEvent $event){
        $player = $event->getPlayer();
        if($player instanceof Player){
            if($event->getItem()->getId() === ItemIds::GOLDEN_APPLE) {
                if ($this->duels->gappleCooldownTask->getGappleCooldown($player->getName()) !== 0) {
                    $event->cancel();
                    $remainingTime = $this->duels->gappleCooldownTask->getGappleCooldown($player->getName());
                    $player->sendMessage(TextFormat::RED . "You cannot consume another " . TextFormat::GOLD . "Golden Apple" . TextFormat::RED . " for $remainingTime seconds");
                } else {
                    $this->duels->gappleCooldownTask->setGappleCooldown($player->getName(), $this->duels->duelConfig["Settings"]["Gapple-Cooldown"]);
                }
            }
        }
    }

    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();

        $match = $this->duels->duelManager->getPlayersMatch($player);
        if($match instanceof Heartbeat){
            if($match->getStage() === Heartbeat::STAGE_PLAYING){
                $kitType = strtolower($match->getKitType());
                if($kitType === "builduhc" || $kitType === "spleef") {
                    return;
                }
            }
            $event->cancel();
        }
    }
}