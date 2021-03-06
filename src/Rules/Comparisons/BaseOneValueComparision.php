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

namespace Whoa\Validation\Rules\Comparisons;

use Whoa\Validation\Contracts\Blocks\ExecutionBlockInterface;
use Whoa\Validation\Contracts\Execution\ContextInterface;
use Whoa\Validation\Contracts\Rules\ComparisionInterface;
use Whoa\Validation\Rules\BaseRule;
use Whoa\Validation\Rules\Generic\Fail;
use Whoa\Validation\Rules\Generic\IfOperator;
use Whoa\Validation\Rules\Generic\Success;

use function assert;

/**
 * @package Whoa\Validation
 */
abstract class BaseOneValueComparision extends BaseRule implements ComparisionInterface
{
    /**
     * Property key.
     */
    public const PROPERTY_VALUE = self::PROPERTY_LAST + 1;

    /**
     * Property key.
     */
    public const PROPERTY_ONE_VALUE_LAST = self::PROPERTY_VALUE;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var int
     */
    private int $errorCode;

    /**
     * @var string
     */
    private string $messageTemplate;

    /**
     * @var array
     */
    private array $messageParams;

    /**
     * @param mixed $value
     * @param int $errorCode
     * @param string $messageTemplate
     * @param array $messageParams
     */
    public function __construct($value, int $errorCode, string $messageTemplate, array $messageParams)
    {
        assert($this->checkEachValueConvertibleToString($messageParams));

        $this->value = $value;
        $this->errorCode = $errorCode;
        $this->messageTemplate = $messageTemplate;
        $this->messageParams = $messageParams;
    }

    /**
     * @inheritdoc
     */
    public function toBlock(): ExecutionBlockInterface
    {
        $operator = new IfOperator(
            [static::class, 'compare'],
            new Success(),
            new Fail($this->getErrorCode(), $this->getMessageTemplate(), $this->getMessageParameters()),
            [static::PROPERTY_VALUE => $this->getValue()]
        );

        $operator->setParent($this);
        if ($this->isCaptureEnabled() === true) {
            $operator->enableCapture();
        }

        return $operator->toBlock();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    protected function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    protected function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    /**
     * @return mixed
     */
    public function getMessageParameters(): array
    {
        return $this->messageParams;
    }

    /**
     * @param ContextInterface $context
     * @return mixed
     */
    protected static function readValue(ContextInterface $context)
    {
        return $context->getProperties()->getProperty(static::PROPERTY_VALUE);
    }
}
