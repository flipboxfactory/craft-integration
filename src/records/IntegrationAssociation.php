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
use flipbox\craft\integration\queries\IntegrationAssociationQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $fieldId
 * @property int $elementId
 * @property string $objectId
 * @property string $siteId
 * @property int $sortOrder
 *
 * @method IntegrationAssociationQuery find()
 */
abstract class IntegrationAssociation extends ActiveRecord
{
    use SiteAttributeTrait,
        ElementAttributeTrait,
        FieldAttributeTrait,
        SortableTrait;

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

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->ensureSortOrder(
            [
                'objectId' => $this->objectId,
                'fieldId' => $this->fieldId,
                'siteId' => $this->siteId
            ]
        );

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->autoReOrder(
            'elementId',
            [
                'objectId' => $this->objectId,
                'fieldId' => $this->fieldId,
                'siteId' => $this->siteId
            ]
        );

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        $this->autoReOrder(
            'elementId',
            [
                'objectId' => $this->objectId,
                'fieldId' => $this->fieldId,
                'siteId' => $this->siteId
            ]
        );

        parent::afterDelete();
    }
}
