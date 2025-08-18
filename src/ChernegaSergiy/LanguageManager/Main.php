<?php

declare(strict_types=1);

namespace ChernegaSergiy\LanguageManager;

use ChernegaSergiy\Language\Language;
use ChernegaSergiy\Language\LanguageAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase {

    private static ?self $instance = null;

    private LanguageAPI $languageAPI;
    private Config $playerLanguageConfig;

    public function onEnable(): void {
        self::$instance = $this;

        $this->languageAPI = new LanguageAPI();
        $this->saveDefaultConfig();

        $this->playerLanguageConfig = new Config($this->getDataFolder() . "player_languages.yml", Config::YAML, []);

        $this->loadLanguages();

        $defaultLocale = $this->getConfig()->get("default-language", "en_US");
        $defaultLang = $this->languageAPI->getLanguageByLocale($defaultLocale);
        if ($defaultLang !== null) {
            $this->languageAPI->setDefaultLanguage($defaultLang);
        } else {
            $this->getLogger()->warning("Default language '{$defaultLocale}' not found. Using first registered language as default.");
            $allLanguages = $this->languageAPI->getLanguages();
            if (!empty($allLanguages)) {
                $this->languageAPI->setDefaultLanguage(reset($allLanguages));
            }
        }

        $this->getLogger()->info($this->getLocalizedMessage(null, "plugin_enabled"));
    }

    private function loadLanguages(): void {
        $languageDir = $this->getDataFolder() . 'languages/';
        if (!is_dir($languageDir)) {
            mkdir($languageDir, 0777, true);
        }

        // Save default languages if they don't exist
        $this->saveResource("languages/en_US.yml");
        $this->saveResource("languages/uk_UA.yml");

        $languageFiles = glob($languageDir . "*.yml");
        if ($languageFiles === false) {
            $this->getLogger()->error("Could not read language directory.");
            return;
        }

        if (empty($languageFiles)) {
            $this->getLogger()->warning("No language files found in {$languageDir}");
            return;
        }

        foreach ($languageFiles as $file) {
            $locale = basename($file, ".yml");
            $translations = (new Config($file, Config::YAML))->getAll();
            $language = new Language($locale, $translations);
            try {
                $this->languageAPI->registerLanguage($language);
            } catch (\InvalidArgumentException $e) {
                $this->getLogger()->warning("Skipping language file '{$file}'. The locale '{$locale}' is not a valid Minecraft: Bedrock Edition language code.");
                $this->getLogger()->debug($e->getMessage());
            }
        }
    }

    private function getPlayerLanguage(Player $player): string {
        return $this->playerLanguageConfig->get($player->getName(), $player->getLocale());
    }

    public function getLocalizedMessage(?CommandSender $sender, string $key, array $args = []): string {
        $locale = null;
        if ($sender instanceof Player) {
            $locale = $this->getPlayerLanguage($sender);
        } else {
            $locale = $this->getConfig()->get("default-language", "en_US");
        }
        return $this->languageAPI->localize(null, $key, $args, $locale);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        switch ($command->getName()) {
            case "setlang":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.player_only"));
                    return true;
                }
                if (count($args) < 1) {
                    $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.setlang.usage"));
                    return true;
                }
                $newLocale = $args[0];
                if ($this->languageAPI->getLanguageByLocale($newLocale) === null) {
                    $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.setlang.invalid_locale", ["%locale%" => $newLocale]));
                    return true;
                }
                $this->playerLanguageConfig->set($sender->getName(), $newLocale);
                $this->playerLanguageConfig->save();
                $sender->sendMessage(TF::GREEN . $this->getLocalizedMessage($sender, "command.setlang.success", ["%locale%" => $newLocale]));
                return true;

            case "mylang":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.player_only"));
                    return true;
                }
                $playerLocale = $this->getPlayerLanguage($sender);
                $sender->sendMessage(TF::GREEN . $this->getLocalizedMessage($sender, "command.mylang.current", ["%locale%" => $playerLocale]));
                return true;

            case "listlangs":
                $sender->sendMessage(TF::GREEN . $this->getLocalizedMessage($sender, "command.listlangs.header"));
                foreach ($this->languageAPI->getLanguages() as $langLocale => $langObject) {
                    $sender->sendMessage(TF::GREEN . $langLocale . ' - ' . $langObject->getTranslation("language.name"));
                }
                return true;

            case "langmanager":
                if (!isset($args[0])) {
                    $this->sendHelpMessage($sender);
                    return true;
                }

                $subcommand = strtolower(array_shift($args));

                switch ($subcommand) {
                    case "help":
                        if (!$sender->hasPermission("languagemanager.command.help")) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.no_permission"));
                            return true;
                        }
                        $this->sendHelpMessage($sender);
                        break;

                    case "reload":
                        if (!$sender->hasPermission("languagemanager.command.reload")) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.no_permission"));
                            return true;
                        }
                        $this->reloadConfig();
                        $this->languageAPI = new LanguageAPI();
                        $this->loadLanguages();

                        $defaultLocale = $this->getConfig()->get("default-language", "en_US");
                        $defaultLang = $this->languageAPI->getLanguageByLocale($defaultLocale);
                        if ($defaultLang !== null) {
                            $this->languageAPI->setDefaultLanguage($defaultLang);
                        } else {
                            $this->getLogger()->warning("Default language '{$defaultLocale}' not found. Using first registered language as default.");
                            $allLanguages = $this->languageAPI->getLanguages();
                            if (!empty($allLanguages)) {
                                $this->languageAPI->setDefaultLanguage(reset($allLanguages));
                            }
                        }

                        $sender->sendMessage(TF::GREEN . $this->getLocalizedMessage($sender, "command.reload.success"));
                        break;

                    case "setdefault":
                        if (!$sender->hasPermission("languagemanager.command.setdefault")) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.no_permission"));
                            return true;
                        }
                        if (count($args) < 1) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.setdefault.usage"));
                            return true;
                        }
                        $newLocale = $args[0];
                        $lang = $this->languageAPI->getLanguageByLocale($newLocale);
                        if ($lang === null) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.setdefault.invalid_locale", ["%locale%" => $newLocale]));
                            return true;
                        }
                        $this->getConfig()->set("default-language", $newLocale);
                        $this->getConfig()->save();
                        $this->languageAPI->setDefaultLanguage($lang);
                        $sender->sendMessage(TF::GREEN . $this->getLocalizedMessage($sender, "command.setdefault.success", ["%locale%" => $newLocale]));
                        break;

                    case "set":
                        if (!$sender->hasPermission("languagemanager.command.set")) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.no_permission"));
                            return true;
                        }
                        if (count($args) < 2) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.set.usage"));
                            return true;
                        }
                        $playerName = array_shift($args);
                        $newLocale = array_shift($args);

                        $targetPlayer = $this->getServer()->getPlayerExact($playerName);
                        if ($targetPlayer === null) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.player_not_found", ["%player%" => $playerName]));
                            return true;
                        }

                        if ($this->languageAPI->getLanguageByLocale($newLocale) === null) {
                            $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.set.invalid_locale", ["%locale%" => $newLocale]));
                            return true;
                        }

                        $this->playerLanguageConfig->set($targetPlayer->getName(), $newLocale);
                        $this->playerLanguageConfig->save();
                        
                        $sender->sendMessage(TF::GREEN . $this->getLocalizedMessage($sender, "command.set.success", ["%player%" => $targetPlayer->getName(), "%locale%" => $newLocale]));
                        
                        $targetPlayer->sendMessage(TF::GREEN . $this->getLocalizedMessage($targetPlayer, "command.set.player_message", ["%locale%" => $newLocale]));
                        break;

                    default:
                        $sender->sendMessage(TF::RED . $this->getLocalizedMessage($sender, "command.unknown_subcommand", ["%subcommand%" => $subcommand]));
                        $this->sendHelpMessage($sender);
                        break;
                }
                return true;

            default:
                return false;
        }
    }

    private function sendHelpMessage(CommandSender $sender): void {
        $sender->sendMessage(TF::YELLOW . "--- LanguageManager Help ---");
        if ($sender->hasPermission("languagemanager.command.help")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager help - " . $this->getLocalizedMessage($sender, "command.help.description"));
        }
        if ($sender->hasPermission("languagemanager.command.reload")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager reload - " . $this->getLocalizedMessage($sender, "command.reload.description"));
        }
        if ($sender->hasPermission("languagemanager.command.setdefault")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager setdefault <locale> - " . $this->getLocalizedMessage($sender, "command.setdefault.description"));
        }
        if ($sender->hasPermission("languagemanager.command.set")) {
            $sender->sendMessage(TF::YELLOW . "/langmanager set <player> <locale> - " . $this->getLocalizedMessage($sender, "command.set.description"));
        }
        $sender->sendMessage(TF::YELLOW . "--------------------------");
    }

    public static function getLanguageAPI(): LanguageAPI {
        if (self::$instance === null) {
            throw new \RuntimeException("LanguageManager plugin is not enabled yet.");
        }
        return self::$instance->languageAPI;
    }
}
