<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\exceptions;

use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.2.0
 */
class ConnectionNotFound extends Exception
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Connection Not Found';
    }
}
