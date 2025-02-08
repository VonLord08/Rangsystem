<?php

namespace Rangsystem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {
    
    private Config $permissionsConfig;
    private array $defaultGroups = [
        "Spieler" => ["prefix" => "§7Spieler", "suffix" => "", "permissions" => []],
        "Premium" => ["prefix" => "§6Premium", "suffix" => "", "permissions" => []],
        "Friend" => ["prefix" => "§5Friend", "suffix" => "", "permissions" => []],
        "Probe-Team" => ["prefix" => "§3Probe-Team", "suffix" => "§c[Team]", "permissions" => []],
        "Supporter" => ["prefix" => "§2Supporter", "nametag" => "§2Sup", "suffix" => "§c[Team]", "permissions" => []],
        "SrSupporter" => ["prefix" => "§2SrSupporter", "nametag" => "§2SrSup", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator" => ["prefix" => "§2Moderator", "nametag" => "§2Mod", "suffix" => "§c[Team]", "permissions" => []],
        "SrModerator" => ["prefix" => "§2SrModerator", "nametag" => "§2SrMod", "suffix" => "§c[Team]", "permissions" => []],
        "Content" => ["prefix" => "§eContent", "suffix" => "§c[Team]", "permissions" => []],
        "SysDev" => ["prefix" => "§bSysDev", "suffix" => "§c[Team]", "permissions" => []],
        "Admin" => ["prefix" => "§4Admin", "suffix" => "§c[Team]", "permissions" => []],
        "Head-Admin" => ["prefix" => "§4Head-Admin", "nametag" => "§4H-Admin", "suffix" => "§c[Team]", "permissions" => []],
        "Leitung" => ["prefix" => "§4Leitung", "suffix" => "§c[Team]", "permissions" => []]
    ];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML, []);
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        if (!$this->permissionsConfig->exists($name)) {
            $this->permissionsConfig->set($name, ["group" => "Spieler", "permissions" => []]);
            $this->permissionsConfig->save();
        }
        $this->updateNametag($player);
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $data = $this->permissionsConfig->get($name, ["group" => "Spieler", "permissions" => []]);
        $group = $data["group"];
        $prefix = $this->defaultGroups[$group]["prefix"] ?? "";
        
        $message = $event->getMessage();
        $this->getServer()->broadcastMessage("$prefix : $name > §7$message");
        $event->cancel();
    }

    private function updateNametag(Player $player): void {
        $name = $player->getName();
        $data = $this->permissionsConfig->get($name, ["group" => "Spieler", "permissions" => []]);
        $group = $data["group"];
        $nametag = $this->defaultGroups[$group]["nametag"] ?? $this->defaultGroups[$group]["prefix"] ?? "";
        $suffix = $this->defaultGroups[$group]["suffix"] ?? "";
        $player->setNameTag("$nametag : $name $suffix");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) return false;

        switch ($command->getName()) {
            case "setgroup":
                if (count($args) < 2) {
                    $sender->sendMessage("§cBenutzung: /setgroup <Spieler> <Gruppe>");
                    return false;
                }
                $targetName = $args[0];
                $newGroup = $args[1];

                if (!isset($this->defaultGroups[$newGroup])) {
                    $sender->sendMessage("§cDiese Gruppe existiert nicht.");
                    return false;
                }

                $this->permissionsConfig->set($targetName, ["group" => $newGroup, "permissions" => []]);
                $this->permissionsConfig->save();

                $target = $this->getServer()->getPlayerExact($targetName);
                if ($target) {
                    $this->updateNametag($target);
                }

                $sender->sendMessage("§aDie Gruppe von §e$targetName §awurde zu §e$newGroup §ageändert.");
                return true;

            case "addperm":
                if (count($args) < 2) {
                    $sender->sendMessage("§cBenutzung: /addperm <Gruppe> <Permission>");
                    return false;
                }
                $group = $args[0];
                $permission = $args[1];

                if (!isset($this->defaultGroups[$group])) {
                    $sender->sendMessage("§cDiese Gruppe existiert nicht.");
                    return false;
                }

                $permissions = $this->permissionsConfig->getNested("$group.permissions", []);
                if (!in_array($permission, $permissions)) {
                    $permissions[] = $permission;
                    $this->permissionsConfig->setNested("$group.permissions", $permissions);
                    $this->permissionsConfig->save();
                    $sender->sendMessage("§aDie Permission §e$permission §awurde der Gruppe §e$group §ahinzugefügt.");
                } else {
                    $sender->sendMessage("§cDiese Permission existiert bereits in der Gruppe.");
                }
                return true;

            case "removeperm":
                if (count($args) < 2) {
                    $sender->sendMessage("§cBenutzung: /removeperm <Gruppe> <Permission>");
                    return false;
                }
                $group = $args[0];
                $permission = $args[1];

                if (!isset($this->defaultGroups[$group])) {
                    $sender->sendMessage("§cDiese Gruppe existiert nicht.");
                    return false;
                }

                $permissions = $this->permissionsConfig->getNested("$group.permissions", []);
                if (in_array($permission, $permissions)) {
                    $permissions = array_diff($permissions, [$permission]);
                    $this->permissionsConfig->setNested("$group.permissions", array_values($permissions));
                    $this->permissionsConfig->save();
                    $sender->sendMessage("§aDie Permission §e$permission §awurde von der Gruppe §e$group §aentfernt.");
                } else {
                    $sender->sendMessage("§cDiese Permission existiert nicht in der Gruppe.");
                }
                return true;
        }

        return false;
    }
}
