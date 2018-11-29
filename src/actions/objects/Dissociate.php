<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\objects;

use flipbox\craft\ember\actions\ManageTrait;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\integration\actions\ResolverTrait;
use flipbox\craft\integration\records\IntegrationAssociation;
use yii\base\Action;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class Dissociate extends Action
{
    use ManageTrait,
        ResolverTrait;

    /**
     * @param string $field
     * @param string $element
     * @param string $objectId
     * @param int|null $siteId
     * @return mixed
     * @throws HttpException
     */
    public function run(
        string $field,
        string $element,
        string $objectId,
        int $siteId = null
    ) {
        // Resolve Field
        $field = $this->resolveField($field);

        // Resolve Element
        $element = $this->resolveElement($element);

        $recordClass = $field::recordClass();

        $record = new $recordClass(
            [
                'element' => $element,
                'field' => $field,
                'objectId' => $objectId,
                'siteId' => SiteHelper::ensureSiteId($siteId ?: $element->siteId),
            ]
        );

        return $this->runInternal($record);
    }

    /**
     * @inheritdoc
     * @param IntegrationAssociation $record
     * @throws \Exception
     */
    protected function performAction(IntegrationAssociation $record): bool
    {
        return $record->save();
    }
}
