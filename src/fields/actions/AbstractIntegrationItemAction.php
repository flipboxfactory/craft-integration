<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields\actions;

use craft\base\ElementInterface;
use craft\base\SavableComponent;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\records\IntegrationAssociation;

abstract class AbstractIntegrationItemAction extends SavableComponent implements IntegrationItemActionInterface
{
    /**
     * The message that should be displayed to the user after the action is performed.
     *
     * @var string
     */
    private $message;

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return static::displayName();
    }

    /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
    }

    /**
     * @inheritdoc
     */
    public function performAction(Integrations $field, ElementInterface $element, IntegrationAssociation $record): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message that should be displayed to the user after the action is performed.
     *
     * @param string $message The message that should be displayed to the user after the action is performed.
     */
    protected function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'message'
            ]
        );
    }
}
