<?php namespace Limoncello\Tests\Passport\Data;

/**
 * Copyright 2015-2017 info@neomerx.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @package Limoncello\Tests\Templates
 */
class PassportSettings extends \Limoncello\Passport\Package\PassportSettings
{
    /**
     * @inheritdoc
     */
    protected function getApprovalUri(): string
    {
        return '/approve-uri';
    }

    /**
     * @inheritdoc
     */
    protected function getErrorUri(): string
    {
        return '/error-uri';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultClientId(): string
    {
        return 'default_client_id';
    }

    /**
     * @inheritdoc
     */
    protected function getUserTableName(): string
    {
        return 'user_table';
    }

    /**
     * @inheritdoc
     */
    protected function getUserPrimaryKeyName(): string
    {
        return 'id_user';
    }

    /**
     * @inheritdoc
     */
    protected function getUserCredentialsValidator(): callable
    {
        return [static::class, 'validateUser'];
    }

    /**
     * @param string $userName
     * @param string $password
     *
     * @return int|null
     */
    public static function validateUser(string $userName, string $password)
    {
        assert($userName || $password);

        return 123;
    }

}
