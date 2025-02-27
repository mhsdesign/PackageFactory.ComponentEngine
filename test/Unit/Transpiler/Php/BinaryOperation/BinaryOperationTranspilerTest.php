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

namespace PackageFactory\ComponentEngine\Test\Unit\Transpiler\Php\BinaryOperation;

use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Test\Unit\TypeSystem\Scope\Fixtures\DummyScope;
use PackageFactory\ComponentEngine\Transpiler\Php\BinaryOperation\BinaryOperationTranspiler;
use PHPUnit\Framework\TestCase;

final class BinaryOperationTranspilerTest extends TestCase
{
    public function binaryOperationExamples(): array
    {
        return [
            'true && false' => ['true && false', '(true && false)'],
            'a && false' => ['a && false', '($this->a && false)'],
            'true && b' => ['true && b', '(true && $this->b)'],
            'a && b' => ['a && b', '($this->a && $this->b)'],
            'true || false' => ['true || false', '(true || false)'],
            'a || false' => ['a || false', '($this->a || false)'],
            'true || b' => ['true || b', '(true || $this->b)'],
            'a || b' => ['a || b', '($this->a || $this->b)'],
            'true && "foo"' => ['true && "foo"', '(true && \'foo\')'],
            'true && "foo"' => ['true && "foo"', '(true && \'foo\')'],
            'true || "foo"' => ['true || "foo"', '(true || \'foo\')'],
            'true && 42' => ['true && 42', '(true && 42)'],
            'true || 42' => ['true || 42', '(true || 42)'],

            '1 + 2' => ['1 + 2', '(1 + 2)'],
            'a + 2' => ['a + 2', '($this->a + 2)'],
            '1 + b' => ['1 + b', '(1 + $this->b)'],
            'a + b' => ['a + b', '($this->a + $this->b)'],
            '2 - 1' => ['2 - 1', '(2 - 1)'],
            'a - 1' => ['a - 1', '($this->a - 1)'],
            '2 - b' => ['2 - b', '(2 - $this->b)'],
            'a - b' => ['a - b', '($this->a - $this->b)'],
            '2 * 4' => ['2 * 4', '(2 * 4)'],
            'a * 4' => ['a * 4', '($this->a * 4)'],
            '2 * b' => ['2 * b', '(2 * $this->b)'],
            'a * b' => ['a * b', '($this->a * $this->b)'],
            '2 / 4' => ['2 / 4', '(2 / 4)'],
            'a / 4' => ['a / 4', '($this->a / 4)'],
            '2 / b' => ['2 / b', '(2 / $this->b)'],
            'a / b' => ['a / b', '($this->a / $this->b)'],
            '2 % 4' => ['2 % 4', '(2 % 4)'],
            'a % 4' => ['a % 4', '($this->a % 4)'],
            '2 % b' => ['2 % b', '(2 % $this->b)'],
            'a % b' => ['a % b', '($this->a % $this->b)'],

            '42 * a / 23 + b - 17 * c' => [
                '42 * a / 23 + b - 17 * c',
                '((((42 * $this->a) / 23) + $this->b) - (17 * $this->c))'
            ],

            '4 === 2' => ['4 === 2', '(4 === 2)'],
            'a === 2' => ['a === 2', '($this->a === 2)'],
            '4 === b' => ['4 === b', '(4 === $this->b)'],
            'a === b' => ['a === b', '($this->a === $this->b)'],
            '4 !== 2' => ['4 !== 2', '(4 !== 2)'],
            'a !== 2' => ['a !== 2', '($this->a !== 2)'],
            '4 !== b' => ['4 !== b', '(4 !== $this->b)'],
            'a !== b' => ['a !== b', '($this->a !== $this->b)'],
            '4 > 2' => ['4 > 2', '(4 > 2)'],
            'a > 2' => ['a > 2', '($this->a > 2)'],
            '4 > b' => ['4 > b', '(4 > $this->b)'],
            'a > b' => ['a > b', '($this->a > $this->b)'],
            '4 >= 2' => ['4 >= 2', '(4 >= 2)'],
            'a >= 2' => ['a >= 2', '($this->a >= 2)'],
            '4 >= b' => ['4 >= b', '(4 >= $this->b)'],
            'a >= b' => ['a >= b', '($this->a >= $this->b)'],
            '4 < 2' => ['4 < 2', '(4 < 2)'],
            'a < 2' => ['a < 2', '($this->a < 2)'],
            '4 < b' => ['4 < b', '(4 < $this->b)'],
            'a < b' => ['a < b', '($this->a < $this->b)'],
            '4 <= 2' => ['4 <= 2', '(4 <= 2)'],
            'a <= 2' => ['a <= 2', '($this->a <= 2)'],
            '4 <= b' => ['4 <= b', '(4 <= $this->b)'],
            'a <= b' => ['a <= b', '($this->a <= $this->b)'],
        ];
    }

    /**
     * @dataProvider binaryOperationExamples
     * @test
     * @param string $binaryOperationAsString
     * @param string $expectedTranspilationResult
     * @return void
     */
    public function transpilesBinaryOperationNodes(string $binaryOperationAsString, string $expectedTranspilationResult): void
    {
        $binaryOperationTranspiler = new BinaryOperationTranspiler(
            scope: new DummyScope()
        );
        $binaryOperationNode = ExpressionNode::fromString($binaryOperationAsString)->root;

        $actualTranspilationResult = $binaryOperationTranspiler->transpile(
            $binaryOperationNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}