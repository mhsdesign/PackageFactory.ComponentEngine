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

namespace PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Type\UnionType;

use PackageFactory\ComponentEngine\TypeSystem\Type\NumberType\NumberType;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PHPUnit\Framework\TestCase;

final class UnionTypeTest extends TestCase
{
    /**
     * @test
     */
    public function staticOfResolvesToGivenTypeIfOnlyOneTypeIsGiven(): void
    {
        $unionType = UnionType::of(StringType::get());
        $this->assertTrue($unionType->is(StringType::get()));
        $this->assertTrue(StringType::get()->is($unionType));

        $unionType = UnionType::of(NumberType::get());
        $this->assertTrue($unionType->is(NumberType::get()));
        $this->assertTrue(NumberType::get()->is($unionType));

        $unionType = UnionType::of(UnionType::of(StringType::get()));
        $this->assertTrue($unionType->is(StringType::get()));
        $this->assertTrue(StringType::get()->is($unionType));

        $unionType = UnionType::of(UnionType::of(NumberType::get()));
        $this->assertTrue($unionType->is(NumberType::get()));
        $this->assertTrue(NumberType::get()->is($unionType));
    }

    /**
     * @test
     */
    public function staticOfResolvesToGivenTypeIfAllGivenTypesAreIdentical(): void
    {
        $unionType = UnionType::of(StringType::get(), StringType::get());
        $this->assertTrue($unionType->is(StringType::get()));
        $this->assertTrue(StringType::get()->is($unionType));

        $unionType = UnionType::of(StringType::get(), StringType::get(), UnionType::of(StringType::get(), StringType::get()));
        $this->assertTrue($unionType->is(StringType::get()));
        $this->assertTrue(StringType::get()->is($unionType));
    }

    /**
     * @test
     */
    public function isReturnsTrueIfGivenTypeIsCongruentUnionType(): void
    {
        $unionType = UnionType::of(StringType::get(), NumberType::get());
        $otherUnionType = UnionType::of(NumberType::get(), StringType::get());

        $this->assertTrue($unionType->is($otherUnionType));
    }

    /**
     * @test
     */
    public function isReturnsTrueIfGivenTypeIsCongruentUnionTypeWithRedundantMembers(): void
    {
        $unionType = UnionType::of(StringType::get(), NumberType::get());
        $otherUnionType = UnionType::of(NumberType::get(), StringType::get(), NumberType::get(), StringType::get());

        $this->assertTrue($unionType->is($otherUnionType));
    }

    /**
     * @test
     */
    public function isReturnsFalseIfGivenTypeIsNotCongruent(): void
    {
        $unionType = UnionType::of(StringType::get(), NumberType::get());

        $this->assertFalse($unionType->is(NumberType::get()));
        $this->assertFalse($unionType->is(StringType::get()));
    }
}