<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\objects;

use flipbox\craft\ember\actions\CheckAccessTrait;
use flipbox\craft\ember\actions\ResponseTrait;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\integration\actions\ResolverTrait;
use flipbox\craft\integration\records\IntegrationAssociation;
use yii\base\Action;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
class DissociateObject extends Action
{
    use ResolverTrait,
        CheckAccessTrait,
        ResponseTrait;

    /**
     * @var int
     */
    public $statusCodeSuccess = 201;

    /**
     * @param string $field
     * @param string $element
     * @param string $objectId
     * @param int|null $siteId
     * @return bool|mixed
     * @throws HttpException
     */
    public function run(
        string $field,
        string $element,
        string $objectId,
        int $siteId = null
    ) {

        $field = $this->resolveField($field);
        $element = $this->resolveElement($element);

        /** @var IntegrationAssociation $recordClass */
        $recordClass = $field::recordClass();

        $query = $recordClass::find();
        $query->setElement($element)
            ->setField($field)
            ->setObjectId($objectId)
            ->setSiteId(SiteHelper::ensureSiteId($siteId ?: $element->siteId));

        if (null === ($association = $query->one())) {
            return $this->handleSuccessResponse(true);
        }

        return $this->runInternal($association);
    }

    /**
     * @param IntegrationAssociation $data
     * @return mixed
     * @throws \yii\web\HttpException
     */
    protected function runInternal(IntegrationAssociation $data)
    {
        // Check access
        if (($access = $this->checkAccess($data)) !== true) {
            return $access;
        }

        if (!$this->performAction($data)) {
            return $this->handleFailResponse($data);
        }

        return $this->handleSuccessResponse($data);
    }

    /**
     * @param IntegrationAssociation $record
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function performAction(IntegrationAssociation $record): bool
    {
        return $record->delete();
    }
}
