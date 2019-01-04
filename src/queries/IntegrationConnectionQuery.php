<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-sortable-associations/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-sortable-associations
 */

namespace flipbox\craft\integration\queries;

use craft\db\QueryAbortedException;
use craft\helpers\Db;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\SiteAttributeTrait;
use flipbox\craft\integration\records\IntegrationConnection;

/**
 * @method IntegrationConnection[] getCachedResult()
 * @method IntegrationConnection[] all()
 * @method IntegrationConnection one()
 */
abstract class IntegrationConnectionQuery extends CacheableActiveQuery
{
    use AuditAttributesTrait,
        SiteAttributeTrait;

    /**
     * @var string|string[]|null
     */
    public $handle;

    /**
     * @var string|string[]|null
     */
    public $class;

    /**
     * @param $value
     * @return $this
     */
    public function handle($value)
    {
        $this->handle = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setHandle($value)
    {
        return $this->handle($value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function class($value)
    {
        $this->class = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setClass($value)
    {
        return $this->class($value);
    }

    /**
     * @inheritdoc
     * @throws QueryAbortedException
     */
    public function prepare($builder)
    {
        // Is the query already doomed?
        if (($this->handle !== null && empty($this->handle))) {
            throw new QueryAbortedException();
        }

        $this->applyAttributeConditions();
        $this->applyAuditAttributeConditions();

        return parent::prepare($builder);
    }

    /**
     * Prepares simple attributes
     */
    protected function applyAttributeConditions()
    {
        $attributes = [
            'class',
            'handle'
        ];

        foreach ($attributes as $property => $attribute) {
            $property = is_numeric($property) ? $attribute : $property;
            if (null !== ($value = $this->{$property})) {
                $this->andWhere(Db::parseParam($attribute, $value));
            }
        }
    }
}
