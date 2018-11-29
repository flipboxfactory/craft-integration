<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\connections;

use flipbox\craft\ember\actions\records\LookupRecordTrait;
use flipbox\craft\ember\actions\records\ManageRecordTrait;
use flipbox\craft\integration\records\IntegrationConnection;
use yii\base\Action;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.1.1
 */
abstract class DisableConnection extends Action
{
    use ManageRecordTrait, LookupRecordTrait {
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