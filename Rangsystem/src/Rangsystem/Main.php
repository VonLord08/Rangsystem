<?php

namespace Rangsystem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\player\Player;
use pocketmine\utils\Config;

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
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML, []);
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $group = $this->getPlayerGroup($player);
        
        $prefix = $this->defaultGroups[$group]["prefix"];
        $suffix = $this->defaultGroups[$group]["suffix"];

        $chatColor = in_array($group, ["Spieler", "Premium", "Friend"]) ? "§7" : "§f";
        
        // **Neues Chat-Format**
        $formattedMessage = "$prefix : $name > $chatColor" . $event->getMessage();
        
        $event->setMessage($formattedMessage);
    }

    private function getPlayerGroup(Player $player): string {
        $name = $player->getName();
        $permissions = $this->permissionsConfig->get("players", []);
        return $permissions[$name]["group"] ?? "Spieler";
    }
}
