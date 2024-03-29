<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections;

use flipbox\craft\ember\actions\records\UpdateRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
abstract class UpdateConnection extends UpdateRecord
{
    /**
     * @return array
     */
    public $validBodyParams = [
        'name',
        'handle',
        'class',
        'enabled'
    ];
}
