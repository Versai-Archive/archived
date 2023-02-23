<?php


namespace Sam\VCrates\tile;


use muqsit\invmenu\InvMenu;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\sound\FizzSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\utils\TextFormat as TF;
use Sam\VCrates\constants\Messages;
use Sam\VCrates\constants\Rarity;
use Sam\VCrates\database\Manager;
use Sam\VCrates\Main;
use Sam\VCrates\task\ClearEntity;
use Sam\VCrates\task\SpinningTask;
use Sam\VCrates\task\TextTask;

class VCrateTile extends Chest{

	/** @var string */
	protected $crateName;

	/** @var string */
	protected $type;

	/** @var bool */
	public $in_use = false;

	/** @var Player|null */
	public $currentPlayer;

	/** @var array[] */
	public $floatingTextParticles = [];

	/** @var InvMenu */
	private $menu;

	/** @var array[] */
	private $text = [];

	/** @var array[] */
	private $items = [];

	private FloatingTextParticle $itemName;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$this->menu->setListener(InvMenu::readonly());
		$this->setCrateName();
		$this->items = Main::getInstance()->getConfig()->get($this->type)["items"];
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		parent::writeSaveData($nbt);
		$nbt->setString("CrateType", $this->type);
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		parent::readSaveData($nbt);
		$this->type = $nbt->getString("CrateType");

