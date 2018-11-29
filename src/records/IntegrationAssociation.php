<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records;

use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\ElementAttributeTrait;
use flipbox\craft\ember\records\FieldAttributeTrait;
use flipbox\craft\ember\records\SiteAttributeTrait;
use flipbox\craft\ember\records\SortableTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property int $fieldId
 * @property int $elementId
 * @property string $objectId
 * @property string $siteId
 * @property int $sortOrder
 */
abstract class IntegrationAssociation extends ActiveRecord
{
    use SiteAttributeTrait,
        ElementAttributeTrait,
        FieldAttributeTrait,
        SortableTrait;

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
                        'objectId',
                    ],
                    'required'
                ],
                [
                    'objectId',
                    'unique',
                    'targetAttribute' => [
                        'elementId',
                        'fieldId',
                        'siteId',
                        'objectId'
                    ]
                ],
                [
                    [
                        'objectId'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    private function sortOrderCondition(): array
    {
        return [
            'elementId' => $this->elementId,
            'fieldId' => $this->fieldId,
            'siteId' => $this->siteId,
        ];
    }

    /**
     * @param $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->ensureSortOrder(
            $this->sortOrderCondition()
        );

        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->autoReOrder(
            'objectId',
            $this->sortOrderCondition()
        );

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        $this->autoReOrder(
            'objectId',
            $this->sortOrderCondition()
        );

        parent::afterDelete();
    }
}
