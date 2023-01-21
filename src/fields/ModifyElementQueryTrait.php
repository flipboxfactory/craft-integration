<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields;

use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Db;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @property int|null id
 */
trait ModifyElementQueryTrait
{
    /**
     * @return string
     */
    abstract protected static function tableAlias(): string;

    /**
     * @inheritdoc
     */
    public function modifyElementsQuery(ElementQueryInterface $query, mixed $value): void
    {
        if ($value === null || !$query instanceof ElementQuery) {
            return;
        }

        if ($value === false) {
            throw new QueryAbortedException();
        }

        if (is_string($value)) {
            $this->modifyElementsQueryForStringValue($query, $value);
            return;
        }

        $this->modifyElementsQueryForTargetValue($query, $value);
        return;
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


        $alias = $this->tableAlias();
        $name = '{{%' . $this->tableAlias() . '}}';

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
                $this->tableAlias(),
                '{{%' . $this->tableAlias() . '}}',
                $operator
            )
        );
    }

    /**
     * @param string $alias
     * @param string $name
     * @param string $operator
     * @return string
     */
    protected function emptyValueSubSelect(
        string $alias,
        string $name,
        string $operator
    ): string {


        return "(select count([[{$alias}.elementId]]) from " .
            $name .
            " {{{$alias}}} where [[{$alias}.elementId" .
            "]] = [[elements.id]] and [[{$alias}.fieldId]] = {$this->id}) {$operator} 0";
    }
}
