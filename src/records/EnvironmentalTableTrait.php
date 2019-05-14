<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records;

use Craft;
use yii\db\Exception;
use yii\db\MigrationInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.2.2
 */
trait EnvironmentalTableTrait
{
    /**
     * Identify whether we have ensured the environment table is present
     *
     * @var bool
     */
    protected static $environmentTableChecked = false;

    /**
     * The table migration responsible for creating a new table (for an environment) if one
     * doesn't already exist.
     *
     * @return MigrationInterface
     */
    abstract static protected function createEnvironmentTableMigration(): MigrationInterface;

    /**
     * The name of the environment table.  It's suggested to add an environmental suffix to the table.  As an
     * example: 'your_table_local' where '_local' is the environment.  Do not include the table prefix.
     *
     * @return string
     */
    abstract protected static function environmentTableAlias(): string;

    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    protected static function doesEnvironmentTableExist(): bool
    {
        return in_array(
            Craft::$app->getDb()->tablePrefix . static::environmentTableAlias(),
            Craft::$app->getDb()->getSchema()->tableNames,
            true
        );
    }

    /**
     * @throws \Throwable
     */
    public static function ensureEnvironmentTableExists()
    {
        if (static::$environmentTableChecked === true) {
            return;
        }

        if (!static::doesEnvironmentTableExist() && !static::createEnvironmentTable()) {
            throw new Exception(sprintf(
                "Unable to create environment table '%s'.",
                static::environmentTableAlias()
            ));
        }

        static::$environmentTableChecked = true;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    protected static function createEnvironmentTable(): bool
    {
        ob_start();
        $return = static::createEnvironmentTableMigration()->up();
        ob_end_clean();

        return $return !== false;
    }
}
