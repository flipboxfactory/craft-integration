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
use flipbox\craft\integration\connections\ConnectionConfigurationInterface;
use flipbox\craft\integration\connections\DefaultConfiguration;
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
    use HandleRulesTrait;

    /**
     * @var ConnectionConfigurationInterface
     */
    private $type;

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

        $settings = $record->settings;

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        $record->setOldAttribute('settings', $settings);
        $record->setAttribute('settings', $settings);
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
