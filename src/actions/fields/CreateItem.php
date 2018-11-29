<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\actions\fields;

use Craft;
use craft\base\ElementInterface;
use flipbox\craft\ember\actions\ManageTrait;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\integration\actions\ResolverTrait;
use flipbox\craft\integration\fields\Integrations;
use flipbox\craft\integration\records\IntegrationAssociation;
use yii\base\Action;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class CreateItem extends Action
{
    use ManageTrait,
        ResolverTrait;

    /**
     * @param string $field
     * @param string $element
     * @param string|null $id
     * @return mixed
     * @throws HttpException
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function run(string $field, string $element, string $id = null)
    {
        $field = $this->resolveField($field);
        $element = $this->resolveElement($element);

        $recordClass = $field::recordClass();

        $record = new $recordClass([
            'field' => $field,
            'element' => $element,
            'objectId' => $id,
            'siteId' => SiteHelper::ensureSiteId($element->siteId),
        ]);

        return $this->runInternal($field, $element, $record);
    }

    /**
     * @param Integrations $field
     * @param ElementInterface $element
     * @param IntegrationAssociation $record
     * @return mixed
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(
        Integrations $field,
        ElementInterface $element,
        IntegrationAssociation $record
    ) {
        // Check access
        if (($access = $this->checkAccess($field, $element, $record)) !== true) {
            return $access;
        }

        if (null === ($html = $this->performAction($field, $record))) {
            return $this->handleFailResponse($html);
        }

        return $this->handleSuccessResponse($html);
    }

    /**
     * @param Integrations $field
     * @param IntegrationAssociation $record
     * @return array
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function performAction(
        Integrations $field,
        IntegrationAssociation $record
    ): array {

        $view = Craft::$app->getView();

        return [
            'html' => $view->renderTemplate(
                $field::INPUT_ITEM_TEMPLATE_PATH,
                [
                    'field' => $field,
                    'record' => $record,
                    'translationCategory' => $field::TRANSLATION_CATEGORY
                ]
            ),
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml()
        ];
    }
}
