<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\connections;

use flipbox\craft\integration\records\IntegrationConnection;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
interface ConnectionConfigurationInterface
{
    /**
     * @param IntegrationConnection $connection
     */
    public function __construct(IntegrationConnection $connection);

    /**
     * @return string
     */
    public static function displayName(): string;

    /**
     * Process / Save a connection (and preform any additional actions necessary)
     *
     * @return bool
     */
    public function process(): bool;

    /**
     * @return string
     */
    public function getSettingsHtml(): string;
}
