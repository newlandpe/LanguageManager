<?php

declare(strict_types=1);

namespace ChernegaSergiy\LanguageManager;

use ChernegaSergiy\Language\Language;
use ChernegaSergiy\Language\LanguageHub;
use ChernegaSergiy\Language\PluginTranslator;
use ChernegaSergiy\Language\TranslatorInterface;
use ChernegaSergiy\LanguageManager\command\LangManagerCommand;
use ChernegaSergiy\LanguageManager\command\ListLangsCommand;
use ChernegaSergiy\LanguageManager\command\MyLangCommand;
use ChernegaSergiy\LanguageManager\command\SetLangCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {

    private Config $playerLanguageConfig;
    private TranslatorInterface $translator;
    private LanguageHub $languageHub;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->playerLanguageConfig = new Config($this->getDataFolder() . "player_languages.yml", Config::YAML, []);

        $this->languageHub = LanguageHub::getInstance();

        $resolver = new ManagedLocaleResolver($this->playerLanguageConfig);
        $this->languageHub->registerLocaleResolver($resolver);

        $languages = $this->loadLanguages();
        $this->translator = new PluginTranslator($this, $languages, $resolver, $this->getConfig()->get("default-language", "en_US"));

        // Register commands
        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
            new SetLangCommand($this),
            new MyLangCommand($this),
            new ListLangsCommand($this),
            new LangManagerCommand($this)
        ]);

        $registeredLocales = $this->languageHub->getKnownLocales();
        if (empty($registeredLocales)) {
            $this->getLogger()->critical($this->translator->translateFor(null, "plugin.no_languages_loaded"));
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $configDefault = $this->getConfig()->get("default-language", "en_US");

        if (in_array($configDefault, $registeredLocales)) {
            $this->languageHub->setDefaultLocale($this, $configDefault);
        } else {
            $this->getLogger()->warning($this->translator->translateFor(null, "plugin.default_language_not_found", ["locale" => $configDefault]));
            if (in_array("en_US", $registeredLocales)) {
                $this->getLogger()->info($this->translator->translateFor(null, "plugin.default_language_fallback_en_us"));
                $this->languageHub->setDefaultLocale($this, "en_US");
            } else {
                $fallbackLocale = reset($registeredLocales); // Get the first available language
                $this->getLogger()->info($this->translator->translateFor(null, "plugin.default_language_fallback_first_loaded", ["locale" => $fallbackLocale]));
                $this->languageHub->setDefaultLocale($this, $fallbackLocale);
            }
        }

        $this->getLogger()->info($this->translator->translateFor(null, "plugin.enabled"));
    }

    public function loadLanguages(): array {
        $this->saveResource("languages/en_US.yml");
        $this->saveResource("languages/uk_UA.yml");

        $languages = [];
        $languageDir = $this->getDataFolder() . 'languages/';
        $languageFiles = glob($languageDir . "*.yml");

        if ($languageFiles === false) {
            $this->getLogger()->error("Could not read language directory.");
            return [];
        }

        foreach ($languageFiles as $file) {
            $locale = basename($file, ".yml");
            $translations = (new Config($file, Config::YAML))->getAll();
            $languages[] = new Language($locale, $translations);
        }
        return $languages;
    }

    public function getTranslator(): TranslatorInterface {
        return $this->translator;
    }

    public function getLanguageHub(): LanguageHub {
        return $this->languageHub;
    }

    public function getPlayerLanguageConfig(): Config {
        return $this->playerLanguageConfig;
    }
}
