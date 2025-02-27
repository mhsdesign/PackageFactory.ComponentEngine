<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\GlobalScope;

use PackageFactory\ComponentEngine\Parser\Ast\TypeReferenceNode;
use PackageFactory\ComponentEngine\TypeSystem\Scope\GlobalScope\GlobalScope;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;
use PHPUnit\Framework\TestCase;

final class GlobalScopeTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function isSingleton(): void
    {
        $globalScope1 = GlobalScope::get();
        $globalScope2 = GlobalScope::get();

        $this->assertSame($globalScope1, $globalScope2);
    }

    public function primitiveTypeExamples(): array
    {
        return [
            'string' => ['string', StringType::get()]
        ];
    }

    /**
     * @dataProvider primitiveTypeExamples
     * @test
     * @param string $typeReferenceAsString
     * @param TypeInterface $expectedType
     * @return void
     */
    public function resolvesPrimitiveTypes(string $typeReferenceAsString, TypeInterface $expectedType): void
    {
        $globalScope = GlobalScope::get();
        $typeReferenceNode = TypeReferenceNode::fromString($typeReferenceAsString);

        $actualType = $globalScope->resolveTypeReference($typeReferenceNode);

        $this->assertTrue(
            $expectedType->is($actualType),
            sprintf('Expected %s, got %s', $expectedType::class, $actualType::class)
        );
    }
}