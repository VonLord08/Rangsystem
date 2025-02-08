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
        "Spieler" => ["prefix" => "§7Spieler", "nametag" => "§7Spieler", "suffix" => "", "permissions" => []],
        "Premium" => ["prefix" => "§6Premium", "nametag" => "§6Premium", "suffix" => "", "permissions" => []],
        "Friend" => ["prefix" => "§5Friend", "nametag" => "§5Friend", "suffix" => "", "permissions" => []],
        "Probe-Team" => ["prefix" => "§3Probe-Team", "nametag" => "§3Probe-Team", "suffix" => "§c[Team]", "permissions" => []],
        "Supporter" => ["prefix" => "§2Supporter", "nametag" => "§2Sup", "suffix" => "§c[Team]", "permissions" => []],
        "SrSupporter" => ["prefix" => "§2SrSupporter", "nametag" => "§2SrSup", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator" => ["prefix" => "§2Moderator", "nametag" => "§2Mod", "suffix" => "§c[Team]", "permissions" => []],
        "SrModerator" => ["prefix" => "§2SrModerator", "nametag" => "§2SrMod", "suffix" => "§c[Team]", "permissions" => []],
        "Content" => ["prefix" => "§eContent", "nametag" => "§eContent", "suffix" => "§c[Team]", "permissions" => []],
        "SysDev" => ["prefix" => "§bSysDev", "nametag" => "§bSysDev", "suffix" => "§c[Team]", "permissions" => []],
        "Admin" => ["prefix" => "§4Admin", "nametag" => "§4Admin", "suffix" => "§c[Team]", "permissions" => []],
        "Head-Admin" => ["prefix" => "§4Head-Admin", "nametag" => "§4H-Admin", "suffix" => "§c[Team]", "permissions" => []],
        "Leitung" => ["prefix" => "§4Leitung", "nametag" => "§4Leitung", "suffix" => "§c[Team]", "permissions" => []]
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
        $event->setFormat("$prefix $name > $chatColor" . $event->getMessage());
    }

    private function updateNametag(Player $player): void {
        $name = $player->getName();
        $group = $this->permissionsConfig->get($name, "Spieler");
        $nametag = $this->defaultGroups[$group]["nametag"] ?? "";
        $suffix = $this->permissionsConfig->getNested("$name.suffix", "");
        $player->setNameTag("$nametag : $name $suffix");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "listgroups") {
            $sender->sendMessage("§aVerfügbare Gruppen: §e" . implode(", ", array_keys($this->defaultGroups)));
            return true;
        }

        if (!$sender instanceof Player) return false;

        $name = $sender->getName();

        if ($command->getName() === "setgroup" && count($args) >= 2) {
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
        }

        if ($command->getName() === "setprefix" && count($args) >= 2) {
            $targetName = $args[0];
            $newPrefix = implode(" ", array_slice($args, 1));

            $this->permissionsConfig->setNested("$targetName.prefix", $newPrefix);
            $this->permissionsConfig->save();

            $target = $this->getServer()->getPlayerExact($targetName);
            if ($target) {
                $this->updateNametag($target);
            }

            $sender->sendMessage("§aPrefix für §e$targetName §awurde zu §e$newPrefix §ageändert.");
            return true;
        }

        if ($command->getName() === "setsuffix" && count($args) >= 2) {
            $targetName = $args[0];
            $newSuffix = implode(" ", array_slice($args, 1));

            $this->permissionsConfig->setNested("$targetName.suffix", $newSuffix);
            $this->permissionsConfig->save();

            $target = $this->getServer()->getPlayerExact($targetName);
            if ($target) {
                $this->updateNametag($target);
            }

            $sender->sendMessage("§aSuffix für §e$targetName §awurde zu §e$newSuffix §ageändert.");
            return true;
        }

        if ($command->getName() === "whois" && count($args) >= 1) {
            $targetName = $args[0];

            if (!$this->permissionsConfig->exists($targetName)) {
                $sender->sendMessage("§cSpieler nicht gefunden.");
                return false;
            }

            $group = $this->permissionsConfig->get($targetName, "Spieler");
            $prefix = $this->permissionsConfig->getNested("$targetName.prefix", $this->defaultGroups[$group]["prefix"] ?? "");
            $suffix = $this->permissionsConfig->getNested("$targetName.suffix", "");

            $sender->sendMessage("§aSpieler: §e$targetName\n§aGruppe: §e$group\n§aPrefix: §e$prefix\n§aSuffix: §e$suffix");
            return true;
        }

        return false;
    }
}
