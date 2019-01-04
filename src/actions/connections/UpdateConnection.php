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
 * @since 1.1.0
 */
abstract class UpdateConnection extends UpdateRecord
{
    /**
     * @return array
     */
    public $validBodyParams = [
        'handle',
        'class',
        'enabled'
    ];

    /**
     * @inheritdoc
     */
    public function run($connection)
    {
        return parent::run($connection);
    }
}
