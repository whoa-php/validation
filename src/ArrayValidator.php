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

namespace Whoa\Validation;

use Whoa\Validation\Contracts\Execution\ContextStorageInterface;
use Whoa\Validation\Contracts\Rules\RuleInterface;
use Whoa\Validation\Execution\ContextStorage;
use Whoa\Validation\Validator\ArrayValidation;
use Whoa\Validation\Validator\BaseValidator;
use Psr\Container\ContainerInterface;

/**
 * @package Whoa\Validation
 */
class ArrayValidator extends BaseValidator
{
    use ArrayValidation;

    /**
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container;

    /**
     * @param RuleInterface[]|iterable $rules
     * @param ContainerInterface|null $container
     */
    public function __construct(iterable $rules, ContainerInterface $container = null)
    {
        parent::__construct();

        if (empty($rules) === false) {
            $this->setRules($rules);
        }

        $this->container = $container;
    }

    /**
     * @param RuleInterface[]|iterable $rules
     * @param ContainerInterface|null $container
     * @return self
     */
    public static function validator(iterable $rules = [], ContainerInterface $container = null): self
    {
        return new static($rules, $container);
    }

    /**
     * @inheritdoc
     */
    public function validate($input): bool
    {
        if ($this->areAggregatorsDirty() === true) {
            $this->resetAggregators();
        }

        $this->validateArrayImplementation($input, $this->getCaptureAggregator(), $this->getErrorAggregator());
        $this->markAggregatorsAsDirty();

        return $this->getErrorAggregator()->count() <= 0;
    }

    /**
     * @return ContainerInterface|null
     */
    protected function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * During validation, you can pass to rule your custom context which might have any additional
     * resources needed by your rules (extra properties, database connection settings, container, and etc).
     * @param array $blocks
     * @return ContextStorageInterface
     */
    protected function createContextStorageFromBlocks(array $blocks): ContextStorageInterface
    {
        return new ContextStorage($blocks, $this->getContainer());
    }
}
