<?php


namespace Versai\Sumo\Command;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use Versai\Sumo\Sumo;

abstract class BaseSubCommand implements PluginIdentifiableCommand
{
    /**
     * @var Sumo
     */
    private Sumo $sumo;

    private string $name;

    private ?array $aliases = []; # [0 => "help"]

    protected ?string $description = "";

    protected ?string $usageMessage;

    private ?string $permission = null;

    private ?string $permissionMessage = null;

    private SumoTournamentCommand $base;


    public function __construct(Sumo $sumo, SumoTournamentCommand $command, string $name, string $description = "", string $usageMessage = null, array $aliases = []) {
        $this->sumo = $sumo;
        $this->name = $name;
        $this->setDescription($description);
        $this->usageMessage = $usageMessage ?? ("/" . $name);
        $this->base = $command;
        $this->setAliases($aliases);
    }

    public function onRun(CommandSender $sender, array $args): void
    {
        if ($this->permission !== null && !$this->testPermission($sender)) return;
        $this->execute($sender, $args);
    }

    abstract public function execute(CommandSender $sender, array $args): void;


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

    public function testPermission(CommandSender $target): bool
    {
        if ($this->testPermissionSilent($target)) {
            return true;
        }

        if ($this->permissionMessage === null) {
            $target->sendMessage($target->getServer()->getLanguage()->translateString(TextFormat::RED . "Yikes! You're missing some permissions"));
        } elseif ($this->permissionMessage !== "") {
            $target->sendMessage(str_replace("<permission>", $this->permission, $this->permissionMessage));
        }

        return false;
    }

    /**
     * @return Sumo
     */
    public function getPlugin(): Plugin
    {
        return $this->sumo;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array|null
     */
    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    /**
     * @param array|null $aliases
     */
    public function setAliases(?array $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getPermission(): ?string
    {
        return $this->permission;
    }

    /**
     * @param string|null $permission
     */
    public function setPermission(?string $permission): void
    {
        $this->permission = $permission;
    }

    /**
     * @return string|null
     */
    public function getPermissionMessage(): ?string
    {
        return $this->permissionMessage;
    }

    /**
     * @param string|null $permissionMessage
     */
    public function setPermissionMessage(?string $permissionMessage): void
    {
        $this->permissionMessage = $permissionMessage;
    }

    /**
     * @return SumoTournamentCommand
     */
    public function getBase(): SumoTournamentCommand
    {
        return $this->base;
    }
}