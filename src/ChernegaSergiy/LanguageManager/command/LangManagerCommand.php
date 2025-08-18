<?php

declare(strict_types=1);

namespace ChernegaSergiy\LanguageManager\command;

use ChernegaSergiy\LanguageManager\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as TF;

class LangManagerCommand extends Command implements PluginOwned {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("langmanager", "Main command for LanguageManager plugin.", "/langmanager <subcommand> [args]");
        $this->setPermission("languagemanager.command.base");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (count($args) < 1) {
            $this->sendHelp($sender);
            return true;
        }

        $subcommand = strtolower(array_shift($args));

        switch ($subcommand) {
            case "help":
                $this->sendHelp($sender);
                break;

            case "reload":
                if (!$sender->hasPermission("languagemanager.command.reload")) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.no_permission"));
                    return true;
                }
                $this->plugin->reloadConfig();
                $this->plugin->onEnable(); 
                $sender->sendMessage(TF::GREEN . $this->plugin->getTranslator()->translateFor($sender, "command.reload.success"));
                break;

            case "setdefault":
                if (!$sender->hasPermission("languagemanager.command.setdefault")) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.no_permission"));
                    return true;
                }
                if (count($args) < 1) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.setdefault.usage"));
                    return true;
                }
                $newLocale = $args[0];
                if (!in_array($newLocale, $this->plugin->getLanguageHub()->getKnownLocales())) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.setdefault.invalid_locale", ["locale" => $newLocale]));
                    return true;
                }
                $this->plugin->getConfig()->set("default-language", $newLocale);
                $this->plugin->getConfig()->save();
                $this->plugin->getLanguageHub()->setDefaultLocale($this->plugin, $newLocale); // Set default locale in LanguageHub
                $sender->sendMessage(TF::GREEN . $this->plugin->getTranslator()->translateFor($sender, "command.setdefault.success", ["locale" => $newLocale]));
                break;

            case "set":
                if (!$sender->hasPermission("languagemanager.command.set")) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.no_permission"));
                    return true;
                }
                if (count($args) < 2) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.set.usage"));
                    return true;
                }
                $playerName = array_shift($args);
                $locale = array_shift($args);
                $targetPlayer = $this->plugin->getServer()->getPlayerExact($playerName);
                if ($targetPlayer === null) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.player_not_found", ["player" => $playerName]));
                    return true;
                }
                if (!in_array($locale, $this->plugin->getLanguageHub()->getKnownLocales())) {
                    $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.set.invalid_locale", ["locale" => $locale]));
                    return true;
                }
                $this->plugin->getPlayerLanguageConfig()->set($targetPlayer->getName(), $locale);
                $this->plugin->getPlayerLanguageConfig()->save();
                $sender->sendMessage(TF::GREEN . $this->plugin->getTranslator()->translateFor($sender, "command.set.success", ["player" => $targetPlayer->getName(), "locale" => $locale]));
                $targetPlayer->sendMessage(TF::GREEN . $this->plugin->getTranslator()->translateFor($targetPlayer, "command.set.player_message", ["locale" => $locale]));
                break;

            default:
                $sender->sendMessage(TF::RED . $this->plugin->getTranslator()->translateFor($sender, "command.unknown_subcommand", ["subcommand" => $subcommand]));
                break;
        }
        return true;
    }

    private function sendHelp(CommandSender $sender): void {
        $sender->sendMessage(TF::YELLOW . $this->plugin->getTranslator()->translateFor($sender, "command.help.header"));
        if ($sender->hasPermission("languagemanager.command.help")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager help - " . $this->plugin->getTranslator()->translateFor($sender, "command.help.description"));
        }
        if ($sender->hasPermission("languagemanager.command.reload")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager reload - " . $this->plugin->getTranslator()->translateFor($sender, "command.reload.description"));
        }
        if ($sender->hasPermission("languagemanager.command.setdefault")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager setdefault <locale> - " . $this->plugin->getTranslator()->translateFor($sender, "command.setdefault.description"));
        }
        if ($sender->hasPermission("languagemanager.command.set")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager set <player> <locale> - " . $this->plugin->getTranslator()->translateFor($sender, "command.set.description"));
        }
    }

    public function getOwningPlugin(): Main {
        return $this->plugin;
    }
}
