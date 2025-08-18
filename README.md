# LanguageManager

[![Poggit CI](https://poggit.pmmp.io/ci.shield/newlandpe/LanguageManager/LanguageManager)](https://poggit.pmmp.io/ci/newlandpe/LanguageManager/LanguageManager)

A comprehensive language management plugin for PocketMine-MP, designed to provide a centralized and extensible solution for handling multi-language support across your server. This plugin allows other plugins to easily integrate and utilize a unified language system.

## Features

- **Centralized Language Management:** Manage all server languages from a single, unified system.
- **Multi-language Support:** Easily add, remove, and switch between various languages.
- **API for Other Plugins:** Provides a robust API for other plugins to localize messages and access language data.
- **Dynamic Language Switching:** Players can switch their preferred language on the fly.
- **Extensible Design:** Built to be easily extended with new language sources or formats.
- **Easy Configuration:** Simple `config.yml` for managing default language and other settings.

## Installation

1. Download the latest stable version of LanguageManager from [Poggit CI](https://poggit.pmmp.io/ci/newlandpe/LanguageManager/LanguageManager) (or your preferred download source).
2. Place the `LanguageManager.phar` file into the `plugins/` folder of your PocketMine-MP server.
3. Restart your server.

## Configuration

The plugin generates a `config.yml` file in `plugin_data/LanguageManager/` upon first run. You can customize various settings there, including:

- Default server language
- Fallback language settings
- Language file paths

Language files are located in `plugin_data/LanguageManager/lang/`. You can modify existing language files or add new ones.

## Commands

Here are the commands available in LanguageManager:

- `/setlang <locale>`: Set your preferred language.
- `/mylang`: Show your current language.
- `/listlangs`: List available languages.
- `/langmanager <subcommand>`: Main command for LanguageManager plugin.
  - `/langmanager help`: Show this help guide.
  - `/langmanager reload`: Reloads the plugin's configuration and language files.
  - `/langmanager setdefault <language_code>`: Sets the default server language.
  - `/langmanager set <player> <language_code>`: Sets a player's preferred language.

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

To use LanguageManager in your plugin, you should add it as a `softdepend` in your `plugin.yml`. This ensures your plugin loads after LanguageManager.

The easiest way to get a translated message is by using the `getLocalizedMessage()` method from the `LanguageManager` plugin instance.

Here is an example of how to send a localized welcome message to a player when they join the server:

```php
<?php

namespace YourPlugin;

use ChernegaSergiy\LanguageManager\Main as LanguageManager;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class YourPlugin extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $welcomeMessage = $this->getTranslatedMessage(
            $player,
            "yourplugin.welcome.message",
            ["%player%" => $player->getName()]
        );
        $player->sendMessage($welcomeMessage);
    }

    /**
     * @param CommandSender|null $sender
     * @param string $key
     * @param array $args
     * @return string
     */
    public function getTranslatedMessage(?CommandSender $sender, string $key, array $args = []): string {
        /** @var LanguageManager|null $languageManager */
        $languageManager = $this->getServer()->getPluginManager()->getPlugin("LanguageManager");

        if ($languageManager !== null && $languageManager->isEnabled()) {
            return $languageManager->getLocalizedMessage($sender, $key, $args);
        }

        // Fallback if LanguageManager is not available
        $message = $key;
        foreach($args as $placeholder => $value) {
            $message = str_replace($placeholder, (string)$value, $message);
        }
        return $message;
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
