<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections;

use Craft;
use flipbox\craft\integration\records\IntegrationConnection;
use flipbox\ember\actions\record\RecordUpdate;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 */
class Update extends RecordUpdate
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

    /**
     * @inheritdoc
     */
    public function run($connection)
    {
        return parent::run($connection);
    }

    /**
     * @param IntegrationConnection $connection
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function performAction(IntegrationConnection $connection): bool
    {
        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            if (!$connection->getConfiguration()->process()) {
                $connection->addError('class', 'Unable to save provider settings');
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
