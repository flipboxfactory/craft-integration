<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\services;

use flipbox\craft\integration\db\IntegrationAssociationQuery;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\ember\services\traits\records\Accessor;
use flipbox\ember\validators\MinMaxValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 *
 * @method IntegrationAssociationQuery parentGetQuery($config = [])
 * @method IntegrationAssociation create(array $attributes = [])
 * @method IntegrationAssociation find($identifier)
 * @method IntegrationAssociation get($identifier)
 * @method IntegrationAssociation findByCondition($condition = [])
 * @method IntegrationAssociation getByCondition($condition = [])
 * @method IntegrationAssociation findByCriteria($criteria = [])
 * @method IntegrationAssociation getByCriteria($criteria = [])
 * @method IntegrationAssociation[] findAllByCondition($condition = [])
 * @method IntegrationAssociation[] getAllByCondition($condition = [])
 * @method IntegrationAssociation[] findAllByCriteria($criteria = [])
 * @method IntegrationAssociation[] getAllByCriteria($criteria = [])
 */
abstract class IntegrationAssociations extends SortableAssociations
{
    use Accessor {
        getQuery as parentGetQuery;
    }

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = IntegrationAssociation::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = IntegrationAssociation::SOURCE_ATTRIBUTE;

    /**
     * @return IntegrationField
     */
    abstract protected function fieldService(): IntegrationField;

    /**
     * @param IntegrationAssociation $record
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    abstract public function validateObject(IntegrationAssociation $record): bool;

    /**
     * @inheritdoc
     * @return IntegrationAssociationQuery
     */
    public function getQuery($config = []): SortableAssociationQueryInterface
    {
        return $this->parentGetQuery($config);
    }

    /**
     * @inheritdoc
     * @param IntegrationAssociation $record
     * @return IntegrationAssociationQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {
        return $this->query(
            $record->elementId,
            $record->fieldId,
            $record->siteId
        );
    }

    /**
     * @inheritdoc
     * @param IntegrationAssociationQuery $query
     */
    protected function existingAssociations(
        SortableAssociationQueryInterface $query
    ): array {
        $source = $this->resolveStringAttribute($query, 'element');
        $field = $this->resolveStringAttribute($query, 'field');
        $site = $this->resolveStringAttribute($query, 'siteId');

        if ($source === null || $field === null || $site === null) {
            return [];
        }

        return $this->associations($source, $field, $site);
    }

    /**
     * @param int $elementId
     * @param int $fieldId
     * @param int $siteId
     * @return IntegrationAssociationQuery
     */
    private function query(
        int $elementId,
        int $fieldId,
        int $siteId
    ): IntegrationAssociationQuery {
        return $this->getQuery()
            ->where([
                'elementId' => $elementId,
                'fieldId' => $fieldId,
                'siteId' => $siteId
            ])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }

    /**
     * @param int $elementId
     * @param int $fieldId
     * @param int $siteId
     * @return array
     */
    private function associations(
        int $elementId,
        int $fieldId,
        int $siteId
    ): array {
        return $this->query($elementId, $fieldId, $siteId)
            ->indexBy(static::TARGET_ATTRIBUTE)
            ->all();
    }

    /**
     * @inheritdoc
     * @param bool $validate
     * @throws \Exception
     */
    public function save(
        SortableAssociationQueryInterface $query,
        bool $validate = true
    ): bool {
        if ($validate === true && null !== ($field = $this->resolveFieldFromQuery($query))) {
            $error = '';

            (new MinMaxValidator([
                'min' => $field->min ? (int)$field->min : null,
                'max' => $field->max ? (int)$field->max : null
            ]))->validate($query, $error);

            if (!empty($error)) {
                return false;
            }
        }

        return parent::save($query);
    }

    /**
     * @param SortableAssociationQueryInterface $query
     * @return Integrations|null
     */
    protected function resolveFieldFromQuery(
        SortableAssociationQueryInterface $query
    ) {
        if (null === ($fieldId = $this->resolveStringAttribute($query, 'field'))) {
            return null;
        }

        return $this->fieldService()->findById($fieldId);
    }
}
