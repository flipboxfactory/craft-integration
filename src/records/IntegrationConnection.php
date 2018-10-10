<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records;

use craft\helpers\Json;
use flipbox\craft\integration\connections\ConnectionConfigurationInterface;
use flipbox\craft\integration\connections\DefaultConfiguration;
use flipbox\craft\integration\services\IntegrationConnectionManager;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\ember\traits\HandleRules;
use yii\validators\UniqueValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.0
 *
 * @property string $class
 * @property array $settings
 */
abstract class IntegrationConnection extends ActiveRecordWithId
{
    use HandleRules;

    /**
     * @var ConnectionConfigurationInterface
     */
    private $type;

    /**
     * @return IntegrationConnectionManager
     */
    abstract protected function getConnectionManager(): IntegrationConnectionManager;

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

    /**
     * @return ConnectionConfigurationInterface
     */
    public function getConfiguration(): ConnectionConfigurationInterface
    {
        if ($this->type === null) {
            if (null === ($type = $this->getConnectionManager()->findConfiguration($this))) {
                $type = new DefaultConfiguration($this);
            }

            $this->type = $type;
        }

        return $this->type;
    }
}
