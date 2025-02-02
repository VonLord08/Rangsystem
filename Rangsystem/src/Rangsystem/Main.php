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
        "Spieler" => ["prefix" => "§7[Spieler]", "suffix" => "", "permissions" => []],
        "Premium" => ["prefix" => "§6[Premium]", "suffix" => "", "permissions" => []],
        "Friend" => ["prefix" => "§5[Friend]", "suffix" => "", "permissions" => []],
        "Probe-Team" => ["prefix" => "§3[Probe-Team]", "suffix" => "§c", "permissions" => []],
        "Supporter" => ["prefix" => "§2[Supporter]", "suffix" => "§c", "permissions" => []],
        "Supporter+" => ["prefix" => "§2[Supporter§4+]", "suffix" => "§c", "permissions" => []],
        "Moderator" => ["prefix" => "§2[Moderator]", "suffix" => "§c", "permissions" => []],
        "Moderator+" => ["prefix" => "§2[Moderator§4+]", "suffix" => "§c", "permissions" => []],
        "Content" => ["prefix" => "§e[Content]", "suffix" => "§c", "permissions" => []],
        "SysDev" => ["prefix" => "§b[SysDev]", "suffix" => "§c", "permissions" => []],
        "Admin" => ["prefix" => "§4[Admin]", "suffix" => "§c", "permissions" => []],
        "Head-Admin" => ["prefix" => "§4[Head-Admin]", "suffix" => "§c", "permissions" => []],
        "Leitung" => ["prefix" => "§4[Leitung]", "suffix" => "§c", "permissions" => []]
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
        return false;
    }
}

        return false;
    }
}

