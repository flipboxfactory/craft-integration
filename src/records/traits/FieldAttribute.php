<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\records\traits;

use Craft;
use craft\base\FieldInterface;
use craft\records\Field as FieldRecord;
use flipbox\ember\records\traits\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait FieldAttribute
{
    use ActiveRecord,
        FieldRules,
        FieldMutator;

    /**
     * Get associated fieldId
     *
     * @return int|null
     */
    public function getFieldId()
    {
        $id = $this->getAttribute('fieldId');
        if (null === $id && null !== $this->field) {
            $id = $this->fieldId = $this->field->id;
        }

        return $id;
    }

    /**
     * @return FieldInterface|null
     */
    protected function resolveField()
    {
        if ($model = $this->resolveFieldFromRelation()) {
            return $model;
        }

        return $this->resolveFieldFromId();
    }

    /**
     * @return FieldInterface|null
     */
    protected function resolveFieldFromRelation()
    {
        if (false === $this->isRelationPopulated('fieldRecord')) {
            return null;
        }

        /** @var FieldRecord $record */
        $record = $this->getRelation('fieldRecord');
        if (null === $record) {
            return null;
        }

        return Craft::$app->getFields()->getFieldById($record->id);
    }

    /**
     * Get the associated Field
     *
     * @return ActiveQueryInterface
     */
    public function getFieldRecord()
    {
        return $this->hasOne(
            FieldRecord::class,
            ['fieldId' => 'id']
        );
    }
}
