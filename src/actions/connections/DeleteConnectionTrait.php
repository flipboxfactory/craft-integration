<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections;

use flipbox\craft\integration\records\IntegrationConnection;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.1
 */
trait DeleteConnectionTrait
{
    /**
     * @param ActiveRecord $record
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function performAction(ActiveRecord $record): bool
    {
        if (!$record instanceof IntegrationConnection) {
            return false;
        }

        return $record->delete();
    }
}
