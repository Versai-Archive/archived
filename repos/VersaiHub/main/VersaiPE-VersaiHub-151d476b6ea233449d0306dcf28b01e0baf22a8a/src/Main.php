<?php

/*
 * Copyright Versai Network (c) 2022.
 *
 * This file and any attachments are only for the use of the intended recipient and may contain information
 * that is legally privileged, confidential or exempt from disclosure under applicable law.
 *
 * If you are not the intended recipient, any disclosure, distribution or other use of this file or attachments is prohibited.
 *
 */

declare(strict_types=1);


namespace Versai;

require dirname(__FILE__, 2) . "/vendor/autoload.php";

use Medoo\Medoo;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\sound\BellRingSound;
use pocketmine\world\sound\ExplodeSound;
use Versai\Forms\SimpleForm;
use Versai\Hotbars\Hotbar;
use Versai\Hotbars\HotbarItem;
use Versai\Hotbars\HotbarManager;
use Versai\Listeners\PlayerListener;
use Versai\Provider\Database;
use Versai\Tasks\AnnounceTask;
use Versai\Session\SessionManager;
use Versai\Tasks\NavigationDataTask;
use Versai\Tasks\SpawnParticlesTask;

class Main extends PluginBase {

	use SingletonTrait;

    public array $navigatorButtonInfo = [];
    public array $navigatorAddressInfo = [];

    public Database $database;

	public SessionManager $sessionManager;
	public HotbarManager $hotbarManager;

	public function onLoad(): void {

	}

	public function onEnable(): void {

		self::setInstance($this);

        $this->database = new Database(new Medoo([
            'type' => 'mysql',
            'host' => $this->getConfig()->getNested("Database.host"),
            'database' => $this->getConfig()->getNested("Database.schema"),
            'username' => $this->getConfig()->getNested("Database.user"),
            'password' => $this->getConfig()->getNested("Database.password")
        ]));

		$this->registerTasks([
			new AnnounceTask((int)(20 * 60 * (float)$this->getConfig()->getNested("TimeBetweenAnnouncements"))),
			new SpawnParticlesTask(10),
            new NavigationDataTask(24000)
		]);

		Server::getInstance()->getPluginManager()->registerEvents(new PlayerListener(), $this);

		$this->sessionManager = new SessionManager();
		$this->hotbarManager = new HotbarManager();

		$hotbar = new Hotbar("MAIN");
        $pvpHotbar = new Hotbar("PVP");

        //begin main hotbar items

        //Enable PVP Item
		$hotbar->setItem((new HotbarItem(new ItemIdentifier(VanillaItems::STICK()->getId(), 0), function (Player $player) {
            $session = Main::getInstance()->getSessionManager()->getSession($player);

            if(!$session->isPvpEnabled()){
                $session->togglePvP();
                return;
            }
            $player->getArmorInventory()->clearAll();
            $player->getInventory()->clearAll();

            $armourItems = [
                VanillaItems::DIAMOND_HELMET(),
                VanillaItems::DIAMOND_CHESTPLATE(),
                VanillaItems::DIAMOND_LEGGINGS(),
                VanillaItems::DIAMOND_BOOTS()
            ];

            $player->getArmorInventory()->setContents($armourItems);
            Main::getInstance()->getHotbarManager()->getHotbar("PVP")->sendTo($player);
            $player->setGamemode(GameMode::SURVIVAL());

            $player->sendTitle("§b> §aHUB PVP Enabled! §b<");
            $player->getWorld()->addSound($player->getPosition(), new ExplodeSound($player));

		}))->setCustomName("§b> §aEnable Hub PVP §b<")->setLore(["§r§dRight Click To Enable Hub PVP!"]), 8);

        //Server Navigator Item
        $hotbar->setItem((new HotbarItem(new ItemIdentifier(VanillaItems::COMPASS()->getId(), 0), function (Player $player) {
            $form = new SimpleForm(function(Player $player, $data): void {
                if($data === null){
                    return;
                };

                foreach(Main::getInstance()->navigatorButtonInfo as $name => $bData){
                    switch($data){
                        case $name:
                            $address = Main::getInstance()->navigatorAddressInfo[$name]["address"];
                            $port = Main::getInstance()->navigatorAddressInfo[$name]["port"];
                            $player->transfer($address, $port);
                            break;
                        default:
                            break;
                    }
                }

            });
            $form->setTitle("§eServer Navigator");
            $form->setContent("§7Please select a server:");
            foreach(Main::getInstance()->navigatorButtonInfo as $name => $data){
                $form->addButton($data["bd"], 0, $data["img"], $name);
            }
            $form->addButton("§cClick Here To Exit!", 0, "textures/blocks/barrier", "exit");
            $player->sendForm($form);
            return;
        }))->setCustomName("§b> §dServer Navigator §b<")->setLore(["§r§dRight Click To Open Server UI!"]), 4);

        //begin pvp hotbar items
        $pvpHotbar->setItem((new HotbarItem(new ItemIdentifier(VanillaItems::DIAMOND_SWORD()->getId(), 0), function () {})), 0);
        $pvpHotbar->setItem((new HotbarItem(new ItemIdentifier(VanillaItems::GOLDEN_APPLE()->getId(), 0), function () {}))->setCount(2), 1);
        $pvpHotbar->setItem((new HotbarItem(new ItemIdentifier(VanillaItems::ENDER_PEARL()->getId(), 0), function () {}))->setCount(3), 2);
        $pvpHotbar->setItem((new HotbarItem(new ItemIdentifier(VanillaItems::STICK()->getId(), 0), function (Player $player) {
            $session = Main::getInstance()->getSessionManager()->getSession($player);

            if($session->isPvpEnabled()){
                $session->togglePvP();
                return;
            }

            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();

            Main::getInstance()->getHotbarManager()->getHotbar("MAIN")->sendTo($player);
            $player->setGamemode(GameMode::ADVENTURE());
            $player->sendTitle("§b> §cHUB PVP Disabled! §b<");
            $player->getWorld()->addSound($player->getPosition(), new BellRingSound($player));

        }))->setCustomName("§b§l> §cDisable Hub PVP §b<")->setLore(["§r§dRight Click To Disable Hub PVP!"]), 8);


		$this->hotbarManager->registerHotbar($hotbar);
        $this->hotbarManager->registerHotbar($pvpHotbar);
        $this->database->initTables();
	}

	public function registerTasks(array $tasks): void {
		foreach ($tasks as $task) {
			$this->getScheduler()->scheduleRepeatingTask($task, $task->ticks);
		}
	}

    public function getDatabase(): Database {
        return $this->database;
    }

	public function getSessionManager(): SessionManager {
		return $this->sessionManager;
	}

	public function	getHotbarManager(): HotbarManager {
		return $this->hotbarManager;
	}

}