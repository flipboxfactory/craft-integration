<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\queries\IntegrationAssociationQuery;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait ResolverTrait
{
    /**
     * @param string $field
     * @return Integrations
     * @throws HttpException
     */
    protected function resolveField(string $field): Integrations
    {
        $field = is_numeric($field) ?
            Craft::$app->getFields()->getFieldbyId($field) :
            Craft::$app->getFields()->getFieldByHandle($field);

        /** @var Integrations $field */

        if (!$field instanceof Integrations) {
            throw new HttpException(400, sprintf(
                "Field must be an instance of '%s', '%s' given.",
                Integrations::class,
                get_class($field)
            ));
        }

        return $field;
    }

    /**
     * @param string $element
     * @return ElementInterface|Element
     * @throws HttpException
     */
    protected function resolveElement(string $element): ElementInterface
    {
        if (null === ($element = Craft::$app->getElements()->getElementById($element))) {
            throw new HttpException(400, 'Invalid element');
        };

        return $element;
    }

    /**
     * @param Integrations $field
     * @param ElementInterface $element
     * @param string $id
     * @return array|mixed|null|\yii\base\BaseObject
     * @throws HttpException
     */
    protected function resolveRecord(Integrations $field, ElementInterface $element, string $id)
    {
        /** @var IntegrationAssociationQuery $query */
        if (null === ($query = $element->getFieldValue($field->handle))) {
            throw new HttpException(400, 'Field is not associated to element');
        }

        if (null === ($record = $query->objectId($id)->one())) {
            throw new HttpException(400, 'Invalid value');
        };

        return $record;
    }
}
