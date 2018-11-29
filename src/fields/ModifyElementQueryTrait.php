<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields;

use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;
use flipbox\craft\integration\records\IntegrationAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $id
 * @property int $elementId
 * @property int $objectId
 */
trait ModifyElementQueryTrait
{
    /**
     * @return string
     */
    abstract public static function recordClass(): string;

    /*******************************************
     * MODIFY ELEMENT QUERY
     *******************************************/

    /**
     * @inheritdoc
     */
    public function modifyElementsQuery(
        ElementQueryInterface $query,
        $value
    ) {
        if ($value === null || !$query instanceof ElementQuery) {
            return null;
        }

        if ($value === false) {
            return false;
        }

        if (is_string($value)) {
            $this->modifyElementsQueryForStringValue($query, $value);
            return null;
        }

        $this->modifyElementsQueryForTargetValue($query, $value);
        return null;
    }

    /**
     * @param ElementQuery $query
     * @param string $value
     */
    protected function modifyElementsQueryForStringValue(
        ElementQuery $query,
        string $value
    ) {
        if ($value === 'not :empty:') {
            $value = ':notempty:';
        }

        if ($value === ':notempty:' || $value === ':empty:') {
            $this->modifyElementsQueryForEmptyValue($query, $value);
            return;
        }

        $this->modifyElementsQueryForTargetValue($query, $value);
    }

    /**
     * @param ElementQuery $query
     * @param $value
     */
    protected function modifyElementsQueryForTargetValue(
        ElementQuery $query,
        $value
    ) {
        /** @var IntegrationAssociation $recordClass */
        $recordClass = static::recordClass();

        $alias = $recordClass::tableAlias();
        $name = $recordClass::tableName();

        $joinTable = "{$name} {$alias}";
        $query->query->innerJoin($joinTable, "[[{$alias}.elementId]] = [[subquery.elementsId]]");
        $query->subQuery->innerJoin($joinTable, "[[{$alias}.elementId]] = [[elements.id]]");

        $query->subQuery->andWhere(
            Db::parseParam($alias . '.fieldId', $this->id)
        );

        $query->subQuery->andWhere(
            Db::parseParam($alias . '.objectId', $value)
        );

        $query->query->distinct(true);
    }

    /**
     * @param ElementQuery $query
     * @param string $value
     */
    protected function modifyElementsQueryForEmptyValue(
        ElementQuery $query,
        string $value
    ) {
        $operator = ($value === ':notempty:' ? '!=' : '=');
        $query->subQuery->andWhere(
            $this->emptyValueSubSelect(
                $operator
            )
        );
    }

    /**
     * @param string $operator
     * @return string
     */
    protected function emptyValueSubSelect(
        string $operator
    ): string {

        /** @var IntegrationAssociation $recordClass */
        $recordClass = static::recordClass();

        $alias = $recordClass::tableAlias();
        $name = $recordClass::tableName();

        return "(select count([[{$alias}.elementId]]) from " .
            $name .
            " {{{$alias}}} where [[{$alias}.elementId" .
            "]] = [[elements.id]] and [[{$alias}.fieldId]] = {$this->id}) {$operator} 0";
    }
}
