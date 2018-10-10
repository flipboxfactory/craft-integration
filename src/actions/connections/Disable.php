<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections;

use flipbox\craft\integration\records\IntegrationConnection;
use flipbox\ember\actions\record\traits\Lookup;
use flipbox\ember\actions\record\traits\Manage;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.1
 */
abstract class Disable extends Action
{
    use Manage, Lookup {
        run as traitRun;
    }

    /**
     * @inheritdoc
     */
    public function run($connection)
    {
        return $this->traitRun($connection);
    }

    /**
     * @inheritdoc
     */
    protected function performAction(IntegrationConnection $record): bool
    {
        $record->enabled = false;
        return $record->save(true, ['enabled']);
    }
}
