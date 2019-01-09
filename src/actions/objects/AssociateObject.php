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

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
class AssociateObject extends Action
{
    use ManageTrait,
        ResolverTrait;

    /**
     * Validate that the HubSpot Object exists prior to associating
     *
     * @var bool
     */
    public $validate = true;

    /**
     * @param IntegrationAssociation $record
     * @return bool
     * @throws \Exception
     */
    protected function validate(IntegrationAssociation $record): bool
    {
        return true;
    }

    /**
     * @param string $field
     * @param string $element
     * @param string $newObjectId
     * @param string|null $objectId
     * @param int|null $siteId
     * @param int|null $sortOrder
     * @return IntegrationAssociation
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
        $element = $this->resolveElement($element);

        $siteId = SiteHelper::ensureSiteId($siteId ?: $element->siteId);

        /** @var IntegrationAssociation $recordClass */
        $recordClass = $field::recordClass();

        // Find existing?
        if (!empty($objectId)) {
            $association = $recordClass::findOne([
                'element' => $element,
                'field' => $field,
                'objectId' => $objectId,
                'siteId' => $siteId,
            ]);
        }

        if (empty($association)) {
            /** @var IntegrationAssociation $association */
            $association = new $recordClass();
            $association->setField($field)
                ->setElement($element)
                ->setSiteId(SiteHelper::ensureSiteId($siteId ?: $element->siteId));
        }

        $association->objectId = $newObjectId;
        $association->sortOrder = $sortOrder;

        return $this->runInternal($association);
    }

    /**
     * @inheritdoc
     * @param IntegrationAssociation $record
     * @throws \Exception
     */
    protected function performAction(IntegrationAssociation $record): bool
    {
        if ($this->validate === true && !$this->validate($record)) {
            return false;
        }

        return $record->save();
    }
}
