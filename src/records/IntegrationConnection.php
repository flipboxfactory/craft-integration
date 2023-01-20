<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records;

use Craft;
use craft\helpers\Json;
use flipbox\craft\ember\models\HandleRulesTrait;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\integration\queries\IntegrationConnectionQuery;
use yii\validators\UniqueValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property string $name
 * @property string $class
 * @property array $settings
 */
abstract class IntegrationConnection extends ActiveRecordWithId
{
    use HandleRulesTrait;

    /**
     * The query class this record uses
     */
    const QUERY_CLASS = IntegrationConnectionQuery::class;

    /**
     * @param static $record
     * @param $row
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);
        $record->setOldAttribute('settings', $record->ensureSettings());
    }


    /*******************************************
     * QUERY
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return IntegrationConnectionQuery
     */
    public static function find(): \craft\db\ActiveQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        return Craft::createObject(
            static::QUERY_CLASS,
            [
                get_called_class()
            ]
        );
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


    /*******************************************
     * RULES
     *******************************************/

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
                        'class',
                        'name'
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
                        'name',
                        'class',
                        'settings',
                        'enabled'
                    ],
                    'safe',
                    'on' => [
                        static::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->ensureSettings();
    }


    /*******************************************
     * SETTINGS
     *******************************************/

    /**
     * @param string $attribute
     * @return mixed
     */
    public function getSettingsValue(string $attribute)
    {
        $settings = $this->ensureSettings();
        return $settings[$attribute] ?? null;
    }

    /**
     * @param string $attribute
     * @param $value
     * @return $this
     */
    public function setSettingsValue(string $attribute, $value)
    {
        $settings = $this->ensureSettings();
        $settings[$attribute] = $value;

        $this->setAttribute('settings', $settings);
        return $this;
    }

    /**
     * @return array|null
     */
    protected function ensureSettings()
    {
        $settings = $this->getAttribute('settings');

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        $this->setAttribute('settings', $settings);

        return $settings;
    }
}
