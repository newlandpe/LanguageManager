<?php

declare(strict_types=1);

namespace ChernegaSergiy\LanguageManager\command;

use ChernegaSergiy\LanguageManager\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as TF;

class SetLangCommand extends Command implements PluginOwned {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("setlang", "Set your preferred language.", "/setlang <locale>");
        $this->setPermission("languagemanager.command.setlang");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . $this->getPlugin()->getTranslator()->translateFor($sender, "command.player_only"));
            return true;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TF::RED . $this->getPlugin()->getTranslator()->translateFor($sender, "command.setlang.usage"));
            return true;
        }

        $newLocale = $args[0];
        if (!in_array($newLocale, $this->getPlugin()->getLanguageHub()->getKnownLocales())) {
            $sender->sendMessage(TF::RED . $this->getPlugin()->getTranslator()->translateFor($sender, "command.setlang.invalid_locale", ["locale" => $newLocale]));
            return true;
        }

        $this->getPlugin()->getPlayerLanguageConfig()->set($sender->getName(), $newLocale);
        $this->getPlugin()->getPlayerLanguageConfig()->save();
        $sender->sendMessage(TF::GREEN . $this->getPlugin()->getTranslator()->translateFor($sender, "command.setlang.success", ["locale" => $newLocale]));
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
