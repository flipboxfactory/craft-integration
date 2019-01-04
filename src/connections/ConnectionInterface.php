<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\connections;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
interface ConnectionInterface
{
    /**
     * @return string
     */
    public static function displayName(): string;

    /**
     * @return string
     */
    public function getSettingsHtml(): string;
}
