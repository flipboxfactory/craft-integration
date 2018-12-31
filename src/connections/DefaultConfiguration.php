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
class DefaultConfiguration implements ConnectionConfigurationInterface
{
    /**
     * @var IntegrationConnection
     */
    protected $connection;

    /**
     * @inheritdoc
     */
    public function __construct(IntegrationConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Default';
    }

    /**
     * @return bool
     */
    public function process(): bool
    {
        return $this->connection->save();
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): string
    {
        return '';
    }
}
