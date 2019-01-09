<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\StringHelper;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\integration\queries\IntegrationAssociationQuery;
use flipbox\craft\integration\records\IntegrationAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property int|null $max
 */
trait NormalizeValueTrait
{
    /**
     * @return string
     */
    abstract public static function recordClass(): string;

    /**
     * @param ElementInterface|Element|null $element
     * @return IntegrationAssociationQuery
     */
    protected function getQuery(ElementInterface $element = null): IntegrationAssociationQuery
    {
        /** @var IntegrationAssociation $recordClass */
        $recordClass = static::recordClass();

        $query = $recordClass::find();

        if ($this->max !== null) {
            $query->limit($this->max);
        }

        $query->field($this)
            ->siteId(SiteHelper::ensureSiteId($element === null ? null : $element->siteId))
            ->elementId(($element === null || $element->getId() === null
            ) ? false : $element->getId());

        return $query;
    }

    /**
     * @param $value
     * @param ElementInterface|null $element
     * @return IntegrationAssociationQuery
     */
    public function normalizeValue(
        $value,
        ElementInterface $element = null
    ) {
    

        if ($value instanceof IntegrationAssociationQuery) {
            return $value;
        }

        $query = $this->getQuery($element);
        $this->normalizeQueryValue($query, $value, $element);
        return $query;
    }

    /**
     * @param IntegrationAssociationQuery $query
     * @param $value
     * @param ElementInterface|null $element
     */
    protected function normalizeQueryValue(
        IntegrationAssociationQuery $query,
        $value,
        ElementInterface $element = null
    ) {
    

        if (is_array($value)) {
            $this->normalizeQueryInputValues($query, $value, $element);
            return;
        }

        if ($value === '') {
            $this->normalizeQueryEmptyValue($query);
            return;
        }
    }

    /**
     * @param IntegrationAssociationQuery $query
     * @param array $value
     * @param ElementInterface|null $element
     */
    protected function normalizeQueryInputValues(
        IntegrationAssociationQuery $query,
        array $value,
        ElementInterface $element = null
    ) {
    

        $models = [];
        $sortOrder = 1;
        foreach ($value as $val) {
            $models[] = $this->normalizeQueryInputValue($val, $sortOrder, $element);
        }
        $query->setCachedResult($models);
    }

    /**
     * @param $value
     * @param int $sortOrder
     * @param ElementInterface|Element|null $element
     * @return IntegrationAssociation
     */
    protected function normalizeQueryInputValue(
        $value,
        int &$sortOrder,
        ElementInterface $element = null
    ): IntegrationAssociation {
    

        if (is_array($value)) {
            $value = StringHelper::toString($value);
        }

        /** @var IntegrationAssociation $recordClass */
        $recordClass = static::recordClass();

        /** @var IntegrationAssociation $association */
        $association = new $recordClass();
        $association->setField($this)
            ->setElement($element)
            ->setSiteId(SiteHelper::ensureSiteId($element === null ? null : $element->siteId));

        $association->sortOrder = $sortOrder++;
        $association->objectId = $value;

        return $association;
    }

    /**
     * @param IntegrationAssociationQuery $query
     */
    protected function normalizeQueryEmptyValue(
        IntegrationAssociationQuery $query
    ) {
    
        $query->setCachedResult([]);
    }
}
