<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\fields;

use craft\base\ElementInterface;
use flipbox\craft\ember\actions\ManageTrait;
use flipbox\craft\integration\actions\ResolverTrait;
use flipbox\craft\integration\fields\actions\IntegrationItemActionInterface;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\records\IntegrationAssociation;
use yii\base\Action;
use yii\web\HttpException;

/**
 * Performs an action on an individual field row
 *
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class PerformFieldItemAction extends Action
{
    use ManageTrait,
        ResolverTrait;

    /**
     * @param string $field
     * @param string $element
     * @param string|null $action
     * @param string|null $id
     * @return mixed
     * @throws HttpException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function run(string $field, string $element, string $action, string $id)
    {
        $field = $this->resolveField($field);
        $element = $this->resolveElement($element);
        $record = $this->resolveRecord($field, $element, $id);

        $availableActions = $field->getItemActions($element);

        foreach ($availableActions as $availableAction) {
            if ($action === get_class($availableAction)) {
                $action = $availableAction;
                break;
            }
        }

        if (!$action instanceof IntegrationItemActionInterface) {
            throw new HttpException(400, 'Field action is not supported by the field');
        }

        return $this->runInternal($action, $field, $element, $record);
    }

    /**
     * @param IntegrationItemActionInterface $action
     * @param Integrations $field
     * @param ElementInterface $element
     * @param IntegrationAssociation $record
     * @return mixed
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(
        IntegrationItemActionInterface $action,
        Integrations $field,
        ElementInterface $element,
        IntegrationAssociation $record
    ) {
    
        // Check access
        if (($access = $this->checkAccess($action, $field, $element, $record)) !== true) {
            return $access;
        }

        if (!$this->performAction($action, $field, $element, $record)) {
            return $this->handleFailResponse($action);
        }

        return $this->handleSuccessResponse($action);
    }

    /**
     * @param IntegrationItemActionInterface $action
     * @param Integrations $field
     * @param ElementInterface $element
     * @param IntegrationAssociation $record
     * @return bool
     */
    public function performAction(
        IntegrationItemActionInterface $action,
        Integrations $field,
        ElementInterface $element,
        IntegrationAssociation $record
    ): bool {
    
        return $action->performAction($field, $element, $record);
    }
}
