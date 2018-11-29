<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\integration\db\IntegrationAssociationQuery;
use yii\db\ActiveRecord;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $id
 * @property int $elementId
 * @property int $objectId
 */
trait NormalizeValueTrait
{
    /**
     * @return string
     */
    abstract public static function recordClass(): string;

    /*******************************************
     * NORMALIZE VALUE
     *******************************************/

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
     * @param ElementInterface|null $element
     * @return IntegrationAssociationQuery
     */
    protected function getQuery(
        ElementInterface $element = null
    ): IntegrationAssociationQuery {

        /** @var ActiveRecord $recordClass */
        $recordClass = static::recordClass();

        /** @var IntegrationAssociationQuery $query */
        $query = $recordClass::find();

        $query
            ->field($this->id)
            ->element($element)
            ->site($this->targetSiteId($element));

        if ($this->max !== null) {
            $query->limit($this->max);
        }

        return $query;
    }

    /**
     * @param IntegrationAssociationQuery $query
     * @param ElementInterface|null $element
     */
    protected function normalizeQuery(
        IntegrationAssociationQuery $query,
        ElementInterface $element = null
    ) {
        $query->element = (
            $element === null || $element->getId() === null
        ) ? false : $element->getId();
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
        $this->normalizeQuery($query, $element);

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
     * @param ElementInterface|null $element
     * @return IntegrationAssociationQuery
     */
    protected function normalizeQueryInputValue(
        $value,
        int &$sortOrder,
        ElementInterface $element = null
    ): IntegrationAssociationQuery {

        if (is_string($value)) {
            $value = [
                'objectId' => $value
            ];
        }

        $recordClass = static::recordClass();

        return new $recordClass(array_merge(
            (array)$value,
            [
                'field' => $this,
                'element' => $element,
                'siteId' => $this->targetSiteId($element),
                'sortOrder' => $sortOrder++
            ]
        ));
    }

    /**
     * @param IntegrationAssociationQuery $query
     */
    protected function normalizeQueryEmptyValue(
        IntegrationAssociationQuery $query
    ) {
        $query->setCachedResult([]);
    }


    /**
     * Returns the site ID that target elements should have.
     *
     * @param ElementInterface|Element|null $element
     *
     * @return int
     */
    protected function targetSiteId(ElementInterface $element = null): int
    {
        /** @var Element $element */
        if (Craft::$app->getIsMultiSite() === true && $element !== null) {
            return $element->siteId;
        }

        return Craft::$app->getSites()->currentSite->id;
    }
}
