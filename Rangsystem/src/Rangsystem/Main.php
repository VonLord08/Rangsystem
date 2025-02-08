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
            $this->permissionsConfig->set($name, "Spieler");
            $this->permissionsConfig->save();
        }
        $this->updateNametag($player);
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $group = $this->permissionsConfig->get($name, "Spieler");
        $prefix = $this->defaultGroups[$group]["prefix"] ?? "";
        $chatColor = (strpos($group, "Team") !== false) ? "§f" : "§7";

        $this->getServer()->broadcastMessage("$prefix $name > $chatColor" . $event->getMessage());
        $event->cancel();
    }

    private function updateNametag(Player $player): void {
        $name = $player->getName();
        $group = $this->permissionsConfig->get($name, "Spieler");
        $prefix = $this->defaultGroups[$group]["nametag"] ?? $this->defaultGroups[$group]["prefix"] ?? "";
        $suffix = $this->defaultGroups[$group]["suffix"] ?? "";
        $player->setNameTag("$prefix : $name $suffix");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) return false;
        
        switch ($command->getName()) {
            case "setgroup":
                if (count($args) < 2) {
                    $sender->sendMessage("§cNutze: /setgroup <Spieler> <Gruppe>");
                    return false;
                }
                $targetName = $args[0];
                $newGroup = $args[1];

                if (!isset($this->defaultGroups[$newGroup])) {
                    $sender->sendMessage("§cDiese Gruppe existiert nicht.");
                    return false;
                }

                $this->permissionsConfig->set($targetName, $newGroup);
                $this->permissionsConfig->save();

                $target = $this->getServer()->getPlayerExact($targetName);
                if ($target) {
                    $this->updateNametag($target);
                }

                $sender->sendMessage("§aDie Gruppe von §e$targetName §awurde zu §e$newGroup §ageändert.");
                return true;

            case "listgroups":
                $groups = implode(", ", array_keys($this->defaultGroups));
                $sender->sendMessage("§aVerfügbare Gruppen: §e$groups");
                return true;

            case "setprefix":
                if (count($args) < 2) {
                    $sender->sendMessage("§cNutze: /setprefix <Spieler> <Prefix>");
                    return false;
                }
                $targetName = $args[0];
                $newPrefix = $args[1];

                $group = $this->permissionsConfig->get($targetName, "Spieler");
                $this->defaultGroups[$group]["prefix"] = $newPrefix;
                $this->updateNametag($this->getServer()->getPlayerExact($targetName));

                $sender->sendMessage("§aPrefix für §e$targetName §awurde auf §e$newPrefix §agesetzt.");
                return true;

            case "setsuffix":
                if (count($args) < 2) {
                    $sender->sendMessage("§cNutze: /setsuffix <Spieler> <Suffix>");
                    return false;
                }
                $targetName = $args[0];
                $newSuffix = $args[1];

                $group = $this->permissionsConfig->get($targetName, "Spieler");
                $this->defaultGroups[$group]["suffix"] = $newSuffix;
                $this->updateNametag($this->getServer()->getPlayerExact($targetName));

                $sender->sendMessage("§aSuffix für §e$targetName §awurde auf §e$newSuffix §agesetzt.");
                return true;

            case "whois":
                if (count($args) < 1) {
                    $sender->sendMessage("§cNutze: /whois <Spieler>");
                    return false;
                }
                $targetName = $args[0];
                $group = $this->permissionsConfig->get($targetName, "Spieler");
                $sender->sendMessage("§aSpieler: §e$targetName\n§aGruppe: §e$group");
                return true;
        }

        return false;
    }
}
