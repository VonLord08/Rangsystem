<?php

namespace Rangsystem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    private Config $permissionsConfig;
    private array $defaultGroups = [
        "Spieler" => ["prefix" => "§7Spieler :", "suffix" => "", "permissions" => []],
        "Premium" => ["prefix" => "§6Premium :", "suffix" => "", "permissions" => []],
        "Friend" => ["prefix" => "§5Friend :", "suffix" => "", "permissions" => []],
        "Probe-Team" => ["prefix" => "§3Probe-Team :", "suffix" => "§c[Team]", "permissions" => []],
        "Supporter" => ["prefix" => "§2Supporter :", "suffix" => "§c[Team]", "permissions" => []],
        "Supporter+" => ["prefix" => "§2Supporter§4+ :", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator" => ["prefix" => "§2Moderator :", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator+" => ["prefix" => "§2Moderator§4+ :", "suffix" => "§c[Team]", "permissions" => []],
        "Content" => ["prefix" => "§eContent :", "suffix" => "§c[Team]", "permissions" => []],
        "SysDev" => ["prefix" => "§bSysDev :", "suffix" => "§c[Team]", "permissions" => []],
        "Admin" => ["prefix" => "§4Admin :", "suffix" => "§c[Team]", "permissions" => []],
        "Head-Admin" => ["prefix" => "§4Head-Admin :", "suffix" => "§c[Team]", "permissions" => []],
        "Leitung" => ["prefix" => "§4Leitung :", "suffix" => "§c[Team]", "permissions" => []]
    ];

    public function onEnable(): void {
        $this->getLogger()->info("Rangsystem wurde aktiviert!");
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML);
        
        if (!$this->permissionsConfig->exists("groups")) {
            $this->permissionsConfig->set("groups", $this->defaultGroups);
        }

        if (!$this->permissionsConfig->exists("players")) {
            $this->permissionsConfig->set("players", []);
        }

        $this->permissionsConfig->save();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        $groups = $this->permissionsConfig->get("groups", []);
        $players = $this->permissionsConfig->get("players", []);

        // Standard-Gruppe "Spieler" zuweisen, wenn der Spieler keine Gruppe hat
        if (!isset($players[$name])) {
            $players[$name] = ["group" => "Spieler"];
            $this->permissionsConfig->set("players", $players);
            $this->permissionsConfig->save();
        }

        $playerGroup = $players[$name]["group"];

        if (isset($groups[$playerGroup])) {
            $prefix = $groups[$playerGroup]["prefix"];
            $suffix = $groups[$playerGroup]["suffix"];
            $player->setDisplayName("$prefix $name");
            $player->setNameTag("$prefix $name $suffix");
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "setgroup") {
            if (count($args) < 2) {
                $sender->sendMessage("§cBenutzung: /setgroup <Spieler> <Gruppe>");
                return false;
            }

            $playerName = $args[0];
            $groupName = $args[1];
            $groups = $this->permissionsConfig->get("groups", []);

            if (!isset($groups[$groupName])) {
                $sender->sendMessage("§cDie Gruppe $groupName existiert nicht.");
                return false;
            }

            $players = $this->permissionsConfig->get("players", []);
            $players[$playerName] = ["group" => $groupName];
            $this->permissionsConfig->set("players", $players);
            $this->permissionsConfig->save();

            $sender->sendMessage("§aDer Spieler $playerName wurde der Gruppe $groupName zugewiesen.");
            return true;
        }

        if ($command->getName() === "addperm") {
            if (count($args) < 2) {
                $sender->sendMessage("§cBenutzung: /addperm <Gruppe> <Permission>");
                return false;
            }

            $groupName = $args[0];
            $permission = $args[1];

            $groups = $this->permissionsConfig->get("groups", []);
            if (!isset($groups[$groupName])) {
                $sender->sendMessage("§cDie Gruppe $groupName existiert nicht.");
                return false;
            }

            if (!in_array($permission, $groups[$groupName]["permissions"])) {
                $groups[$groupName]["permissions"][] = $permission;
                $this->permissionsConfig->set("groups", $groups);
                $this->permissionsConfig->save();
                $sender->sendMessage("§aDie Permission $permission wurde zur Gruppe $groupName hinzugefügt.");
            } else {
                $sender->sendMessage("§cDie Gruppe $groupName hat bereits die Permission $permission.");
            }

            return true;
        }

        if ($command->getName() === "removeperm") {
            if (count($args) < 2) {
                $sender->sendMessage("§cBenutzung: /removeperm <Gruppe> <Permission>");
                return false;
            }

            $groupName = $args[0];
            $permission = $args[1];

            $groups = $this->permissionsConfig->get("groups", []);
            if (!isset($groups[$groupName])) {
                $sender->sendMessage("§cDie Gruppe $groupName existiert nicht.");
                return false;
            }

            if (($key = array_search($permission, $groups[$groupName]["permissions"])) !== false) {
                unset($groups[$groupName]["permissions"][$key]);
                $this->permissionsConfig->set("groups", $groups);
                $this->permissionsConfig->save();
                $sender->sendMessage("§aDie Permission $permission wurde von der Gruppe $groupName entfernt.");
            } else {
                $sender->sendMessage("§cDie Gruppe $groupName hat die Permission $permission nicht.");
            }

            return true;
        }

        return false;
    }
}
