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
        "Spieler" => ["prefix" => "§7Spieler:", "suffix" => "", "permissions" => []],
        "Premium" => ["prefix" => "§6Premium:", "suffix" => "", "permissions" => []],
        "Friend" => ["prefix" => "§5Friend:", "suffix" => "", "permissions" => []],
        "Probe-Team" => ["prefix" => "§3Probe-Team:", "suffix" => "§c[Team]", "permissions" => []],
        "Supporter" => ["prefix" => "§2Supporter:", "suffix" => "§c[Team]", "permissions" => []],
        "Supporter+" => ["prefix" => "§2Supporter§4+:", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator" => ["prefix" => "§2Moderator:", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator+" => ["prefix" => "§2Moderator§4+:", "suffix" => "§c[Team]", "permissions" => []],
        "Content" => ["prefix" => "§eContent:", "suffix" => "§c[Team]", "permissions" => []],
        "SysDev" => ["prefix" => "§bSysDev:", "suffix" => "§c[Team]", "permissions" => []],
        "Admin" => ["prefix" => "§4Admin:", "suffix" => "§c[Team]", "permissions" => []],
        "Head-Admin" => ["prefix" => "§4Head-Admin:", "suffix" => "§c[Team]", "permissions" => []],
        "Leitung" => ["prefix" => "§4Leitung:", "suffix" => "§c[Team]", "permissions" => []]
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

        if (!isset($players[$name])) {
            $players[$name] = ["group" => "Spieler"];
            $this->permissionsConfig->set("players", $players);
            $this->permissionsConfig->save();
        }

        $playerGroup = $players[$name]["group"];

        if (isset($groups[$playerGroup])) {
            $prefix = $groups[$playerGroup]["prefix"];
            $suffix = $groups[$playerGroup]["suffix"];

            // Setze DisplayName für Chat
            $player->setDisplayName("$prefix $name >");

            // Setze NameTag (über Kopf)
            $player->setNameTag("$prefix $name $suffix");
        }
    }

    public function onPlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $message = $event->getMessage();

        $players = $this->permissionsConfig->get("players", []);
        $groups = $this->permissionsConfig->get("groups", []);
        $playerGroup = $players[$name]["group"] ?? "Spieler";

        $prefix = $groups[$playerGroup]["prefix"] ?? "";
        $chatColor = (isset($groups[$playerGroup]) && strpos($groups[$playerGroup]["suffix"], "§c[Team]") !== false) ? "§f" : "§7";

        // Setzt das neue Chat-Format
        $event->setFormat("$prefix $name > $chatColor$message");
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

        return false;
    }
}
