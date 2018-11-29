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
use flipbox\craft\integration\fields\actions\IntegrationActionInterface;
use flipbox\craft\integration\fields\Integrations;
use yii\base\Action;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
abstract class PerformAction extends Action
{
    use ManageTrait,
        ResolverTrait;

    /**
     * @param string $field
     * @param string $element
     * @param string|null $action
     * @return mixed
     * @throws HttpException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function run(string $field, string $element, string $action = null)
    {
        $field = $this->resolveField($field);
        $element = $this->resolveElement($element);

        $availableActions = $field->getActions($element);

        foreach ($availableActions as $availableAction) {
            if ($action === get_class($availableAction)) {
                $action = $availableAction;
                break;
            }
        }

        if (!$action instanceof IntegrationActionInterface) {
            throw new HttpException(400, 'Field action is not supported by the field');
        }

        return $this->runInternal($action, $field, $element);
    }

    /**
     * @param IntegrationActionInterface $action
     * @param Integrations $field
     * @param ElementInterface $element
     * @return mixed
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(
        IntegrationActionInterface $action,
        Integrations $field,
        ElementInterface $element
    ) {
        // Check access
        if (($access = $this->checkAccess($action, $field, $element)) !== true) {
            return $access;
        }

        if (!$this->performAction($action, $field, $element)) {
            return $this->handleFailResponse($action);
        }

        return $this->handleSuccessResponse($action);
    }

    /**
     * @param IntegrationActionInterface $action
     * @param Integrations $field
     * @param ElementInterface $element
     * @return bool
     */
    public function performAction(
        IntegrationActionInterface $action,
        Integrations $field,
        ElementInterface $element
    ): bool {
        return $action->performAction($field, $element);
    }
}