		$this->scheduleUpdate();
	}

	public function getName() : string{
		return $this->crateName;
	}


	public function viewContent(Player $player){
		$this->menu->getInventory()->clearAll();
		foreach($this->items as $item){
			$i = Item::get($item["id"], $item['meta']);
			$i->setCount($item["amount"]);
			$name = '';
			if(isset($item["name"])){
				$name = $item["name"];
			}else{
				$name = $i->getVanillaName();
			}
			if($item["chance"] <= 10){
				$i->setCustomName(TF::GOLD . $name);
				$i->setLore([TF::GOLD . $item["chance"] . "%"]);
			}else if($item["chance"] <= 25){
				$i->setCustomName(TF::AQUA . $name);
				$i->setLore([TF::AQUA . $item["chance"] . "%"]);
			}else if($item["chance"] <= 50){
				$i->setCustomName(TF::LIGHT_PURPLE . $name);
				$i->setLore([TF::LIGHT_PURPLE . $item["chance"] . "%"]);
			}else{
				$i->setCustomName(TF::GRAY . $name);
				$i->setLore([TF::GRAY . $item["chance"] . "%"]);
			}
			$this->menu->getInventory()->addItem($i);
		}
		$this->menu->send($player);
	}

	public function setCrateName() : void{
		switch($this->type){
			case "common":
				$this->menu->setName(Rarity::COMMON . TF::GOLD . "Crate");
				$this->crateName = Rarity::COMMON . TF::GOLD . "Crate";
				break;
			case "rare":
				$this->menu->setName(Rarity::RARE . TF::GOLD . "Crate");
				$this->crateName = Rarity::RARE . TF::GOLD . "Crate";
				break;
			case "epic":
				$this->menu->setName(Rarity::EPIC . TF::GOLD . "Crate");
				$this->crateName = Rarity::EPIC . TF::GOLD . "Crate";
				break;
			case "legendary":
				$this->menu->setName(Rarity::LEGENDARY . TF::GOLD . "Crate");
				$this->crateName = Rarity::LEGENDARY . TF::GOLD . "Crate";
				break;
		}
	}

	public function onUpdate() : bool{
		if(!$this->closed && ($level = $this->getLevel()) !== null && !$this->in_use){
			foreach($this->text as $key => $text){
				/** @var Player $player */
				$player = $text[0];
				/** @var FloatingTextParticle $particle */
				$particle = $text[1];
				if(!$player->isOnline() || $player->getLevel() !== $this->getLevel()){
					$particle->setInvisible();
					$this->getLevel()->addParticle($particle, [$player]);
					unset($this->text[$key]);
				}
			}
			$this->setParticle();

		}else if($this->in_use){
			$pk = new BlockEventPacket();
			$pk->x = $this->getX();
			$pk->y = $this->getY();
			$pk->z = $this->getZ();
			$pk->eventType = 1;
			$pk->eventData = 2;
			$this->getLevel()->addChunkPacket($this->getX() >> 4, $this->getZ() >> 4, $pk);

			foreach($this->text as $key => $text){
				/** @var Player $player */
				$player = $text[0];
				/** @var FloatingTextParticle $particle */
				$particle = $text[1];
				$particle->setInvisible();
				$this->getLevel()->addParticle($particle, [$player]);
				unset($this->text[$key]);
			}
		}
		return !$this->closed;
	}

	public function close() : void{
		foreach($this->text as $text){
			$text[1]->setInvisible();
			if($text[0]->getLevel()){
				$text[0]->getLevel()->addParticle($text[1], [$text[0]]);
			}
		}
		parent::close();
	}

	public function checkCrate(Player $player, Manager $database, bool $instant = false){
		$uuid = $player->getUniqueId();
		$database->getPlayerID($uuid, function($id) use ($instant, $player, $database){
			$database->getPlayerSpecificKey($id, $this->type, function($keys) use ($instant, $player, $id, $database){
				$nKeys = $keys[$this->type];
				if($nKeys > 0){
					$database->removeOneKey($id, $this->type);
					if(!$this->in_use){
						if($instant){
							$this->openCrateInstant($player);
						}else{
							$this->openCrate($player);
						}

					}else $player->sendMessage(Messages::NEGATIVE_PREFIX . Messages::ALREADY_IN_USE);

				}else $player->sendMessage(Messages::NEGATIVE_PREFIX . Messages::NO_KEY);
			});
		});
	}

	public function openCrateInstant(Player $player){
		$this->currentPlayer = $player;
		$this->in_use = true;
		$items = [];
		$id = 0;
		foreach($this->items as $item){
			$i = Item::get($item["id"], $item['meta']);
			$tempTag = new CompoundTag("", []);
			$tempTag->setString("Crate", "preview");
			$tempTag->setInt("crateID", (int) $id);
			$i->setCompoundTag($tempTag);
			for($j = 0; $j < $item['chance']; $j++){
				array_push($items, $i);
			}
			$id++;
		}
		$pos = $this->asVector3()->add(0.5, 1, 0.5);
		shuffle($items);
		$amount = rand(1, 100);
		$entityId = $this->getLevel()->dropItem($pos, $items[$amount], new Vector3(0, 0.5, 0), 0)->getId();
		Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClearEntity($entityId, $this->getLevel()), 80);
		$this->getLevel()->addSound(new FizzSound($pos));
		$this->onWin($items[$amount]);
	}

	public function openCrate(Player $player){
		$this->currentPlayer = $player;
		$this->in_use = true;
		$items = [];
		$id = 0;
		foreach($this->items as $item){
			$i = Item::get($item["id"], $item['meta']);
			$tempTag = new CompoundTag("", []);
			$tempTag->setString("Crate", "preview");
			$tempTag->setInt("crateID", (int) $id);
			$i->setCompoundTag($tempTag);
			for($j = 0; $j < $item['chance']; $j++){
				array_push($items, $i);
			}
			$id++;
		}
		shuffle($items);
		$pos = $this->asVector3()->add(0.5, 1, 0.5);
		$amount = rand(1, 100);
		$task = new SpinningTask($items, $pos, $amount, $this);
		Main::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, 3, 3);
	}

	public function onWin(Item $item){
		$pk = new BlockEventPacket();
		$pk->x = $this->getX();
		$pk->y = $this->getY();
		$pk->z = $this->getZ();
		$pk->eventType = 1;
		$pk->eventData = 0;
		$this->getLevel()->addChunkPacket($this->getX() >> 4, $this->getZ() >> 4, $pk);


		$index = $item->getNamedTag()->getInt("crateID");
		$won = $this->items[$index];
		if(isset($won["cmd"])){
			Main::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), $won["cmd"]);
		}else{
			$i = Item::get($won['id'], $won['meta']);
			$i->setCount($won['amount']);
			if(isset($won['name'])){
				$i->setCustomName($won['name']);
			}
			$this->currentPlayer->getInventory()->addItem($i);

		}


		$level = $this->getLevel();
		$name = '';
		if(isset($won['name'])) $name = $won['name'];
		else $name = $item->getVanillaName();
		if($won["chance"] <= 10){
			Main::getInstance()->getServer()->broadcastMessage(TF::GOLD . $this->currentPlayer->getName() . "won " . Messages::WON_LEGENDARY . $won['amount'] . "x " . $name);
		}
		$this->itemName = new FloatingTextParticle($this->add(0.5, 0.7, 0.5), $name);
		$level->addParticle($this->itemName, [$this->currentPlayer]);
		$this->in_use = false;
		$task = new TextTask($level, $this->itemName);
		Main::getInstance()->getScheduler()->scheduleDelayedTask($task, 80);
		$this->setParticle();
	}

	private function setParticle(){
		$level = $this->getLevel();
		foreach($level->getPlayers() as $player){
			if(!isset($this->text[$player->getName()])){
				$this->text[$player->getName()] = [$player, new FloatingTextParticle($this->add(0.5, 1.2, 0.5), $this->crateName)];
				$level->addParticle($this->text[$player->getName()][1], [$player]);
			}
		}
	}

}