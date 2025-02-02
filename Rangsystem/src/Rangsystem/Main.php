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
        "Spieler" => ["prefix" => "\u00A7f[Spieler]", "suffix" => "", "permissions" => []],
        "Premium" => ["prefix" => "\u00A76[Premium]", "suffix" => "", "permissions" => []],
        "Friend" => ["prefix" => "\u00A7d[Friend]", "suffix" => "", "permissions" => []],
        "Probe-Team" => ["prefix" => "\u00A79[Probe]", "suffix" => "\u00A77[Team]", "permissions" => []],
        "Supporter" => ["prefix" => "\u00A71[Supporter]", "suffix" => "\u00A77[Team]", "permissions" => []],
        "Moderator" => ["prefix" => "\u00A7c[Mod]", "suffix" => "\u00A77[Team]", "permissions" => []],
        "Admin" => ["prefix" => "\u00A74[Admin]", "suffix" => "\u00A77[Team]", "permissions" => []],
        "Leitung" => ["prefix" => "\u00A7e[Leitung]", "suffix" => "\u00A77[Team]", "permissions" => []]
    ];

    public function onEnable(): void {
        $this->getLogger()->info("Rangsystem wurde aktiviert!");
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

        $groups = $this->permissionsConfig->get("groups", []);
        $players = $this->permissionsConfig->get("players", []);

        if (!isset($players[$name])) {
            $players[$name] = ["group" => "Spieler"];
            $this->permissionsConfig->set("players", $players);
            $this->permissionsConfig->save();
        }

        $playerGroup = $players[$name]["group"] ?? "Spieler";

        if (isset($groups[$playerGroup])) {
            $prefix = $groups[$playerGroup]["prefix"];
            $suffix = $groups[$playerGroup]["suffix"];
            $player->setDisplayName("$prefix $name");
            $player->setNameTag("$prefix $name $suffix");
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Dieser Befehl kann nur im Spiel verwendet werden.");
            return false;
        }

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

