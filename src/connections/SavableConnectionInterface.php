<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-integration/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-integration/
 */

namespace flipbox\craft\integration\connections;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.1.0
 */
interface SavableConnectionInterface
{
    /**
     * Returns the display name of the connection.
     *
     * @return string
     */
    public static function displayName(): string;

    /**
     * Returns the settings html for the connection
     *
     * @return string|null
     */
    public function getSettingsHtml();

    /**
     * Validates the connection.
     *
     * @param string[]|null $attributeNames List of attribute names that should
     * be validated. If this parameter is empty, it means any attribute listed
     * in the applicable validation rules should be validated.
     * @param bool $clearErrors Whether existing errors should be cleared before
     * performing validation
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true);

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool;

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew, array $changedAttributes);

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool;

    /**
     * @inheritdoc
     */
    public function afterDelete();
}
