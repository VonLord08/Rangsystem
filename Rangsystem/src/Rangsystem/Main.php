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
        "Spieler" => ["prefix" => "§7Spieler", "suffix" => "", "nametag" => "§7Spieler"],
        "Premium" => ["prefix" => "§6Premium", "suffix" => "", "nametag" => "§6Premium"],
        "Friend" => ["prefix" => "§5Friend", "suffix" => "", "nametag" => "§5Friend"],
        "Probe-Team" => ["prefix" => "§3Probe-Team", "suffix" => "§c[Team]", "nametag" => "§3Probe-Team"],
        "Supporter" => ["prefix" => "§2Supporter", "suffix" => "§c[Team]", "nametag" => "§2Sup"],
        "SrSupporter" => ["prefix" => "§2SrSupporter", "suffix" => "§c[Team]", "nametag" => "§2SrSup"],
        "Moderator" => ["prefix" => "§2Moderator", "suffix" => "§c[Team]", "nametag" => "§2Mod"],
        "SrModerator" => ["prefix" => "§2SrModerator", "suffix" => "§c[Team]", "nametag" => "§2SrMod"],
        "Content" => ["prefix" => "§eContent", "suffix" => "§c[Team]", "nametag" => "§eContent"],
        "SysDev" => ["prefix" => "§bSysDev", "suffix" => "§c[Team]", "nametag" => "§bSysDev"],
        "Admin" => ["prefix" => "§4Admin", "suffix" => "§c[Team]", "nametag" => "§4Admin"],
        "Head-Admin" => ["prefix" => "§4Head-Admin", "suffix" => "§c[Team]", "nametag" => "§4H-Admin"],
        "Leitung" => ["prefix" => "§4Leitung", "suffix" => "§c[Team]", "nametag" => "§4Leitung"]
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

        // Senden der Chat-Nachricht mit Broadcast
        $this->getServer()->broadcastMessage("$prefix §r$name > $chatColor" . $event->getMessage());

        // Chat-Nachricht verhindern (damit keine doppelte Nachricht kommt)
        $event->cancel();
    }

    private function updateNametag(Player $player): void {
        $name = $player->getName();
        $group = $this->permissionsConfig->get($name, "Spieler");
        $nametag = $this->defaultGroups[$group]["nametag"] ?? "";
        $suffix = $this->defaultGroups[$group]["suffix"] ?? "";
        $player->setNameTag("$nametag : $name $suffix");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) return false;

        $name = $sender->getName();

        switch ($command->getName()) {
            case "setgroup":
                if (count($args) < 2) {
                    $sender->sendMessage("§cVerwendung: /setgroup <Spieler> <Gruppe>");
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
                    $sender->sendMessage("§cVerwendung: /setprefix <Spieler> <Prefix>");
                    return false;
                }

                $targetName = $args[0];
                $newPrefix = $args[1];

                $this->defaultGroups[$targetName]["prefix"] = $newPrefix;
                $sender->sendMessage("§aPrefix für §e$targetName §awurde zu §e$newPrefix §ageändert.");
                return true;

            case "setsuffix":
                if (count($args) < 2) {
                    $sender->sendMessage("§cVerwendung: /setsuffix <Spieler> <Suffix>");
                    return false;
                }

                $targetName = $args[0];
                $newSuffix = $args[1];

                $this->defaultGroups[$targetName]["suffix"] = $newSuffix;
                $sender->sendMessage("§aSuffix für §e$targetName §awurde zu §e$newSuffix §ageändert.");
                return true;

            case "whois":
                if (count($args) < 1) {
                    $sender->sendMessage("§cVerwendung: /whois <Spieler>");
                    return false;
                }

                $targetName = $args[0];
                $group = $this->permissionsConfig->get($targetName, "Spieler");
                $prefix = $this->defaultGroups[$group]["prefix"] ?? "";
                $suffix = $this->defaultGroups[$group]["suffix"] ?? "";

                $sender->sendMessage("§aSpieler: §e$targetName");
                $sender->sendMessage("§aGruppe: §e$group");
                $sender->sendMessage("§aPrefix: §e$prefix");
                $sender->sendMessage("§aSuffix: §e$suffix");
                return true;
        }

        return false;
    }
}
