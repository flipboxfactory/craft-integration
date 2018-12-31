<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections\traits;

use Craft;
use flipbox\craft\integration\records\IntegrationConnection;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
trait Save
{
    /**
     * @inheritdoc
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function performAction(ActiveRecord $record): bool
    {
        if (!$record instanceof IntegrationConnection) {
            return false;
        }

        return $this->saveConnection($record);
    }

    /**
     * @param IntegrationConnection $connection
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function saveConnection(IntegrationConnection $connection): bool
    {
        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            if (!$connection->getConfiguration()->process()) {
                $connection->addError('configuration', 'Unable to save configuration.');
                $transaction->rollBack();
                return false;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction->commit();
        return true;
    }
}
