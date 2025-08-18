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

## Integration with Other Plugins

If you want to use the `LanguageManager` plugin as a central source for language management across multiple plugins, you can access its `LanguageAPI` instance directly. This allows `LanguageManager` to manage a global set of languages that other plugins can utilize.

To do this, your plugin needs to:
1. Add `LanguageManager` as a `softdepend` in its `plugin.yml`.
2. Access the `LanguageManager` plugin instance and then its `LanguageAPI` via the static `getLanguageAPI()` method.

```php
<?php

namespace AnotherPlugin;

use ChernegaSergiy\LanguageManager\Main as LanguageManagerPlugin; // Alias the main class
use ChernegaSergiy\Language\LanguageAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class AnotherPlugin extends PluginBase {

    private ?LanguageAPI $centralLanguageAPI = null;

    public function onEnable(): void {
        $languageManagerPlugin = $this->getServer()->getPluginManager()->getPlugin("LanguageManager");

        if ($languageManagerPlugin instanceof LanguageManagerPlugin) {
            $this->centralLanguageAPI = $languageManagerPlugin::getLanguageAPI();
            $this->getLogger()->info("Successfully hooked into LanguageManager as central language provider.");

            // Now you can use $this->centralLanguageAPI to access languages managed by LanguageManager
            $message = $this->centralLanguageAPI->localize(
                $this->getServer()->getConsoleSender(),
                "welcome.message",
                ["%player%" => "Console"]
            );
            $this->getLogger()->info("Translated via central API: " . $message);

        } else {
            $this->getLogger()->warning("LanguageManager plugin not found or not enabled. Using isolated LanguageAPI instance.");
            // Fallback to isolated instance if LanguageManager is not available
            $this->centralLanguageAPI = new LanguageAPI();
            // ... your own language setup for this plugin
        }
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
