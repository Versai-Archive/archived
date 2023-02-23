<?php
declare(strict_types=1);

namespace Versai\Disguise\Events;

use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;

use Versai\Disguise\DisguisedPlayer;
use Versai\Disguise\DisguiseAccessor;
use ARTulloss\Groups\Groups;

class Listener implements PMListener
{
	/**
	 * Listener constructor.
	 * @param DisguiseAccessor $disguiseAccessor
	 */
	public function __construct(private DisguiseAccessor $disguiseAccessor){}

	/**
	 * @param EnableDisguiseEvent $event
	 * @priority LOWEST
	 */
	public function onDisguise(EnableDisguiseEvent $event): void
	{
		$this->disguisePlayer($event->getDisguisedPlayer(), TextFormat::GREEN . 'You are now disguised! You will show as a different player!');
	}

	/**
	 * @param ReDisguiseEvent $event
	 */
	public function onReDisguise(ReDisguiseEvent $event): void
	{
		$this->disguisePlayer($event->getDisguisedPlayer(), TextFormat::GREEN . 'You changed your disguise! You will show as a different player!');
	}

	/**
	 * @param DisguisedPlayer $disguisedPlayer
	 * @param string|null $msg
	 * @priority LOWEST
	 */
	private function disguisePlayer(DisguisedPlayer $disguisedPlayer, string $msg = null): void
	{
		$player = $disguisedPlayer->getPlayer();

		$disguise = $disguisedPlayer->getDisguise();

		$player->setDisplayName($disguise->getName());

		$disguiseSkin = $disguise->getSkin();

		if($disguiseSkin !== null) {
			$player->setSkin($disguiseSkin);
			$player->sendSkin();
		}

		if($player->getServer()->getPluginManager()->getPlugin('Groups') !== null)
		    Groups::getInstance()->playerHandler->reloadPlayer($player);

		if($msg !== null)
			$player->sendMessage($msg);
	}

	/**
	 * @param DisableDisguiseEvent $event
	 * @priority LOWEST
	 */
	public function onDisableDisguise(DisableDisguiseEvent $event): void
	{
		$disguisedPlayer = $event->getDisguisedPlayer();
		$player = $disguisedPlayer->getPlayer();
		$player->setDisplayName($player->getName());
		$player->setSkin($disguisedPlayer->getOldSkin());
		$player->sendSkin();
		$disguisedPlayer->getPlayer()->sendMessage(TextFormat::GREEN . 'You are no longer disguised! You will show as yourself!');
		Groups::getInstance()->playerHandler->reloadPlayer($player);
	}

	/**
	 * Unregister players that leave
	 * @param PlayerQuitEvent $event
	 */
	public function onLeave(PlayerQuitEvent $event): void
	{
		$name = $event->getPlayer()->getName();
		$disguisedPlayers = $this->disguiseAccessor->getDisguisedPlayers();
		if(isset($disguisedPlayers[$name]))
			$disguisedPlayers[$name]->unregister();
	}

	/**
	 * @param DisableButNotDisguisedEvent $event
	 * @priority LOWEST
	 */
	public function onDisableDisguiseFail(DisableButNotDisguisedEvent $event): void
	{
		$event->getPlayer()->sendMessage(TextFormat::RED . "You aren't disguised!");
	}

}