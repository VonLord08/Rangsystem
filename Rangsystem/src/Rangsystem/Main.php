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
use pocketmine\Server;

class Main extends PluginBase implements Listener {
    
    private Config $permissionsConfig;
    private array $defaultGroups = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML, []);
        $this->loadGroups();
    }

    private function loadGroups(): void {
        $defaultGroups = [
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
        
        $groups = $this->permissionsConfig->get("groups", $defaultGroups);
        $this->permissionsConfig->set("groups", $groups);
        $this->permissionsConfig->save();
        $this->defaultGroups = $groups;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) return false;
        
        switch ($command->getName()) {
            case "setgroup":
                if (!$sender->hasPermission("rangsystem.setgroup")) {
                    $sender->sendMessage("§cDafür hast du keine Berechtigung!");
                    return false;
                }
                if (count($args) < 2) return false;
                $this->setGroup($sender, $args[0], $args[1]);
                return true;
            
            case "listgroups":
                if (!$sender->hasPermission("rangsystem.listgroups")) {
                    $sender->sendMessage("§cDafür hast du keine Berechtigung!");
                    return false;
                }
                $sender->sendMessage("§aVerfügbare Gruppen:");
                foreach (array_keys($this->defaultGroups) as $group) {
                    $sender->sendMessage(" - §e" . $group);
                }
                return true;

            case "whois":
                if (!$sender->hasPermission("rangsystem.whois")) {
                    $sender->sendMessage("§cDafür hast du keine Berechtigung!");
                    return false;
                }
                if (count($args) < 1) return false;
                $this->whoIs($sender, $args[0]);
                return true;

            case "setprefix":
                if (!$sender->hasPermission("rangsystem.setprefix")) {
                    $sender->sendMessage("§cDafür hast du keine Berechtigung!");
                    return false;
                }
                if (count($args) < 2) return false;
                $this->defaultGroups[$args[0]]["prefix"] = $args[1];
                $this->permissionsConfig->set("groups", $this->defaultGroups);
                $this->permissionsConfig->save();
                $sender->sendMessage("§aPrefix für §e" . $args[0] . " §awurde zu §e" . $args[1] . " §ageändert.");
                return true;
            
            case "setsuffix":
                if (!$sender->hasPermission("rangsystem.setsuffix")) {
                    $sender->sendMessage("§cDafür hast du keine Berechtigung!");
                    return false;
                }
                if (count($args) < 2) return false;
                $this->defaultGroups[$args[0]]["suffix"] = $args[1];
                $this->permissionsConfig->set("groups", $this->defaultGroups);
                $this->permissionsConfig->save();
                $sender->sendMessage("§aSuffix für §e" . $args[0] . " §awurde zu §e" . $args[1] . " §ageändert.");
                return true;
        }
        return false;
    }
}
