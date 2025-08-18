<?php

declare(strict_types=1);

namespace ChernegaSergiy\LanguageManager\command;

use ChernegaSergiy\LanguageManager\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as TF;

class ListLangsCommand extends Command implements PluginOwned {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("listlangs", "List available languages.", "/listlangs");
        $this->setPermission("languagemanager.command.listlangs");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . $this->getPlugin()->getTranslator()->translateFor($sender, "command.player_only"));
            return true;
        }

        $sender->sendMessage(TF::GREEN . $this->getPlugin()->getTranslator()->translateFor($sender, "command.listlangs.header"));
        $knownLocales = $this->getPlugin()->getLanguageHub()->getKnownLocales();
        foreach ($knownLocales as $locale) {
            $sender->sendMessage(TF::GREEN . "- " . $locale);
        }
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
