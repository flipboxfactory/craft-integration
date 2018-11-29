<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records;

use craft\helpers\Json;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\models\HandleRulesTrait;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\ember\records\StateAttributeTrait;
use yii\validators\UniqueValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property string $class
 * @property array $settings
 */
abstract class IntegrationConnection extends ActiveRecordWithId
{
    use HandleRulesTrait,
        StateAttributeTrait;

    /**
     * @inheritdoc
     */
    abstract public static function displayName(): string;

    /**
     * @inheritdoc
     */
    abstract public function getSettingsHtml(): string;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Normalize settings
        $this->setAttribute(
            'settings',
            static::normalizeSettings($this->getAttribute('settings'))
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->handleRules(),
            $this->stateRules(),
            [
                [
                    [
                        'class',
                        'type'
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
                        'type',
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
     * @param static $record
     * @param $row
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $settings = static::normalizeSettings($record->settings);

        $record->setOldAttribute('settings', $settings);
        $record->setAttribute('settings', $settings);
    }

    /**
     * @param $settings
     * @return array
     */
    protected static function normalizeSettings($settings): array
    {
        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        if (!is_array($settings)) {
            $settings = array_filter([$settings]);
        }

        return $settings;
    }
}
