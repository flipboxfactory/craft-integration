<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\services;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use flipbox\craft\integration\exceptions\ConnectionNotFound;
use Flipbox\Skeleton\Exceptions\InvalidConfigurationException;
use Flipbox\Skeleton\Helpers\ObjectHelper;
use yii\base\Component;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.2.0
 */
abstract class IntegrationConnections extends Component
{
    /**
     * The app connection handle
     */
    const CONNECTION = 'app';

    /**
     * The default connection identifier
     */
    const DEFAULT_CONNECTION = 'DEFAULT';

    /**
     * The override file
     */
    public $overrideFile;

    /**
     * @var array|null
     */
    private $overrides;

    /**
     * @var array
     */
    private $connections = [];

    /**
     * @var array
     */
    private $enabled = [];

    /**
     * @return string
     */
    abstract protected static function tableName(): string;

    /**
     * @return string
     */
    abstract protected static function connectionInstance(): string;

    /**
     * Load override cache configurations
     */
    protected function loadOverrides()
    {
        if ($this->overrides === null) {
            $this->overrides = [];

            if ($this->overrideFile !== null) {
                $this->overrides = Craft::$app->getConfig()->getConfigFromFile($this->overrideFile);
            }
        }
    }

    /**
     * Returns any configurations from the config file.
     *
     * @param string $handle
     * @return array|null
     */
    public function getOverrides(string $handle)
    {
        $this->loadOverrides();
        return $this->overrides[$handle] ?? null;
    }

    /**
     * @return string
     */
    protected function getDefaultConnection(): string
    {
        return static::CONNECTION;
    }

    /**
     * @param string $handle
     * @throws ConnectionNotFound
     */
    protected function handleConnectionNotFound(string $handle)
    {
        throw new ConnectionNotFound(
            sprintf(
                "Unable to find connection '%s'.",
                $handle
            )
        );
    }

    /**
     * @param string $handle
     * @param bool $enabledOnly
     * @return mixed|null
     */
    public function find(
        string $handle = self::DEFAULT_CONNECTION,
        bool $enabledOnly = true
    ) {
        if ($handle === self::DEFAULT_CONNECTION) {
            $handle = $this->getDefaultConnection();
        }

        if (!array_key_exists($handle, $this->connections)) {
            $connection = null;
            $enabled = false;

            if ($config = (new Query())
                ->select([
                    'handle',
                    'class',
                    'settings',
                    'enabled'
                ])
                ->from(static::tableName())
                ->andWhere([
                    'handle' => $handle
                ])
                ->one()
            ) {
                $enabled = (bool)ArrayHelper::remove($config, 'enabled', false);
                $connection = $this->create($config);
            }
            $this->enabled[$handle] = $enabled;
            $this->connections[$handle] = $connection;
        }

        // Disabled?
        if ($enabledOnly === true && ($this->enabled[$handle] ?? false) === false) {
            return null;
        }

        return $this->connections[$handle];
    }

    /**
     * @param string $handle
     * @param bool $enabledOnly
     * @return mixed
     * @throws ConnectionNotFound
     */
    public function get(
        string $handle = self::DEFAULT_CONNECTION,
        bool $enabledOnly = true
    ) {
        if (null === ($connection = $this->find($handle, $enabledOnly))) {
            return $this->handleConnectionNotFound($handle);
        }

        return $connection;
    }

    /**
     * @param $config
     * @return mixed|null
     */
    protected function create(array $config)
    {
        // Merge settings
        $config = ComponentHelper::mergeSettings($config);

        // Apply overrides
        if (null !== ($handle = ArrayHelper::remove($config, 'handle')) &&
            null !== ($override = $this->getOverrides($handle))
        ) {
            $config = array_merge($config, $override);
        }

        try {
            $connection = ObjectHelper::create(
                $config,
                static::connectionInstance()
            );
        } catch (InvalidConfigurationException $e) {
            return null;
        }

        return $connection;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        $configs = (new Query())
            ->select([
                'handle',
                'class',
                'settings',
                'enabled'
            ])
            ->from(static::tableName())
            ->all();

        foreach ($configs as $config) {
            $handle = ArrayHelper::getValue($config, 'handle');

            if (!array_key_exists($handle, $this->connections)) {
                $this->enabled[$handle] = (bool)ArrayHelper::remove($config, 'enabled', false);
                $this->connections[$handle] = $this->create($config);
            }
        }

        return $this->connections;
    }
}
