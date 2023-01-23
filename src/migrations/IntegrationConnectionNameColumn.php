<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\StringHelper;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
abstract class IntegrationConnectionNameColumn extends Migration
{
    /**
     * @return string
     */
    abstract protected static function tableName(): string;

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $table = $this->getDb()->getSchema()->getTableSchema(
            $this->getDb()->getSchema()->getRawTableName(static::tableName())
        );

        if (isset($table->columns['name'])) {
            return true;
        }

        $this->addColumn(
            static::tableName(),
            'name',
            $this->string()->after('id')->notNull()
        );

        $records = (new Query())
            ->from(static::tableName())
            ->select(['id', 'handle'])
            ->all();

        foreach ($records as $record) {
            Craft::$app->getDb()->createCommand()
                ->update(
                    static::tableName(),
                    ['name' => StringHelper::titleize($record['handle'])],
                    ['id' => $record['id']]
                )
                ->execute();
        }

        return true;
    }
}
