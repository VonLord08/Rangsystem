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
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener {

    private Config $permissionsConfig;
    private array $defaultGroups = [
        "Spieler" => ["prefix" => "§7Spieler", "suffix" => "", "chatColor" => "§7"],
        "Premium" => ["prefix" => "§6Premium", "suffix" => "", "chatColor" => "§7"],
        "Friend" => ["prefix" => "§5Friend", "suffix" => "", "chatColor" => "§7"],
        "Probe-Team" => ["prefix" => "§3Probe-Team", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Supporter" => ["prefix" => "§2Supporter", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Supporter+" => ["prefix" => "§2Supporter§4+", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Moderator" => ["prefix" => "§2Moderator", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Moderator+" => ["prefix" => "§2Moderator§4+", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Content" => ["prefix" => "§eContent", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "SysDev" => ["prefix" => "§bSysDev", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Admin" => ["prefix" => "§4Admin", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Head-Admin" => ["prefix" => "§4Head-Admin", "suffix" => "§c[Team]", "chatColor" => "§f"],
        "Leitung" => ["prefix" => "§4Leitung", "suffix" => "§c[Team]", "chatColor" => "§f"]
    ];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML);
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
        $prefix = $this->defaultGroups[$group]["prefix"] ?? "§7Spieler";
        $chatColor = $this->defaultGroups[$group]["chatColor"] ?? "§7";
        $message = $event->getMessage();
        
        $event->setFormat("$prefix : $name > $chatColor$message");
    }

    private function updateNametag(Player $player): void {
        $name = $player->getName();
        $group = $this->permissionsConfig->get($name, "Spieler");
        $prefix = $this->defaultGroups[$group]["prefix"] ?? "§7Spieler";
        $suffix = $this->defaultGroups[$group]["suffix"] ?? "";
        $player->setNameTag("$prefix : $name $suffix");
    }
}
