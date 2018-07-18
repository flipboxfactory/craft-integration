<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records\traits;

use craft\base\Field;
use craft\base\FieldInterface;
use flipbox\ember\helpers\ModelHelper;

/**
 * @property int|null $fieldId
 * @property Field|FieldInterface|null $field
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait FieldRules
{
    /**
     * @return array
     */
    protected function fieldRules(): array
    {
        return [
            [
                [
                    'fieldId'
                ],
                'number',
                'integerOnly' => true
            ],
            [
                [
                    'fieldId',
                    'field'
                ],
                'safe',
                'on' => [
                    ModelHelper::SCENARIO_DEFAULT
                ]
            ]
        ];
    }
}
