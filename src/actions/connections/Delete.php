<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections;

use flipbox\ember\actions\record\RecordDelete;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.1
 */
abstract class Delete extends RecordDelete
{
    use traits\Delete;

    /**
     * @inheritdoc
     */
    public function run($connection)
    {
        return parent::run($connection);
    }
}
