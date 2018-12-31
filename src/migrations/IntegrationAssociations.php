<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\migrations;

use craft\db\Migration;
use craft\records\Element as ElementRecord;
use craft\records\Field as FieldRecord;
use craft\records\Site as SiteRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class IntegrationAssociations extends Migration
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
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(static::tableName());
        return true;
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable(static::tableName(), [
            'objectId' => $this->string()->notNull(),
            'elementId' => $this->integer()->notNull(),
            'fieldId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->addPrimaryKey(
            null,
            static::tableName(),
            [
                'elementId',
                'objectId',
                'fieldId',
                'siteId'
            ]
        );
        $this->createIndex(
            null,
            static::tableName(),
            'objectId',
            false
        );
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            null,
            static::tableName(),
            'elementId',
            ElementRecord::tableName(),
            'id',
            'CASCADE',
            null
        );
        $this->addForeignKey(
            null,
            static::tableName(),
            'siteId',
            SiteRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            null,
            static::tableName(),
            'fieldId',
            FieldRecord::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
