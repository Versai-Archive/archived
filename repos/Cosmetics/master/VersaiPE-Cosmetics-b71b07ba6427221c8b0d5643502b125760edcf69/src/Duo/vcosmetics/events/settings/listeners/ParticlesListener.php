<?php
declare(strict_types=1);

namespace Duo\vcosmetics\events\settings\listeners;

use pocketmine\color\Color;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\world\particle\EnchantmentTableParticle;
use pocketmine\world\particle\EnchantParticle;
use pocketmine\world\particle\EntityFlameParticle;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\particle\InkParticle;
use pocketmine\world\particle\InstantEnchantParticle;
use pocketmine\world\particle\ItemBreakParticle;
use pocketmine\world\particle\LavaDripParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\particle\PortalParticle;
use pocketmine\world\particle\RainSplashParticle;
use pocketmine\world\particle\RedstoneParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\SplashParticle;
use Duo\vcosmetics\constants\Particles;
use Duo\vcosmetics\Main;
use Versai\vStaff\EventListener as vStaff;

class ParticlesListener implements Listener {

	private Main $plugin;

	public function __construct(Main $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event): void{

		if ($event instanceof EntityDamageByEntityEvent) {
			$player = $event->getDamager();
			$damaged = $event->getEntity();

			if ($player instanceof Player && $damaged instanceof Player) {
				$playerSession = $this->plugin->getSessionManager()->getSession($player);

				if ($playerSession !== null && $this->plugin->getSessionManager()->getSession($damaged) !== null && !vStaff::isEnabled($player)) {
                    $this->addParticle($damaged, $playerSession->getHitParticle());
                }
			}
		}
	}

	/**
	 * @param Player $player
	 * @param int $selected
	 */
	public function addParticle(Player $player, int $selected): void {
		$name = $player->getName();
        $playerSession = $this->plugin->getSessionManager()->getSession($player);

		if ($playerSession !== null) {

			$pos = $player->getPosition();
			$pos = $pos->add(rand(-50, 50) / 100, 0.2, rand(-50, 50) / 100);
			$particle = "";

			switch ($selected) {
				case Particles::CRITICAL:
					$particle = new CriticalParticle();
					break;
				case Particles::FLAME:
					$particle = new FlameParticle();
					break;
				case Particles::SPLASH:
					$particle = new SplashParticle();
					break;
				case Particles::REDSTONE:
					$particle = new RedstoneParticle();
					break;
				case Particles::PORTAL:
					$particle = new PortalParticle();
					break;
				case Particles::SPIRAL:
					$particle = new EnchantParticle(new Color(255, 255, 255));
					break;
				case Particles::GLYPH:
					$particle = new EnchantmentTableParticle();
					break;
				case Particles::HEART:
					$particle = new HeartParticle();
					break;
				case Particles::LAVA:
					$particle = new LavaParticle();
					break;
				case Particles::EMBER:
					$particle = new LavaDripParticle();
					break;
				case Particles::FIRE:
					$particle = new EntityFlameParticle();
					break;
				case Particles::SMOKE;
					$particle = new SmokeParticle();
					break;
				case Particles::EXPLOSION:
					$particle = (mt_rand(1, 100) === 1) ? new HugeExplodeSeedParticle() : new ExplodeParticle();
					break;
				case Particles::RAIN:
					$particle = new RainSplashParticle();
					break;
				case Particles::INK:
					$particle = new InkParticle();
					break;
				case Particles::DARKNESS:
					$particle = new InstantEnchantParticle(Color::fromRGB(255));
					break;
				case Particles::CLOUD:
					$particle = new AngryVillagerParticle();
					break;
				case Particles::STAR:
					$particle = new HappyVillagerParticle();
					break;
				case Particles::DIAMOND:
					$particle = new ItemBreakParticle(VanillaItems::DIAMOND());
					break;
				case Particles::EMERALD:
					$particle = new ItemBreakParticle(VanillaItems::EMERALD());
					break;
				case Particles::LAPIS:
					$particle = new ItemBreakParticle(VanillaItems::LAPIS_LAZULI());
					break;
				case Particles::GOLD:
					$particle = new ItemBreakParticle(VanillaItems::GOLD_INGOT());
					break;
				default:
					return;
			}
			if($particle instanceof Particle) {
                $player->getWorld()->addParticle($pos, $particle);
            }
		}
	}

	/**
	 * @param PlayerMoveEvent $event
	 */
	public function onMove(PlayerMoveEvent $event): void {
		$player = $event->getPlayer();
		$name = $player->getName();
		$session = $this->plugin->getSessionManager()->getSession($player);


		if ($session !== null && !$player->getGamemode()->equals(GameMode::CREATIVE()) && !vStaff::isEnabled($player)) {
            $this->addParticle($player, $session->getFollowParticle());
        }
	}
}