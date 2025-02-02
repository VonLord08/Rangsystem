<?php

namespace Rangsystem;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {
    
    private Config $permissionsConfig;
    
    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML);
    }
    
    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        
        // Standardwerte setzen, falls keine Gruppe gefunden wird
        $group = $this->permissionsConfig->get("players", [])[$name] ?? "Spieler";
        $groups = $this->permissionsConfig->get("groups", []);
        
        $prefix = $groups[$group]["prefix"] ?? "§7Spieler";
        $suffix = $groups[$group]["suffix"] ?? "";
        
        // Farben für Chat-Nachricht setzen
        $chatColor = (strpos($group, "Moderator+") !== false || strpos($group, "Supporter+") !== false) ? "§2" : "§f";
        
        // **Alternative Methode ohne setFormat()**
        $format = "$prefix : $name > $chatColor" . $event->getMessage();
        
        // Broadcast des formatierten Chat-Events
        $event->setRecipients($this->getServer()->getOnlinePlayers());
        $event->setMessage($format);
    }
}
