# LanguageManager

[![Poggit CI](https://poggit.pmmp.io/ci.shield/newlandpe/LanguageManager/LanguageManager)](https://poggit.pmmp.io/ci/newlandpe/LanguageManager/LanguageManager)

A comprehensive language management plugin for PocketMine-MP, built upon the [libLanguage](https://github.com/newlandpe/libLanguage) virion, designed to provide a centralized and extensible solution for handling multi-language support across your server. This plugin allows other plugins to easily integrate and utilize a unified language system.

## Features

- **Centralized Language Management:** Manage all server languages from a single, unified system.
- **Player-Specific Language Preferences:** Allows players to set and save their preferred language, which is then used for all localized messages.
- **Flexible Translation:** Leverages `libLanguage`'s `PluginTranslator` for efficient and flexible message translation, supporting placeholders and fallback mechanisms.
- **Command-Based Management:** Provides a suite of commands for server administrators and players to manage languages.
- **Automatic Language Fallback:** Handles default server language fallback if the configured language is not found.
- **Easy Configuration:** Simple `config.yml` for managing default language and other settings.

## Installation

1. Download the latest stable version of LanguageManager from [Poggit CI](https://poggit.pmmp.io/ci/newlandpe/LanguageManager/LanguageManager) (or your preferred download source).
2. Place the `LanguageManager.phar` file into the `plugins/` folder of your PocketMine-MP server.
3. Restart your server.

## Configuration

The plugin generates a `config.yml` file in `plugin_data/LanguageManager/` upon first run. You can customize various settings there, including:

- Default server language
- Fallback language settings

Language files are located in `plugin_data/LanguageManager/languages/`. You can modify existing language files or add new ones.

## Commands

LanguageManager provides the following commands for managing languages on your server:

- `/setlang <locale>`: Set your preferred language.
- `/mylang`: Show your current language.
- `/listlangs`: List all available languages.
- `/langmanager <subcommand>`: Main command for LanguageManager plugin, offering administrative functionalities.
  - `help`: Show this help guide.
  - `reload`: Reloads the plugin's configuration and language files.
  - `set <player> <locale>`: Sets a specific player's preferred language.
  - `setdefault <locale>`: Sets the default server language.

### Permissions

| Permission | Description | Default |
| --- | --- | --- |
| `languagemanager.command.setlang` | Allows players to set their language | `true` |
| `languagemanager.command.mylang` | Allows players to view their current language | `true` |
| `languagemanager.command.listlangs` | Allows players to list available languages | `true` |
| `languagemanager.command.base` | Allows usage of the `/langmanager` command | `op` |
| `languagemanager.command.help` | Allows usage of the `/langmanager help` command | `true` |
| `languagemanager.command.reload` | Allows usage of the `/langmanager reload` command | `op` |
| `languagemanager.command.setdefault` | Allows usage of the `/langmanager setdefault` command | `op` |
| `languagemanager.command.set` | Allows usage of the `/langmanager set` command | `op` |

## Adding a New Language

LanguageManager makes it easy to add new languages without needing to modify any code.

1. **Create a Language File:** Create a new `.yml` file for your language. The filename **must** be a valid [Minecraft: Bedrock Edition locale code](https://wiki.bedrock.dev/text/text-intro#vanilla-languages) (e.g., `de_DE.yml` for German, `fr_FR.yml` for French).
2. **Translate Messages:** Copy the contents from an existing language file (like `en_US.yml`) into your new file and translate all the messages.
3. **Upload the File:** Place your new language file in the `plugin_data/LanguageManager/languages/` directory on your server.
4. **Reload and Use:** Restart your server or use the `/langmanager reload` command. The new language will be automatically detected and made available for players to use with `/setlang <your_new_locale>`.

If you use an invalid locale for the filename, the server will not crash. Instead, you will see a warning in the console, and the file will be skipped.

## Integration with Other Plugins

LanguageManager provides a global translation scope for any plugin using the `libLanguage` virion. Plugins using `libLanguage` do **not** need to explicitly depend on LanguageManager.

The `libLanguage` API provides translation isolation between plugins, meaning you never have to worry about translation key conflicts. If a translation is not found within your own plugin's scope, `libLanguage` will automatically fall back to `LanguageManager`'s translations (if LanguageManager is installed and enabled).

Here is an example of how to use `libLanguage` in your plugin, which will automatically benefit from LanguageManager's global translations:

```php
<?php

namespace YourPlugin;

use ChernegaSergiy\Language\Language;
use ChernegaSergiy\Language\LanguageHub;
use ChernegaSergiy\Language\PluginTranslator;
use ChernegaSergiy\Language\TranslatorInterface;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class YourPlugin extends PluginBase implements Listener {

    private TranslatorInterface $translator;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Load your plugin's translations
        $this->saveResource("languages/en_US.yml");
        $languages = [];
        $languageDir = $this->getDataFolder() . 'languages/';
        $languageFiles = glob($languageDir . "*.yml");
        foreach ($languageFiles as $file) {
            $locale = basename($file, ".yml");
            $translations = (new Config($file, Config::YAML))->getAll();
            $languages[] = new Language($locale, $translations);
        }

        // Get the best available LocaleResolver from the LanguageHub
        $localeResolver = LanguageHub::getInstance()->getLocaleResolver();

        // Initialize your PluginTranslator instance
        $this->translator = new PluginTranslator($this, $languages, $localeResolver, "en_US");
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        // Translate for a CommandSender (Player or Console)
        $welcomeMessage = $this->translator->translateFor(
            $player,
            "myplugin.welcome.message",
            ["player" => $player->getName()]
        );
        $player->sendMessage($welcomeMessage);
    }
}
```

## Contributing

Contributions are welcome and appreciated! Here's how you can contribute:

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please make sure to update tests as appropriate and adhere to the existing coding style.

## License

This project is licensed under the CSSM Unlimited License v2 (CSSM-ULv2). See the [LICENSE](LICENSE) file for details.
