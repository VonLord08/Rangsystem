<?php

namespace Rangsystem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
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
        "Supporter" => ["prefix" => "§2Supporter", "suffix" => "§c[Team]", "permissions" => []],
        "Supporter+" => ["prefix" => "§2Supporter§4+", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator" => ["prefix" => "§2Moderator", "suffix" => "§c[Team]", "permissions" => []],
        "Moderator+" => ["prefix" => "§2Moderator§4+", "suffix" => "§c[Team]", "permissions" => []],
        "Content" => ["prefix" => "§eContent", "suffix" => "§c[Team]", "permissions" => []],
        "SysDev" => ["prefix" => "§bSysDev", "suffix" => "§c[Team]", "permissions" => []],
        "Admin" => ["prefix" => "§4Admin", "suffix" => "§c[Team]", "permissions" => []],
        "Head-Admin" => ["prefix" => "§4Head-Admin", "suffix" => "§c[Team]", "permissions" => []],
        "Leitung" => ["prefix" => "§4Leitung", "suffix" => "§c[Team]", "permissions" => []]
    ];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML, ["players" => []]);
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $players = $this->permissionsConfig->get("players", []);

        if (!isset($players[$name])) {
            $players[$name] = "Spieler";
            $this->permissionsConfig->set("players", $players);
            $this->permissionsConfig->save();
        }

        $group = $players[$name];
        $prefix = $this->defaultGroups[$group]["prefix"];
        $suffix = $this->defaultGroups[$group]["suffix"];
        
        // Set Nametag (Über Kopf)
        $player->setNameTag("$prefix : $name $suffix");
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $players = $this->permissionsConfig->get("players", []);
        $group = $players[$name] ?? "Spieler";
        $prefix = $this->defaultGroups[$group]["prefix"];
        
        // Nachrichtenfarbe
        $chatColor = in_array($group, ["Probe-Team", "Supporter", "Supporter+", "Moderator", "Moderator+", "Content", "SysDev", "Admin", "Head-Admin", "Leitung"])
            ? "§f"
            : "§7";
        
        $message = $event->getMessage();
        $event->setFormat("$prefix : $name > $chatColor$message");
    }
}
