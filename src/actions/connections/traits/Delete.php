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
 * @since 1.1.1
 */
trait Delete
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

        return $this->deleteConnection($record);
    }

    /**
     * @param IntegrationConnection $connection
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function deleteConnection(IntegrationConnection $connection): bool
    {
        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            if (!$connection->getConfiguration()->delete()) {
                $connection->addError('configuration', 'Unable to delete configuration.');

                // Validate anyway so we can see all errors
                $connection->validate();

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
