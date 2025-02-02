<?php

namespace Rangsystem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\event\server\ServerCommandEvent;

class Main extends PluginBase implements Listener {

    private Config $permissionsConfig;
    private array $defaultGroups = [
        "Spieler" => ["prefix" => "§7[Spieler] ", "suffix" => "", "permissions" => []],
        "Premium" => ["prefix" => "§6[Premium] ", "suffix" => "", "permissions" => []],
        "Friend" => ["prefix" => "§d[Friend] ", "suffix" => "", "permissions" => []],
        "Probe-Team" => ["prefix" => "§b[Probe-Team] ", "suffix" => " §b[Team]", "permissions" => []],
        "Supporter" => ["prefix" => "§1[Supporter] ", "suffix" => " §1[Team]", "permissions" => []],
        "Supporter+" => ["prefix" => "§3[Supporter+] ", "suffix" => " §3[Team]", "permissions" => []],
        "Moderator" => ["prefix" => "§2[Mod] ", "suffix" => " §2[Team]", "permissions" => []],
        "Moderator+" => ["prefix" => "§a[Mod+] ", "suffix" => " §a[Team]", "permissions" => []],
        "Content" => ["prefix" => "§5[Content] ", "suffix" => " §5[Team]", "permissions" => []],
        "SysDev" => ["prefix" => "§9[SysDev] ", "suffix" => " §9[Team]", "permissions" => []],
        "Admin" => ["prefix" => "§4[Admin] ", "suffix" => " §4[Team]", "permissions" => []],
        "Head-Admin" => ["prefix" => "§c[Head-Admin] ", "suffix" => " §c[Team]", "permissions" => []],
        "Leitung" => ["prefix" => "§e[Leitung] ", "suffix" => " §e[Team]", "permissions" => []]
    ];

    public function onEnable(): void {
        $this->getLogger()->info("§aRangsystem wurde aktiviert!");
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML);

        if (!$this->permissionsConfig->exists("groups")) {
            $this->permissionsConfig->set("groups", $this->defaultGroups);
            $this->permissionsConfig->save();
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        $players = $this->permissionsConfig->get("players", []);
        $groups = $this->permissionsConfig->get("groups", []);

        if (!isset($players[$name])) {
            $players[$name] = ["group" => "Spieler"];
            $this->permissionsConfig->set("players", $players);
            $this->permissionsConfig->save();
        }

        $playerGroup = $players[$name]["group"];

        if (isset($groups[$playerGroup])) {
            $prefix = $groups[$playerGroup]["prefix"];
            $suffix = $groups[$playerGroup]["suffix"];
            $player->setNameTag("$prefix§r$name §r$suffix");
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
            }

            $sender->sendMessage("§aDie Permission $permission wurde zur Gruppe $groupName hinzugefügt.");
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

            if (in_array($permission, $groups[$groupName]["permissions"])) {
                $groups[$groupName]["permissions"] = array_diff($groups[$groupName]["permissions"], [$permission]);
                $this->permissionsConfig->set("groups", $groups);
                $this->permissionsConfig->save();
            }

            $sender->sendMessage("§aDie Permission $permission wurde von der Gruppe $groupName entfernt.");
            return true;
        }

        return false;
    }
}
