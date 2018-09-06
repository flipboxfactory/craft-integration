<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\services;

use flipbox\craft\integration\connections\ConnectionConfigurationInterface;
use flipbox\craft\integration\events\RegisterConnectionConfigurationsEvent;
use flipbox\craft\integration\records\IntegrationConnection as Connection;
use flipbox\ember\exceptions\ObjectNotFoundException;
use flipbox\ember\services\traits\records\AccessorByString;
use yii\base\Component;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 *
 * @method Connection  create(array $attributes = [])
 * @method Connection  find($identifier)
 * @method Connection  get($identifier)
 * @method Connection  findByString($identifier)
 * @method Connection  getByString($identifier)
 * @method Connection  findByCondition($condition = [])
 * @method Connection  getByCondition($condition = [])
 * @method Connection  findByCriteria($criteria = [])
 * @method Connection  getByCriteria($criteria = [])
 * @method Connection [] findAllByCondition($condition = [])
 * @method Connection [] getAllByCondition($condition = [])
 * @method Connection [] findAllByCriteria($criteria = [])
 * @method Connection [] getAllByCriteria($criteria = [])
 */
abstract class IntegrationConnectionManager extends Component
{
    use AccessorByString;

    /**
     * @event RegisterConnectionsEvent The event that is triggered when registering connections.
     */
    const EVENT_REGISTER_CONFIGURATIONS = 'registerConfigurations';

    /**
     * @inheritdoc
     */
    protected function stringProperty(): string
    {
        return 'handle';
    }

    /**
     * @param Connection $connection
     * @return ConnectionConfigurationInterface[]
     */
    public function getConfigurations(Connection $connection): array
    {
        $event = new RegisterConnectionConfigurationsEvent;

        $this->trigger(self::EVENT_REGISTER_CONFIGURATIONS, $event);

        $configurations = [];
        foreach ($event->configurations as $class => $configuration) {
            $configurations[$class] = new $configuration($connection);
        }

        return $configurations;
    }

    /**
     * @param Connection $connection
     * @return ConnectionConfigurationInterface|null
     */
    public function findConfiguration(Connection $connection)
    {
        $class = $connection->class ?: null;

        if ($class === null) {
            return null;
        }

        $types = $this->getConfigurations($connection);
        return $types[$class] ?? null;
    }

    /**
     * @param Connection $connection
     * @return ConnectionConfigurationInterface
     * @throws ObjectNotFoundException
     */
    public function getConfiguration(Connection $connection): ConnectionConfigurationInterface
    {
        if (null === ($type = $this->findConfiguration($connection))) {
            throw new ObjectNotFoundException("Unable to find connection type");
        }

        return $type;
    }
}
