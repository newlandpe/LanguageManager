<?php

declare(strict_types=1);

namespace ChernegaSergiy\LanguageManager\command;

use ChernegaSergiy\LanguageManager\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as TF;

class MyLangCommand extends Command implements PluginOwned {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("mylang", "Show your current language.", "/mylang");
        $this->setPermission("languagemanager.command.mylang");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . $this->getPlugin()->getTranslator()->translateFor($sender, "command.player_only"));
            return true;
        }

        $locale = $this->getPlugin()->getPlayerLanguageConfig()->get($sender->getName(), $sender->getLocale());
        $sender->sendMessage(TF::GREEN . $this->getPlugin()->getTranslator()->translateFor($sender, "command.mylang.current", ["locale" => $locale]));
        return true;
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
