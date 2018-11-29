<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
trait PopulateConnectionTrait
{
    /**
     * @return array
     */
    protected function validBodyParams(): array
    {
        return [
            'handle',
            'class',
            'enabled'
        ];
    }
}