<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\objects;

use flipbox\craft\integration\actions\traits\ResolverTrait;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\integration\services\IntegrationAssociations;
use flipbox\ember\actions\model\traits\Manage;
use flipbox\ember\exceptions\RecordNotFoundException;
use flipbox\ember\helpers\SiteHelper;
use yii\base\Action;
use yii\base\Model;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class Dissociate extends Action
{
    use Manage,
        ResolverTrait;

    /**
     * @return IntegrationAssociations
     */
    abstract protected function associationService(): IntegrationAssociations;

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

        return $this->runInternal($this->associationService()->create([
            'element' => $element,
            'field' => $field,
            'objectId' => $objectId,
            'siteId' => SiteHelper::ensureSiteId($siteId ?: $element->siteId),
        ]));
    }

    /**
     * @inheritdoc
     * @param IntegrationAssociation $model
     * @throws \flipbox\ember\exceptions\RecordNotFoundException
     * @throws \Exception
     */
    protected function performAction(Model $model): bool
    {
        if (!$model instanceof IntegrationAssociation) {
            throw new RecordNotFoundException(sprintf(
                "Association must be an instance of '%s', '%s' given.",
                IntegrationAssociation::class,
                get_class($model)
            ));
        }

        return $this->associationService()->dissociate(
            $model
        );
    }
}
