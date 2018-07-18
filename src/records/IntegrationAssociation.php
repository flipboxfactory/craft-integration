<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records;

use flipbox\craft\sortable\associations\records\SortableAssociation;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\traits\ElementAttribute;
use flipbox\ember\records\traits\SiteAttribute;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $fieldId
 * @property int $elementId
 * @property string $objectId
 * @property string $siteId
 * @property int $sortOrder
 */
abstract class IntegrationAssociation extends SortableAssociation
{
    use SiteAttribute,
        ElementAttribute,
        traits\FieldAttribute;

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = 'objectId';

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = 'elementId';

    /**
     * The default Object Id (if none exists)
     */
    const DEFAULT_ID = 'UNKNOWN_ID';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['fieldId', 'elementId', 'siteId'];

    /**
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            $this->siteRules(),
            $this->elementRules(),
            $this->fieldRules(),
            [
                [
                    [
                        self::TARGET_ATTRIBUTE,
                    ],
                    'required'
                ],
                [
                    self::TARGET_ATTRIBUTE,
                    'unique',
                    'targetAttribute' => [
                        'elementId',
                        'fieldId',
                        'siteId',
                        self::TARGET_ATTRIBUTE
                    ]
                ],
                [
                    [
                        self::TARGET_ATTRIBUTE
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
