<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records;

use Craft;
use craft\helpers\Json;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\models\HandleRulesTrait;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\integration\connections\ConnectionInterface;
use flipbox\craft\integration\queries\IntegrationConnectionQuery;
use yii\validators\UniqueValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property string $class
 * @property array $settings
 */
abstract class IntegrationConnection extends ActiveRecordWithId implements ConnectionInterface
{
    use HandleRulesTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Always this class
        $this->class = static::class;
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return IntegrationConnectionQuery
     */
    public static function find(): IntegrationConnectionQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        return Craft::createObject(IntegrationConnectionQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->handleRules(),
            [
                [
                    [
                        'class'
                    ],
                    'required'
                ],
                [
                    [
                        'handle'
                    ],
                    UniqueValidator::class
                ],
                [
                    [
                        'class',
                        'settings'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function instantiate($row)
    {
        $class = $row['class'] ?? static::class;
        return new $class;
    }

    /**
     * @inheritdoc
     */
    protected static function findByCondition($condition)
    {
        if (!is_numeric($condition) && is_string($condition)) {
            $condition = ['handle' => $condition];
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        return parent::findByCondition($condition);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->ensureSettings();
    }

    /**
     * @param static $record
     * @param $row
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);
        $record->ensureSettings();
    }

    /**
     *
     */
    protected function ensureSettings()
    {
        $settings = $this->settings;

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        $this->setOldAttribute('settings', $settings);
        $this->setAttribute('settings', $settings);
    }
}
