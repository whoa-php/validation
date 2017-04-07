<?php namespace Limoncello\Auth\Contracts\Authorization\PolicyAdministration;

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
 * @package Limoncello\Auth
 */
interface RuleInterface
{
    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return TargetInterface|null
     */
    public function getTarget();

    /**
     * Optional extra check if rule matches incoming request.
     *
     * @return MethodInterface|null
     */
    public function getCondition();

    /**
     * @return MethodInterface|null
     */
    public function effect();

    /**
     * @return ObligationInterface[]
     */
    public function getObligations(): array;

    /**
     * @return AdviceInterface[]
     */
    public function getAdvice(): array;
}
