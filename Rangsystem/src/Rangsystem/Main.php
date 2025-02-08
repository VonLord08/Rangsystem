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
        "Head-Admin" => ["prefix" => "§4H-Admin", "suffix" => "§c[Team]", "permissions" => []],
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
        $suffix = $this->defaultGroups[$group]["suffix"] ?? "";

        $chatColor = (strpos($group, "Team") !== false) ? "§f" : "§7";
        $message = "$prefix : $name > $chatColor" . $event->getMessage();
        
        Server::getInstance()->broadcastMessage($message);
        $event->cancel();
    }

    private function updateNametag(Player $player): void {
        $name = $player->getName();
        $group = $this->permissionsConfig->get($name, "Spieler");
        $nametag = $this->defaultGroups[$group]["nametag"] ?? $this->defaultGroups[$group]["prefix"];
        $suffix = $this->defaultGroups[$group]["suffix"] ?? "";
        $player->setNameTag("$nametag : $name $suffix");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) return false;
        
        switch ($command->getName()) {
            case "setgroup":
                if (count($args) < 2) return false;
                $this->setGroup($sender, $args[0], $args[1]);
                return true;
            
            case "listgroups":
                $sender->sendMessage("§aVerfügbare Gruppen:");
                foreach (array_keys($this->defaultGroups) as $group) {
                    $sender->sendMessage(" - §e" . $group);
                }
                return true;

            case "whois":
                if (count($args) < 1) return false;
                $this->whoIs($sender, $args[0]);
                return true;
        }
        return false;
    }

    private function setGroup(CommandSender $sender, string $playerName, string $newGroup): void {
        if (!isset($this->defaultGroups[$newGroup])) {
            $sender->sendMessage("§cDiese Gruppe existiert nicht.");
            return;
        }
        $this->permissionsConfig->set($playerName, $newGroup);
        $this->permissionsConfig->save();

        $target = $this->getServer()->getPlayerExact($playerName);
        if ($target) {
            $this->updateNametag($target);
        }

        $sender->sendMessage("§aDie Gruppe von §e$playerName §awurde zu §e$newGroup §ageändert.");
    }

    private function whoIs(CommandSender $sender, string $playerName): void {
        $group = $this->permissionsConfig->get($playerName, "Unbekannt");
        $sender->sendMessage("§eName: §f$playerName");
        $sender->sendMessage("§eGruppe: §f$group");
    }
}
