<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields\actions;

use craft\base\ElementInterface;
use craft\base\SavableComponentInterface;
use flipbox\craft\integration\fields\Integrations;

interface IntegrationActionInterface extends SavableComponentInterface
{
    /**
     * Returns whether this action is destructive in nature.
     *
     * @return bool Whether this action is destructive in nature.
     */
    public static function isDestructive(): bool;

    /**
     * Returns the action’s trigger label.
     *
     * @return string The action’s trigger label
     */
    public function getTriggerLabel(): string;

    /**
     * Returns the action’s trigger HTML.
     *
     * @return string|null The action’s trigger HTML.
     */
    public function getTriggerHtml();

    /**
     * Returns a confirmation message that should be displayed before the action is performed.
     *
     * @return string|null The confirmation message, if any.
     */
    public function getConfirmationMessage();

    /**
     * Performs the action.
     *
     * @param Integrations $field The field on which the action is occurring.
     * @param ElementInterface $element The element which the field is associated to
     * @return bool Whether the action was performed successfully.
     */
    public function performAction(Integrations $field, ElementInterface $element): bool;

    /**
     * Returns the message that should be displayed to the user after the action is performed.
     *
     * @return string|null The message that should be displayed to the user.
     */
    public function getMessage();
}
