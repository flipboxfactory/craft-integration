<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\StringHelper;
use flipbox\craft\ember\helpers\ModelHelper;
use flipbox\craft\ember\validators\MinMaxValidator;
use flipbox\craft\integration\events\RegisterIntegrationFieldActionsEvent;
use flipbox\craft\integration\fields\actions\IntegrationActionInterface;
use flipbox\craft\integration\fields\actions\IntegrationItemActionInterface;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\integration\web\assets\integrations\Integrations as IntegrationsAsset;
use yii\db\ActiveQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
abstract class Integrations extends Field
{
    use NormalizeValueTrait,
        ModifyElementQueryTrait;

    /**
     * The Plugin's translation category
     */
    const TRANSLATION_CATEGORY = '';

    /**
     * The action path to preform field actions
     */
    const ACTION_PREFORM_ACTION_PATH = '';

    /**
     * The action path to preform field actions
     */
    const ACTION_PREFORM_ITEM_ACTION_PATH = '';

    /**
     * The action path to associate an item
     */
    const ACTION_ASSOCIATION_ITEM_PATH = '';

    /**
     * The action path to dissociate an item
     */
    const ACTION_DISSOCIATION_ITEM_PATH = '';

    /**
     * The action path to create an integration object
     */
    const ACTION_CREATE_ITEM_PATH = '';

    /**
     * The action event name
     */
    const EVENT_REGISTER_ACTIONS = 'registerActions';

    /**
     * The action event name
     */
    const EVENT_REGISTER_AVAILABLE_ACTIONS = 'registerAvailableActions';

    /**
     * The item action event name
     */
    const EVENT_REGISTER_ITEM_ACTIONS = 'registerItemActions';

    /**
     * The item action event name
     */
    const EVENT_REGISTER_AVAILABLE_ITEM_ACTIONS = 'registerAvailableItemActions';

    /**
     * The default action event name
     */
    const DEFAULT_AVAILABLE_ACTIONS = [];

    /**
     * The default item action event name
     */
    const DEFAULT_AVAILABLE_ITEM_ACTIONS = [];

    /**
     * The input template path
     */
    const INPUT_TEMPLATE_PATH = '';

    /**
     * The input template path
     */
    const INPUT_ITEM_TEMPLATE_PATH = '_inputItem';

    /**
     * The input template path
     */
    const SETTINGS_TEMPLATE_PATH = '';

    /**
     * @var string
     */
    public $object;

    /**
     * @var int|null
     */
    public $min;

    /**
     * @var int|null
     */
    public $max;

    /**
     * @var string
     */
    public $viewUrl = '';

    /**
     * @var string
     */
    public $listUrl = '';

    /**
     * @var IntegrationActionInterface[]
     */
    public $selectedActions = [];

    /**
     * @var IntegrationItemActionInterface[]
     */
    public $selectedItemActions = [];

    /**
     * @var string|null
     */
    public $selectionLabel;

    /**
     * @return string
     */
    abstract public static function recordClass(): string;

    /*******************************************
     * OBJECT
     *******************************************/

    /**
     * @return string
     */
    public function getObjectLabel(): string
    {
        return StringHelper::titleize($this->object);
    }

