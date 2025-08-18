<?php

declare(strict_types=1);

namespace ChernegaSergiy\LanguageManager;

use ChernegaSergiy\Language\LocaleResolverInterface;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class ManagedLocaleResolver implements LocaleResolverInterface {

    private Config $playerLanguageConfig;

    public function __construct(Config $playerLanguageConfig) {
        $this->playerLanguageConfig = $playerLanguageConfig;
    }

    public function resolve(Player $player): string {
        return $this->playerLanguageConfig->get($player->getName(), $player->getLocale());
    }
}
