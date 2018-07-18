<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\db;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\sortable\associations\db\SortableAssociationQuery;
use flipbox\craft\sortable\associations\db\traits\SiteAttribute;
use flipbox\ember\db\traits\ElementAttribute;
use flipbox\ember\helpers\QueryHelper;

/**
 * @method IntegrationAssociation[] getCachedResult()
 */
abstract class IntegrationAssociationQuery extends SortableAssociationQuery
{
    use traits\FieldAttribute,
        traits\ObjectAttribute,
        ElementAttribute,
        SiteAttribute;

    /**
     * @inheritdoc
     */
    protected function fixedOrderColumn(): string
    {
        return 'objectId';
    }

    /**
     * @param array $config
     * @return $this
     */
    public function configure(array $config)
    {
        QueryHelper::configure(
            $this,
            $config
        );

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws QueryAbortedException if it can be determined that there won’t be any results
     */
    public function prepare($builder)
    {
        // Is the query already doomed?
        if (($this->field !== null && empty($this->field)) ||
            ($this->object !== null && empty($this->object)) ||
            ($this->element !== null && empty($this->element))
        ) {
            throw new QueryAbortedException();
        }

        $this->applyConditions();
        $this->applySiteConditions();
        $this->applyObjectConditions();
        $this->applyFieldConditions();

        return parent::prepare($builder);
    }

    /**
     *  Apply query specific conditions
     */
    protected function applyConditions()
    {
        if ($this->element !== null) {
            $this->andWhere(Db::parseParam('elementId', $this->parseElementValue($this->element)));
        }
    }
}
