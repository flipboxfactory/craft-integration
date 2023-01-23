<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\migrations;

use craft\db\Migration;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
abstract class IntegrationConnections extends Migration
{
    /**
     * @return string
     */
    abstract protected static function tableName(): string;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(
            static::tableName(),
            [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'handle' => $this->string()->notNull(),
                'class' => $this->string()->notNull(),
                'settings' => $this->text(),
                'enabled' => $this->boolean(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]
        );

        $this->createIndex(
            $this->db->getIndexName(),
            static::tableName(),
            'handle',
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(static::tableName());
        return true;
    }
}
