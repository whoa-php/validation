<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
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

declare(strict_types=1);

namespace Whoa\Validation\Blocks;

use Whoa\Validation\Contracts\Blocks\ExecutionBlockInterface;
use Whoa\Validation\Contracts\Blocks\OrExpressionInterface;

/**
 * @package Whoa\Validation
 */
final class OrBlock implements OrExpressionInterface
{
    /**
     * @var ExecutionBlockInterface
     */
    private ExecutionBlockInterface $primary;

    /**
     * @var ExecutionBlockInterface
     */
    private ExecutionBlockInterface $secondary;

    /**
     * @var array
     */
    private array $properties;

    /**
     * @param ExecutionBlockInterface $primary
     * @param ExecutionBlockInterface $secondary
     * @param array $properties
     */
    public function __construct(
        ExecutionBlockInterface $primary,
        ExecutionBlockInterface $secondary,
        array $properties = []
    ) {
        $this->primary = $primary;
        $this->secondary = $secondary;
        $this->properties = $properties;
    }

    /**
     * @inheritdoc
     */
    public function getPrimary(): ExecutionBlockInterface
    {
        return $this->primary;
    }

    /**
     * @inheritdoc
     */
    public function getSecondary(): ExecutionBlockInterface
    {
        return $this->secondary;
    }

    /**
     * @inheritdoc
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
