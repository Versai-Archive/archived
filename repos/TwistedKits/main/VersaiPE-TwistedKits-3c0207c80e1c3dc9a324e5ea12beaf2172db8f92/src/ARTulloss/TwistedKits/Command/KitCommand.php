<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/22/2020
 * Time: 9:51 AM
 */
declare(strict_types=1);

namespace ARTulloss\TwistedKits\Command;
use ARTulloss\TwistedKits\Kit;
use ARTulloss\TwistedKits\Main;
use ARTulloss\TwistedKits\Modes;
use ARTulloss\TwistedKits\Utilities\Utilities;
use function count;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use function explode;
use function gmdate;
use function microtime;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use function str_replace;
use function strtolower;

class KitCommand extends PluginCommand {
    /**
     * KitCommand constructor.
     * @param string $name
     * @param Plugin $owner
     */
    public function __construct(string $name, Plugin $owner) {
        parent::__construct($name, $owner);
        $this->setDescription('Select a kit!');
        $this->setUsage('/kit [kit]');
    }
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if(!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'This command can only be run as a player');
            return;
        }
        if(count($args) > 1)
            throw new InvalidCommandSyntaxException();
        /** @var Main $main */
        $main = $this->getPlugin();
        $config = $main->getConfig();
        $kits = $main->getKits();
        if(isset($args[0])) {
            $this->handleKitArgument($sender, $sender, $args[0], $kits);
            return;
        }
        $format = $config->get('Button Format');
        $options = [];
        $kits = array_values($kits); // Strip keys of kits array
        foreach ($kits as $kit) {
            $options[] = new MenuOption(str_replace('{kit}', $kit->getName(), $format));
        }
        $sender->sendForm(new MenuForm((string) $config->get('Form Title'), '', $options, function (Player $player, int $selection) use ($main, $kits, $config): void{
            $kitName = $kits[$selection]->getName();
            $this->selectKit($player, $kitName);
        }));
    }
    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param string $kitNameArg
     * @param Kit[] $kits
     * @param bool|null $cooldown
     */
    public function handleKitArgument(CommandSender $sender, Player $player, string $kitNameArg, array $kits, $cooldown = true): void{
        $lowerCaseKits = [];
        foreach ($kits as $kitName => $kit)
            $lowerCaseKits[strtolower($kitName)] = $kit;
        $lcKitName = strtolower($kitNameArg);
        if(isset($lowerCaseKits[$lcKitName])) {
            $kit = $lowerCaseKits[$lcKitName];
            $this->selectKit($player, $kit->getName(), $cooldown);
            return;
        }
        $kitNames = array_keys($kits);
        $kitNamesString = "";
        $end = end($kitNames);
        foreach ($kitNames as $kitName) {
            $kitName !== $end ? $kitNamesString .= "$kitName, " : $kitNamesString .= "$kitName.";
        }
        $sender->sendMessage(TextFormat::RED . "Invalid kit type! Available kits are: $kitNamesString");
    }
    /**
     * @param Player $player
     * @param string $kitName
     * @param bool $cooldown
     */
    public function selectKit(Player $player, string $kitName, $cooldown = true): void{
        /** @var Main $main */
        $main = $this->getPlugin();
        $config = $main->getConfig();
        $item = $main->parseItemString($config->getNested('Selection Item.Data'), $kitName);
        $item->setNamedTagEntry(new StringTag('kitSelectionItem', $kitName . '~' . (string) microtime())); // Cheat to make the items not stack
        $inv = $player->getInventory();
        $mode = $main->getEquipMode();
        $kit = $main->getKits()[$kitName];

        $bypass = $player->hasPermission(Main::PERMISSION_PREFIX . 'bypass');

        if($cooldown && $kit->isPlayerOnCooldown($player) && !$bypass) { // Bypass cooldowns
            $timeArray = explode(":", gmdate("d:H:i:s", $kit->getCooldown() + $kit->getPlayerCooldown($player) - time()));
            $timeString = "$timeArray[0] days, $timeArray[1] hours, $timeArray[2] minutes, $timeArray[3] seconds";
            $player->sendMessage(str_replace(['{kit}', '{time}'], [$kitName, $timeString], $config->get('Cooldown Message')));
            return;

    }
        if(!$player->hasPermission(Main::PERMISSION_PREFIX . 'all') && !$player->hasPermission(Main::PERMISSION_PREFIX . "use.$kitName")) {
            $player->sendMessage(TextFormat::RED . "You don't have permission to use $kitName!");
            return;
        }

        switch ($mode) {
            case Modes::MODE_CLEAR_INVENTORY:
                $inv->setContents([$item]);
                $player->getArmorInventory()->clearAll();
                $player->removeAllEffects();
                break;
            case Modes::MODE_ADD_ITEMS:
            case Modes::MODE_ADD_ITEMS_CLEAR_EFFECTS:
                if($inv->canAddItem($item)) {
                    $inv->addItem($item);
                } else {
                    Utilities::createItemEntity($item, $player);
                }
        }
        if($cooldown && !$bypass)
            $kit->updatePlayerLastUsedKit($player);
        $player->sendMessage(str_replace('{kit}', $kitName, $config->getNested('Messages.Kit Selected')));
    }
}