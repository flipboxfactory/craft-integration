<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\events;

use craft\base\ElementInterface;
use flipbox\craft\integration\fields\actions\IntegrationActionInterface;
use flipbox\craft\integration\fields\actions\IntegrationItemActionInterface;
use yii\base\Event;

class RegisterIntegrationFieldActionsEvent extends Event
{
    /**
     * @var IntegrationActionInterface[]|IntegrationItemActionInterface[]
     */
    public $actions = [];

    /**
     * @var ElementInterface
     */
    public $element;
}
