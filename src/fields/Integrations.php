<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\db\ElementQueryInterface;
use flipbox\craft\integration\db\IntegrationAssociationQuery;
use flipbox\craft\integration\fields\actions\IntegrationActionInterface;
use flipbox\craft\integration\fields\actions\IntegrationItemActionInterface;
use flipbox\craft\integration\records\IntegrationAssociation;
use flipbox\craft\integration\services\IntegrationAssociations;
use flipbox\craft\integration\services\IntegrationField;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\validators\MinMaxValidator;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class Integrations extends Field
{
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
     * @return IntegrationField
     */
    abstract protected function fieldService(): IntegrationField;

    /**
     * @return IntegrationAssociations
     */
    abstract protected function associationService(): IntegrationAssociations;

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
     * OBJECT
     *******************************************/

    /**
     * @return string
     */
    public function getObjectLabel(): string
    {
        return $this->fieldService()->getObjectLabel($this);
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
     * VALUE
     *******************************************/

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return $this->fieldService()->normalizeValue(
            $this,
            $value,
            $element
        );
    }


    /*******************************************
     * QUERY
     *******************************************/

    /**
     * @inheritdoc
     */
    public function modifyElementsQuery(ElementQueryInterface $query, $value)
    {
        return $this->fieldService()->modifyElementsQuery(
            $this,
            $query,
            $value
        );
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
     * @param IntegrationAssociationQuery $value
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
     * @param IntegrationAssociationQuery $value
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $value->limit(null);
        return $this->fieldService()->getInputHtml($this, $value, $element, false);
    }

    /**
     * @inheritdoc
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return $this->fieldService()->getSettingsHtml($this);
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        $this->associationService()->save(
            $element->getFieldValue($this->handle)
        );

        parent::afterElementSave($element, $isNew);
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
}