    /**
     * @inheritdoc
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t(static::TRANSLATION_CATEGORY, 'Add an Object');
    }


    /*******************************************
     * VALIDATION
     *******************************************/

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return [
            [
                MinMaxValidator::class,
                'min' => $this->min ? (int)$this->min : null,
                'max' => $this->max ? (int)$this->max : null,
                'tooFew' => Craft::t(
                    static::TRANSLATION_CATEGORY,
                    '{attribute} should contain at least {min, number} {min, plural, one{domain} other{domains}}.'
                ),
                'tooMany' => Craft::t(
                    static::TRANSLATION_CATEGORY,
                    '{attribute} should contain at most {max, number} {max, plural, one{domain} other{domains}}.'
                ),
                'skipOnEmpty' => false
            ]
        ];
    }

    /*******************************************
     * RULES
     *******************************************/

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    'object',
                    'required',
                    'message' => Craft::t(static::TRANSLATION_CATEGORY, 'Object cannot be empty.')
                ],
                [
                    [
                        'object',
                        'min',
                        'max',
                        'viewUrl',
                        'listUrl',
                        'selectionLabel'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }


    /*******************************************
     * SEARCH
     *******************************************/

    /**
     * @param ActiveQuery $value
     * @inheritdoc
     */
    public function getSearchKeywords($value, ElementInterface $element): string
    {
        $objects = [];

        /** @var IntegrationAssociation $association */
        foreach ($value->all() as $association) {
            array_push($objects, $association->objectId);
        }

        return parent::getSearchKeywords($objects, $element);
    }


    /*******************************************
     * VIEWS
     *******************************************/

    /**
     * @inheritdoc
     * @param ActiveQuery $value
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $value->limit(null);

        Craft::$app->getView()->registerAssetBundle(IntegrationsAsset::class);

        return Craft::$app->getView()->renderTemplate(
            static::INPUT_TEMPLATE_PATH,
            $this->inputHtmlVariables($value, $element)
        );
    }

    /**
     * @param ActiveQuery $query
     * @param ElementInterface|null $element
     * @param bool $static
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function inputHtmlVariables(
        ActiveQuery $query,
        ElementInterface $element = null,
        bool $static = false
    ): array {
        return [
            'field' => $this,
            'element' => $element,
            'value' => $query,
            'objectLabel' => $this->getObjectLabel(),
            'static' => $static,
            'itemTemplate' => $this::INPUT_ITEM_TEMPLATE_PATH,
            'settings' => [
                'translationCategory' => $this::TRANSLATION_CATEGORY,
                'limit' => $this->max ? $this->max : null,
                'data' => [
                    'field' => $this->id,
                    'element' => $element ? $element->getId() : null
                ],
                'actions' => $this->getActionHtml($element),
                'actionAction' => $this::ACTION_PREFORM_ACTION_PATH,
                'createItemAction' => $this::ACTION_CREATE_ITEM_PATH,
                'itemData' => [
                    'field' => $this->id,
                    'element' => $element ? $element->getId() : null
                ],
                'itemSettings' => [
                    'translationCategory' => $this::TRANSLATION_CATEGORY,
                    'actionAction' => $this::ACTION_PREFORM_ITEM_ACTION_PATH,
                    'associateAction' => $this::ACTION_ASSOCIATION_ITEM_PATH,
                    'dissociateAction' => $this::ACTION_DISSOCIATION_ITEM_PATH,
                    'data' => [
                        'field' => $this->id,
                        'element' => $element ? $element->getId() : null
                    ],
                    'actions' => $this->getItemActionHtml($element),
                ]
            ]
        ];
    }

    /**
     * @param ElementInterface|null $element
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getActionHtml(ElementInterface $element = null): array
    {
        $actionData = [];

        foreach ($this->getActions($element) as $action) {
            $actionData[] = [
                'type' => get_class($action),
                'destructive' => $action->isDestructive(),
                'name' => $action->getTriggerLabel(),
                'trigger' => $action->getTriggerHtml(),
                'confirm' => $action->getConfirmationMessage(),
            ];
        }

        return $actionData;
    }

    /**
     * @param ElementInterface|null $element
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getItemActionHtml(ElementInterface $element = null): array
    {
        $actionData = [];

        foreach ($this->getItemActions($element) as $action) {
            $actionData[] = [
                'type' => get_class($action),
                'destructive' => $action->isDestructive(),
                'name' => $action->getTriggerLabel(),
                'trigger' => $action->getTriggerHtml(),
                'confirm' => $action->getConfirmationMessage(),
            ];
        }

        return $actionData;
    }


    /*******************************************
     * SETTINGS
     *******************************************/

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            static::SETTINGS_TEMPLATE_PATH,
            $this->settingsHtmlVariables()
        );
    }

    /**
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function settingsHtmlVariables(): array
    {
        return [
            'field' => $this,
            'availableActions' => $this->getAvailableActions(),
            'availableItemActions' => $this->getAvailableItemActions(),
            'translationCategory' => static::TRANSLATION_CATEGORY,
        ];
    }


    /*******************************************
     * ACTIONS
     *******************************************/

    /**
     * @return IntegrationActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getAvailableActions(): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => static::DEFAULT_AVAILABLE_ACTIONS
        ]);

        $this->trigger(
            $this::EVENT_REGISTER_AVAILABLE_ACTIONS,
            $event
        );

        return $this->resolveActions(
            array_filter((array)$event->actions),
            IntegrationActionInterface::class
        );
    }

    /**
     * @return IntegrationActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getAvailableItemActions(): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => static::DEFAULT_AVAILABLE_ITEM_ACTIONS
        ]);

        $this->trigger(
            $this::EVENT_REGISTER_AVAILABLE_ITEM_ACTIONS,
            $event
        );

        return $this->resolveActions(
            array_filter((array)$event->actions),
            IntegrationItemActionInterface::class
        );
    }

    /**
     * @param ElementInterface|null $element
     * @return IntegrationActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getActions(ElementInterface $element = null): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => $this->selectedActions,
            'element' => $element
        ]);

        $this->trigger(
            static::EVENT_REGISTER_ACTIONS,
            $event
        );

        return $this->resolveActions(
            array_filter((array)$event->actions),
            IntegrationActionInterface::class
        );
    }

    /**
     * @param ElementInterface|null $element
     * @return IntegrationItemActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getItemActions(ElementInterface $element = null): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => $this->selectedItemActions,
            'element' => $element
        ]);

        $this->trigger(
            static::EVENT_REGISTER_ITEM_ACTIONS,
            $event
        );

        return $this->resolveActions(
            array_filter((array)$event->actions),
            IntegrationItemActionInterface::class
        );
    }

    /**
     * @param array $actions
     * @param string $instance
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function resolveActions(array $actions, string $instance)
    {
        foreach ($actions as $i => $action) {
            // $action could be a string or config array
            if (!$action instanceof $instance) {
                $actions[$i] = $action = ComponentHelper::createComponent($action, $instance);

                if ($actions[$i] === null) {
                    unset($actions[$i]);
                }
            }
        }

        return array_values($actions);
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @inheritdoc
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        $query = $element->getFieldValue($this->handle);

        (new MinMaxValidator([
            'min' => $this->min ? (int)$this->min : null,
            'max' => $this->max ? (int)$this->max : null
        ]))->validate($query, $error);

        if (!empty($error)) {
            return false;
        }

        return parent::afterElementSave($element, $isNew);
    }

    /*******************************************
     * SETTINGS
     *******************************************/

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        return array_merge(
            [
                'object',
                'min',
                'max',
                'viewUrl',
                'listUrl',
                'selectedActions',
                'selectedItemActions',
                'selectionLabel'
            ],
            parent::settingsAttributes()
        );
    }

    /**
     * Returns the site ID that target elements should have.
     *
     * @param ElementInterface|Element|null $element
     *
     * @return int
     */
    protected function targetSiteId(ElementInterface $element = null): int
    {
        /** @var Element $element */
        if (Craft::$app->getIsMultiSite() === true && $element !== null) {
            return $element->siteId;
        }

        return Craft::$app->getSites()->currentSite->id;
    }
}
