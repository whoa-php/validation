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

namespace Whoa\Validation\Contracts\Rules;

use Whoa\Validation\Contracts\Blocks\ExecutionBlockInterface;

/**
 * @package Whoa\Validation
 */
interface RuleInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return RuleInterface
     */
    public function setName(string $name): self;

    /**
     * @return self
     */
    public function unsetName(): self;

    /**
     * @return bool
     */
    public function isCaptureEnabled(): bool;

    /**
     * @return self
     */
    public function enableCapture(): self;

    /**
     * @return self
     */
    public function disableCapture(): self;

    /**
     * @return RuleInterface|null
     */
    public function getParent(): ?RuleInterface;

    /**
     * @param RuleInterface $rule
     *
     * @return self
     */
    public function setParent(RuleInterface $rule): self;

    /**
     * @return self
     */
    public function unsetParent(): self;

    /**
     * @return ExecutionBlockInterface
     */
    public function toBlock(): ExecutionBlockInterface;
}
