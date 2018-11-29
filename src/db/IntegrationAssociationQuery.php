<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\db;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\ElementAttributeTrait;
use flipbox\craft\ember\queries\FieldAttributeTrait;
use flipbox\craft\ember\queries\SiteAttributeTrait;
use flipbox\craft\integration\records\IntegrationAssociation;

/**
 * @method IntegrationAssociation[] getCachedResult()
 */
abstract class IntegrationAssociationQuery extends CacheableActiveQuery
{
    use AuditAttributesTrait,
        FieldAttributeTrait,
        ElementAttributeTrait,
        SiteAttributeTrait;

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

        if ($this->orderBy === null) {
            $this->orderBy = ['sortOrder' => SORT_ASC];
        }
    }

    /**
     * @inheritdoc
     * return static
     */
    public function sortOrder($value)
    {
        $this->sortOrder = $value;
        return $this;
    }

    /**
     * @var string|string[]|null
     */
    public $object;

    /**
     * @param string|string[]|null $value
     * @return static
     */
    public function setObjectId($value)
    {
        return $this->setObject($value);
    }

    /**
     * @param string|string[]|null $value
     * @return static
     */
    public function objectId($value)
    {
        return $this->setObject($value);
    }

    /**
     * @param string|string[]|null $value
     * @return static
     */
    public function setObject($value)
    {
        $this->object = $value;
        return $this;
    }

    /**
     * @param string|string[]|null $value
     * @return static
     */
    public function object($value)
    {
        return $this->setObject($value);
    }

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

        return parent::prepare($builder);
    }

    /**
     *  Apply query specific conditions
     */
    protected function applyConditions()
    {
        if ($this->object !== null) {
            $this->andWhere(Db::parseParam('objectId', $this->object));
        }

        if ($this->element !== null) {
            $this->andWhere(Db::parseParam('elementId', $this->parseElementValue($this->element)));
        }

        if ($this->field !== null) {
            $this->andWhere(Db::parseParam('fieldId', $this->parseFieldValue($this->field)));
        }

        if ($this->site !== null) {
            $this->andWhere(Db::parseParam('siteId', $this->parseSiteValue($this->site)));
        }

        if ($this->sortOrder !== null) {
            $this->andWhere(Db::parseParam('sortOrder', $this->sortOrder));
        }
    }
}
