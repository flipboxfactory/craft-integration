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

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class Associate extends Action
{
    use Manage,
        ResolverTrait;

    /**
     * Validate that the HubSpot Object exists prior to associating
     *
     * @var bool
     */
    public $validate = true;

    /**
     * @return IntegrationAssociations
     */
    abstract protected function associationService(): IntegrationAssociations;

    /**
     * @param IntegrationAssociation $record
     * @return bool
     * @throws \Exception
     */
    abstract protected function validate(IntegrationAssociation $record): bool;

    /**
     * @param string $field
     * @param string $element
     * @param string $newObjectId
     * @param string|null $objectId
     * @param int|null $siteId
     * @param int|null $sortOrder
     * @return Model
     * @throws \flipbox\ember\exceptions\NotFoundException
     * @throws \yii\web\HttpException
     */
    public function run(
        string $field,
        string $element,
        string $newObjectId,
        string $objectId = null,
        int $siteId = null,
        int $sortOrder = null
    ) {
        // Resolve Field
        $field = $this->resolveField($field);

        // Resolve Element
        $element = $this->resolveElement($element);

        $siteId = SiteHelper::ensureSiteId($siteId ?: $element->siteId);

        // Find existing?
        if (!empty($objectId)) {
            $association = $this->associationService()->getByCondition([
                'element' => $element,
                'field' => $field,
                'objectId' => $objectId,
                'siteId' => $siteId,
            ]);
        } else {
            $association = $this->associationService()->create([
                'element' => $element,
                'field' => $field,
                'siteId' => $siteId,
            ]);
        }

        $association->objectId = $newObjectId;
        $association->sortOrder = $sortOrder;

        return $this->runInternal($association);
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

        if ($this->validate === true && !$this->validate($model)) {
            return false;
        }

        return $this->associationService()->associate(
            $model
        );
    }
}
