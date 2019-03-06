<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\connections;

use craft\events\ModelEvent;
use yii\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.2.0
 */
abstract class AbstractSaveableConnection extends Model implements SavableConnectionInterface
{
    /**
     * @event ModelEvent The event that is triggered before the component is saved
     * You may set [[ModelEvent::isValid]] to `false` to prevent the component from getting saved.
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @event ModelEvent The event that is triggered after the component is saved
     */
    const EVENT_AFTER_SAVE = 'afterSave';

    /**
     * @event ModelEvent The event that is triggered before the component is deleted
     * You may set [[ModelEvent::isValid]] to `false` to prevent the component from getting deleted.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @event \yii\base\Event The event that is triggered after the component is deleted
     */
    const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        $event = new ModelEvent([
            'isNew' => $isNew,
        ]);
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);

        return $event->isValid;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew, array $changedAttributes)
    {
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new ModelEvent([
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);

        return $event->isValid;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE);
        }
    }
}
