<?php

declare(strict_types=1);

namespace Ranksystem;

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

        // Standardgruppen speichern, falls nicht vorhanden
        foreach ($this->defaultGroups as $group => $data) {
            if (!$this->permissionsConfig->exists($group)) {
                $this->permissionsConfig->set($group, $data);
            }
        }
        $this->permissionsConfig->save();
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();

        // Falls der Spieler keine Gruppe hat, zu "Spieler" hinzufügen
        if (!$this->permissionsConfig->exists($name)) {
            $this->permissionsConfig->set($name, "Spieler");
            $this->permissionsConfig->save();
        }

        // Nametag setzen
        $group = $this->permissionsConfig->get($name);
        $prefix = $this->getGroupPrefix($group);
        $suffix = $this->getGroupSuffix($group);
        $player->setNameTag("$prefix : $name $suffix");
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $message = $event->getMessage();

        $group = $this->permissionsConfig->get($name);
        $prefix = $this->getGroupPrefix($group);

        // Chatnachricht Farbe anpassen
        $chatColor = $this->isTeamRank($group) ? "§f" : "§7";

        // Chatformat setzen
        $event->setFormat("$prefix : $name > $chatColor$message");
    }

    private function getGroupPrefix(string $group): string {
        return $this->defaultGroups[$group]["prefix"] ?? "§7Spieler";
    }

    private function getGroupSuffix(string $group): string {
        return $this->defaultGroups[$group]["suffix"] ?? "";
    }

    private function isTeamRank(string $group): bool {
        return in_array($group, [
            "Probe-Team", "Supporter", "Supporter+", "Moderator", "Moderator+",
            "Content", "SysDev", "Admin", "Head-Admin", "Leitung"
        ]);
    }
}
