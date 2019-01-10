<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\queries;

use Craft;
use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\ElementAttributeTrait;
use flipbox\craft\ember\queries\FieldAttributeTrait;
use flipbox\craft\ember\queries\SiteAttributeTrait;
use flipbox\craft\integration\records\IntegrationAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 *
 * @method IntegrationAssociation[] getCachedResult()
 * @method IntegrationAssociation[] all()
 * @method IntegrationAssociation one()
 */
class IntegrationAssociationQuery extends CacheableActiveQuery
{
    use AuditAttributesTrait,
        FieldAttributeTrait,
        ElementAttributeTrait,
        ObjectAttributeTrait,
        SiteAttributeTrait;

    /**
     * The sort order attribute
     */
    const SORT_ORDER_ATTRIBUTE = 'sortOrder';

    /**
     * The sort order direction
     */
    const SORT_ORDER_DIRECTION = SORT_ASC;

    /**
     * @var int|null Sort order
     */
    public $sortOrder;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->select === null) {
            $this->select = ['*'];
        }

        if ($this->orderBy === null && static::SORT_ORDER_ATTRIBUTE !== null) {
            $this->orderBy = [static::SORT_ORDER_ATTRIBUTE => static::SORT_ORDER_DIRECTION];
        }
    }

    /**
     * @param $value
     * @return $this
     */
    public function sortOrder($value)
    {
        $this->sortOrder = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setSortOrder($value)
    {
        return $this->sortOrder($value);
    }

    /**
     * @inheritdoc
     * @throws QueryAbortedException
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

        if ($this->sortOrder !== null) {
            $this->andWhere(Db::parseParam(static::SORT_ORDER_ATTRIBUTE, $this->sortOrder));
        }

        $this->applyElementConditions();
        $this->applyFieldConditions();
        $this->applyObjectConditions();
        $this->applySiteConditions();
        $this->applyAuditAttributeConditions();

        return parent::prepare($builder);
    }

    /**
     * Apply attribute conditions
     */
    protected function applyElementConditions()
    {
        if ($this->element !== null) {
            $this->andWhere(Db::parseParam('elementId', $this->parseElementValue($this->element)));
        }
    }

    /**
     * Apply attribute conditions
     */
    protected function applyFieldConditions()
    {
        if ($this->field !== null) {
            $this->andWhere(Db::parseParam('fieldId', $this->parseFieldValue($this->field)));
        }
    }

    /**
     * Apply attribute conditions
     */
    protected function applySiteConditions()
    {
        if ($this->site !== null) {
            $this->andWhere(Db::parseParam('siteId', $this->parseSiteValue($this->site)));
        } else {
            $this->andWhere(Db::parseParam('siteId', Craft::$app->getSites()->currentSite->id));
        }
    }
}
