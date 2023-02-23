<?php


namespace Martin\GameAPI\Command;


use Martin\GameAPI\GamePlugin;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\utils\CommandException;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\TextFormat;
use function explode;
use function str_replace;

abstract class BaseGameSubCommand implements PluginIdentifiableCommand
{

    public ?TimingsHandler $timings = null;
    protected string $description = "";
    private string $name;
    /** @var string[] */
    private array $aliases = [];
    private ?string $permission = null;
    private ?string $permissionMessage = null;
    private GamePlugin $plugin;

    /**
     * @param string[] $aliases
     */
    public function __construct(GamePlugin $plugin, string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->name = $name;
        $this->plugin = $plugin;
        $this->setDescription($description);
        $this->setAliases($aliases);
        $this->prepare();
    }

    abstract protected function prepare(): void;

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @return void
     * @throws CommandException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermission($sender)) {
            return;
        }

        $this->onRun($sender, $args);
    }

    public function testPermission(CommandSender $target): bool
    {
        if ($this->testPermissionSilent($target)) {
            return true;
        }

        if ($this->permissionMessage === null) {
            $target->sendMessage($target->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));
        } elseif ($this->permissionMessage !== "") {
            $target->sendMessage(str_replace("<permission>", $this->permission, $this->permissionMessage));
        }

        return false;
    }

    public function testPermissionSilent(CommandSender $target): bool
    {
        if ($this->permission === null or $this->permission === "") {
            return true;
        }

        foreach (explode(";", $this->permission) as $permission) {
            if ($target->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CommandSender $sender
     * @param string[] $args
     * @return void
     * @throws CommandException
     */
    abstract public function onRun(CommandSender $sender, array $args): void;

    public function getName(): string
    {
        return $this->name;
    }

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    public function setPermission(string $permission = null): void
    {
        $this->permission = $permission;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param string[] $aliases
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    public function getPermissionMessage(): ?string
    {
        return $this->permissionMessage;
    }

    public function setPermissionMessage(string $permissionMessage): void
    {
        $this->permissionMessage = $permissionMessage;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getPlugin(): GamePlugin
    {
        return $this->plugin;
    }
}