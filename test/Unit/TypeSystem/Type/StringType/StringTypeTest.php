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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\StringType;

use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PHPUnit\Framework\TestCase;

final class StringTypeTest extends TestCase
{
    /**
     * @test
     */
    public function stringTypeIsSingleton(): void
    {
        $this->assertSame(StringType::get(), StringType::get());
    }

    /**
     * @test
     */
    public function isReturnsTrueIfGivenTypeIsStringType(): void
    {
        $this->assertTrue(StringType::get()->is(StringType::get()));
    }

    /**
     * @test
     */
    public function isReturnsFalseIfGivenTypeIsNotStringType(): void
    {
        $this->assertFalse(StringType::get()->is(NumberType::get()));
    }
}