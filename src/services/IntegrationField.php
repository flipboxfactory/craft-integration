<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\services;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\StringHelper;
use flipbox\craft\integration\db\IntegrationAssociationQuery;
use flipbox\craft\integration\events\RegisterIntegrationFieldActionsEvent;
use flipbox\craft\integration\fields\actions\IntegrationActionInterface;
use flipbox\craft\integration\fields\actions\IntegrationItemActionInterface;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\integration\web\assets\integrations\Integrations as IntegrationsAsset;
use flipbox\craft\sortable\associations\db\SortableAssociationQueryInterface;
use flipbox\craft\sortable\associations\records\SortableAssociationInterface;
use flipbox\craft\sortable\associations\services\SortableFields;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class IntegrationField extends SortableFields
{
    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = IntegrationAssociation::TARGET_ATTRIBUTE;

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = IntegrationAssociation::SOURCE_ATTRIBUTE;

    /**
     * @return IntegrationAssociations
     */
    abstract protected function associationService(): IntegrationAssociations;

    /**
     * @var Integrations[]
     */
    private $fields = [];

    /**
     * @var IntegrationActionInterface[]
     */
    protected $defaultAvailableActions = [];

    /**
     * @var IntegrationItemActionInterface[]
     */
    protected $defaultAvailableItemActions = [];

    /**
     * @param int $id
     * @return Integrations|null
     */
    public function findById(int $id)
    {
        if (!array_key_exists($id, $this->fields)) {
            $objectField = Craft::$app->getFields()->getFieldById($id);
            if (!$objectField instanceof Integrations) {
                $objectField = null;
            }

            $this->fields[$id] = $objectField;
        }

        return $this->fields[$id];
    }


    /**
     * @inheritdoc
     * @param Integrations $field
     * @return IntegrationAssociationQuery
     * @throws Exception
     */
    public function getQuery(
        FieldInterface $field,
        ElementInterface $element = null
    ): SortableAssociationQueryInterface {
        $query = $this->baseQuery($field, $element);

        /** @var Integrations $field */

        if ($field->max !== null) {
            $query->limit($field->max);
        }

        return $query;
    }

    /**
     * @param FieldInterface $field
     * @param ElementInterface|null $element
     * @return IntegrationAssociationQuery
     * @throws Exception
     */
    private function baseQuery(
        FieldInterface $field,
        ElementInterface $element = null
    ): IntegrationAssociationQuery {
        if (!$field instanceof Integrations) {
            throw new Exception(sprintf(
                "The field must be an instance of '%s', '%s' given.",
                (string)Integrations::class,
                (string)get_class($field)
            ));
        }

        /** @var IntegrationAssociationQuery $query */
        $query = $this->associationService()->getQuery()
            ->field($field->id)
            ->site($this->targetSiteId($element));

        $query->element = $element;

        return $query;
    }


    /*******************************************
     * NORMALIZE VALUE
     *******************************************/

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    protected function normalizeQueryInputValue(
        FieldInterface $field,
        $value,
        int &$sortOrder,
        ElementInterface $element = null
    ): SortableAssociationInterface {
        if (!$field instanceof Integrations) {
            throw new Exception(sprintf(
                "The field must be an instance of '%s', '%s' given.",
                (string)Integrations::class,
                (string)get_class($field)
            ));
        }

        if (is_array($value)) {
            $value = StringHelper::toString($value);
        }

        return $this->associationService()->create(
            [
                'field' => $field,
                'element' => $element,
                'objectId' => $value,
                'siteId' => $this->targetSiteId($element),
                'sortOrder' => $sortOrder++
            ]
        );
    }

    /**
     * @param Integrations $field
     * @param IntegrationAssociationQuery $query
     * @param ElementInterface|null $element
     * @param bool $static
     * @return null|string
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getInputHtml(
        Integrations $field,
        IntegrationAssociationQuery $query,
        ElementInterface $element = null,
        bool $static
    ) {
        Craft::$app->getView()->registerAssetBundle(IntegrationsAsset::class);

        return Craft::$app->getView()->renderTemplate(
            $field::INPUT_TEMPLATE_PATH,
            $this->inputHtmlVariables($field, $query, $element, $static)
        );
    }

    /**
     * @param Integrations $field
     * @param IntegrationAssociationQuery $query
     * @param ElementInterface|null $element
     * @param bool $static
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function inputHtmlVariables(
        Integrations $field,
        IntegrationAssociationQuery $query,
        ElementInterface $element = null,
        bool $static
    ): array {
        return [
            'field' => $field,
            'element' => $element,
            'value' => $query,
            'objectLabel' => $this->getObjectLabel($field),
            'static' => $static,
            'itemTemplate' => $field::INPUT_ITEM_TEMPLATE_PATH,
            'settings' => [
                'translationCategory' => $field::TRANSLATION_CATEGORY,
                'limit' => $field->max ? $field->max : null,
                'data' => [
                    'field' => $field->id,
                    'element' => $element ? $element->getId() : null
                ],
                'actions' => $this->getActionHtml($field, $element),
                'actionAction' => $field::ACTION_PREFORM_ACTION_PATH,
                'createItemAction' => $field::ACTION_CREATE_ITEM_PATH,
                'itemData' => [
                    'field' => $field->id,
                    'element' => $element ? $element->getId() : null
                ],
                'itemSettings' => [
                    'translationCategory' => $field::TRANSLATION_CATEGORY,
                    'actionAction' => $field::ACTION_PREFORM_ITEM_ACTION_PATH,
                    'associateAction' => $field::ACTION_ASSOCIATION_ITEM_PATH,
                    'dissociateAction' => $field::ACTION_DISSOCIATION_ITEM_PATH,
                    'data' => [
                        'field' => $field->id,
                        'element' => $element ? $element->getId() : null
                    ],
                    'actions' => $this->getItemActionHtml($field, $element),
                ]
            ]
        ];
    }

    /**
     * @param Integrations $field
     * @return string
     * @throws Exception
     * @throws \Twig_Error_Loader
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettingsHtml(
        Integrations $field
    ) {
        return Craft::$app->getView()->renderTemplate(
            $field::SETTINGS_TEMPLATE_PATH,
            $this->settingsHtmlVariables($field)
        );
    }

    /**
     * @param Integrations $field
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function settingsHtmlVariables(Integrations $field): array
    {
        return [
            'field' => $field,
            'availableActions' => $this->getAvailableActions($field),
            'availableItemActions' => $this->getAvailableItemActions($field),
            'translationCategory' => $field::TRANSLATION_CATEGORY,
        ];
    }


    /*******************************************
     * OBJECT
     *******************************************/

    /**
     * @param Integrations $field
     * @return string
     */
    public function getObjectLabel(Integrations $field): string
    {
        return StringHelper::titleize($field->object);
    }

    /*******************************************
     * ACTIONS
     *******************************************/

    /**
     * @param Integrations $field
     * @return IntegrationActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getAvailableActions(Integrations $field): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => $this->defaultAvailableActions
        ]);

        $field->trigger(
            $field::EVENT_REGISTER_AVAILABLE_ACTIONS,
            $event
        );

        return $this->resolveActions(
            array_filter((array)$event->actions),
            IntegrationActionInterface::class
        );
    }

    /**
     * @param Integrations $field
     * @param ElementInterface|null $element
     * @return IntegrationActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getActions(Integrations $field, ElementInterface $element = null): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => $field->selectedActions,
            'element' => $element
        ]);

        $field->trigger(
            $field::EVENT_REGISTER_ACTIONS,
            $event
        );

        return $this->resolveActions(
            array_filter((array)$event->actions),
            IntegrationActionInterface::class
        );
    }

    /**
     * @param Integrations $field
     * @return IntegrationActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getAvailableItemActions(Integrations $field): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => $this->defaultAvailableItemActions
        ]);

        $field->trigger(
            $field::EVENT_REGISTER_AVAILABLE_ITEM_ACTIONS,
            $event
        );

        return $this->resolveActions(
            array_filter((array)$event->actions),
            IntegrationItemActionInterface::class
        );
    }

    /**
     * @param Integrations $field
     * @param ElementInterface|null $element
     * @return IntegrationItemActionInterface[]
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getItemActions(Integrations $field, ElementInterface $element = null): array
    {
        $event = new RegisterIntegrationFieldActionsEvent([
            'actions' => $field->selectedItemActions,
            'element' => $element
        ]);

        $field->trigger(
            $field::EVENT_REGISTER_ITEM_ACTIONS,
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

    /**
     * @param Integrations $field
     * @param ElementInterface|null $element
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getActionHtml(Integrations $field, ElementInterface $element = null): array
    {
        $actionData = [];

        foreach ($this->getActions($field, $element) as $action) {
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
     * @param Integrations $field
     * @param ElementInterface|null $element
     * @return array
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getItemActionHtml(Integrations $field, ElementInterface $element = null): array
    {
        $actionData = [];

        foreach ($this->getItemActions($field, $element) as $action) {
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
}
