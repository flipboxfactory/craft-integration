<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\services;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\errors\ElementNotFoundException;
use flipbox\craft\integration\db\IntegrationAssociationQuery;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\craft\sortable\associations\services\SortableAssociations;
use flipbox\ember\exceptions\NotFoundException;
use flipbox\ember\helpers\SiteHelper;
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
     * @inheritdoc
     * @return IntegrationAssociationQuery
     */
    public function getQuery($config = []): SortableAssociationQueryInterface
    {
        return $this->parentGetQuery($config);
    }

    /**
     * @param ElementInterface $element
     * @param Integrations $field
     * @return string|null
     */
    public function findObjectIdByElement(ElementInterface $element, Integrations $field)
    {
        /** @var Element $element */
        return $this->findObjectId($element->getId(), $field->id, $element->siteId);
    }

    /**
     * @param int $elementId
     * @param int $fieldId
     * @param int|null $siteId
     * @return null|string
     */
    public function findObjectId(int $elementId, int $fieldId, int $siteId = null)
    {
        $objectId = $this->getQuery([
            'select' => ['objectId'],
            'elementId' => $elementId,
            'siteId' => SiteHelper::ensureSiteId($siteId),
            'fieldId' => $fieldId
        ])->scalar();

        return is_string($objectId) ? $objectId : null;
    }

    /**
     * @param int $elementId
     * @param int $fieldId
     * @param int|null $siteId
     * @return string
     * @throws NotFoundException
     */
    public function getObjectId(int $elementId, int $fieldId, int $siteId = null): string
    {
        $siteId = SiteHelper::ensureSiteId($siteId);

        if (null === ($objectId = $this->findObjectId($elementId, $fieldId, $siteId))) {
            throw new NotFoundException(sprintf(
                "Unable to find integration with: Element Id: %s, Field Id: %s, Site Id: $%s",
                $elementId,
                $fieldId,
                $siteId
            ));
        }

        return $objectId;
    }

    /**
     * @param string $objectId
     * @param int $fieldId
     * @param int|null $siteId
     * @return null|string
     */
    public function findElementId(string $objectId, int $fieldId, int $siteId = null)
    {
        $elementId = $this->getQuery([
            'select' => ['elementId'],
            'objectId' => $objectId,
            'siteId' => SiteHelper::ensureSiteId($siteId),
            'fieldId' => $fieldId
        ])->scalar();

        return is_string($elementId) ? $elementId : null;
    }

    /**
     * @param string $objectId
     * @param int $fieldId
     * @param int|null $siteId
     * @return string
     * @throws NotFoundException
     */
    public function getElementId(string $objectId, int $fieldId, int $siteId = null): string
    {
        $siteId = SiteHelper::ensureSiteId($siteId);

        if (null === ($elementId = $this->findElementId($objectId, $fieldId, $siteId))) {
            throw new NotFoundException(sprintf(
                "Unable to find element with: HubSpot Id: %s, Field Id: %s, Site Id: $%s",
                $objectId,
                $fieldId,
                $siteId
            ));
        }

        return $elementId;
    }

    /**
     * @param string $objectId
     * @param int $fieldId
     * @param int|null $siteId
     * @return ElementInterface|null
     */
    public function findElement(string $objectId, int $fieldId, int $siteId = null)
    {
        if (null === ($elementId = $this->findElementId($fieldId, $objectId, $siteId))) {
            return null;
        }

        return Craft::$app->getElements()->getELementById($elementId, null, $siteId);
    }

    /**
     * @param string $objectId
     * @param int $fieldId
     * @param int|null $siteId
     * @return ElementInterface
     * @throws ElementNotFoundException
     */
    public function getElement(string $objectId, int $fieldId, int $siteId = null): ElementInterface
    {
        $siteId = SiteHelper::ensureSiteId($siteId);

        if (!$element = $this->findElement($fieldId, $objectId, $siteId)) {
            throw new ElementNotFoundException(sprintf(
                "Unable to find element with: HubSpot Id: %s, Field Id: %s, Site Id: $%s",
                $objectId,
                $fieldId,
                $siteId
            ));
        }

        return $element;
    }

    /**
     * @param string $objectId
     * @param int $elementId
     * @param int $fieldId
     * @param int|null $siteId
     * @param int|null $sortOrder
     * @return bool
     */
    public function associateByIds(
        string $objectId,
        int $elementId,
        int $fieldId,
        int $siteId = null,
        int $sortOrder = null
    ): bool {
        return $this->create([
            'objectId' => $objectId,
            'elementId' => $elementId,
            'fieldId' => $fieldId,
            'siteId' => SiteHelper::ensureSiteId($siteId),
            'sortOrder' => $sortOrder
        ])->associate();
    }

    /**
     * @param string $objectId
     * @param int $elementId
     * @param int $fieldId
     * @param int|null $siteId
     * @param int|null $sortOrder
     * @return bool
     */
    public function dissociateByIds(
        string $objectId,
        int $elementId,
        int $fieldId,
        int $siteId = null,
        int $sortOrder = null
    ): bool {
        return $this->create([
            'objectId' => $objectId,
            'elementId' => $elementId,
            'fieldId' => $fieldId,
            'siteId' => SiteHelper::ensureSiteId($siteId),
            'sortOrder' => $sortOrder
        ])->dissociate();
    }

    /**
     * @inheritdoc
     * @param IntegrationAssociation $record
     * @return IntegrationAssociationQuery
     */
    protected function associationQuery(
        SortableAssociationInterface $record
    ): SortableAssociationQueryInterface {

        /** @var IntegrationAssociation $record */
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
