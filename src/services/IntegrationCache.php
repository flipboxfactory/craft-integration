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
use flipbox\craft\psr16\SimpleCacheAdapter;
use Flipbox\Skeleton\Exceptions\InvalidConfigurationException;
use Flipbox\Skeleton\Helpers\ObjectHelper;
use Psr\SimpleCache\CacheInterface;
use yii\base\Component;
use yii\caching\DummyCache;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.2.0
 */
abstract class IntegrationCache extends Component
{
    /**
     * The dummy cache handle
     */
    const DUMMY_CACHE = 'dummy';

    /**
     * The app cache handle
     */
    const APP_CACHE = 'app';

    /**
     * The default cache identifier
     */
    const DEFAULT_CACHE = 'DEFAULT';

    /**
     * The override file
     */
    public $overrideFile;

    /**
     *  Cache overrides
     *
     * @var array|null
     */
    private $overrides;

    /**
     * @var CacheInterface[]
     */
    private $cache = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache[self::APP_CACHE] = Craft::createObject([
            'class' => SimpleCacheAdapter::class,
            'cache' => Craft::$app->getCache()
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache[self::DUMMY_CACHE] = Craft::createObject([
            'class' => SimpleCacheAdapter::class,
            'cache' => [
                'class' => DummyCache::class
            ]
        ]);
    }

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
    protected function getDefaultCache(): string
    {
        return static::DUMMY_CACHE;
    }

    /**
     * @param string $handle
     * @return CacheInterface|null
     */
    protected function handleCacheNotFound(string $handle)
    {
        return $this->find(static::DUMMY_CACHE);
    }

    /**
     * @param string $handle
     * @return CacheInterface|null
     */
    public function find(string $handle = self::DEFAULT_CACHE)
    {
        if ($handle === self::DEFAULT_CACHE) {
            $handle = $this->getDefaultCache();
        }

        if (!array_key_exists($handle, $this->cache)) {
            $this->cache[$handle] = $this->create(['handle' => $handle]);
        }

        return $this->cache[$handle];
    }

    /**
     * @param string $handle
     * @return CacheInterface
     */
    public function get(string $handle = self::DEFAULT_CACHE): CacheInterface
    {
        if (null === ($cache = $this->find($handle))) {
            return $this->handleCacheNotFound($handle);
        }

        return $cache;
    }

    /**
     * @param $config
     * @return CacheInterface|null
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
            $cache = ObjectHelper::create(
                $config,
                CacheInterface::class
            );
        } catch (InvalidConfigurationException $e) {
            return null;
        }

        return $cache;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        // Ensure configs are loaded
        $this->loadOverrides();

        // The handles defined in the config
        $handles = array_keys($this->overrides);

        // Any configured that aren't in the db
        foreach ($handles as $handle) {
            $this->cache[$handle] = $this->create(['handle' => $handle]);
        }

        return $this->cache;
    }
}
