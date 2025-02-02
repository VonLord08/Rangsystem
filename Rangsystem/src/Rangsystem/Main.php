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

    public function onEnable(): void {
        $this->getLogger()->info("Rangsystem wurde aktiviert!");
        $this->permissionsConfig = new Config($this->getDataFolder() . "permissions.yml", Config::YAML);
        if (!$this->permissionsConfig->exists("groups")) {
            $this->permissionsConfig->set("groups", [
                "Spieler" => ["prefix" => "[Spieler]", "permissions" => []],
                "Premium" => ["prefix" => "[Premium]", "permissions" => []]
            ]);
            $this->permissionsConfig->save();
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch ($command->getName()) {
            case "setgroup":
                if (count($args) < 2) {
                    $sender->sendMessage("§cBenutzung: /setgroup <Spieler> <Gruppe>");
                    return false;
                }
                $this->setGroup($sender, $args[0], $args[1]);
                return true;
            case "addperm":
                if (count($args) < 2) {
                    $sender->sendMessage("§cBenutzung: /addperm <Gruppe> <Permission>");
                    return false;
                }
                $this->addPermission($sender, $args[0], $args[1]);
                return true;
            case "removeperm":
                if (count($args) < 2) {
                    $sender->sendMessage("§cBenutzung: /removeperm <Gruppe> <Permission>");
                    return false;
                }
                $this->removePermission($sender, $args[0], $args[1]);
                return true;
        }
        return false;
    }

    private function setGroup(CommandSender $sender, string $playerName, string $groupName): void {
        $groups = $this->permissionsConfig->get("groups", []);
        if (!isset($groups[$groupName])) {
            $sender->sendMessage("§cDie Gruppe $groupName existiert nicht.");
            return;
        }
        $players = $this->permissionsConfig->get("players", []);
        $players[$playerName] = ["group" => $groupName];
        $this->permissionsConfig->set("players", $players);
        $this->permissionsConfig->save();
        $sender->sendMessage("§aDer Spieler $playerName wurde der Gruppe $groupName zugewiesen.");
    }

    private function addPermission(CommandSender $sender, string $groupName, string $permission): void {
        $groups = $this->permissionsConfig->get("groups", []);
        if (!isset($groups[$groupName])) {
            $sender->sendMessage("§cDie Gruppe $groupName existiert nicht.");
            return;
        }
        if (!in_array($permission, $groups[$groupName]["permissions"])) {
            $groups[$groupName]["permissions"][] = $permission;
            $this->permissionsConfig->set("groups", $groups);
            $this->permissionsConfig->save();
        }
        $sender->sendMessage("§aDie Permission $permission wurde der Gruppe $groupName hinzugefügt.");
    }

    private function removePermission(CommandSender $sender, string $groupName, string $permission): void {
        $groups = $this->permissionsConfig->get("groups", []);
        if (!isset($groups[$groupName])) {
            $sender->sendMessage("§cDie Gruppe $groupName existiert nicht.");
            return;
        }
        if (($key = array_search($permission, $groups[$groupName]["permissions"])) !== false) {
            unset($groups[$groupName]["permissions"][$key]);
            $this->permissionsConfig->set("groups", $groups);
            $this->permissionsConfig->save();
        }
        $sender->sendMessage("§aDie Permission $permission wurde von der Gruppe $groupName entfernt.");
    }
}
