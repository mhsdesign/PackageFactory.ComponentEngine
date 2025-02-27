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

namespace PackageFactory\ComponentEngine\Test\Unit\Transpiler\Php\EnumDeclaration;

use PackageFactory\ComponentEngine\Parser\Ast\EnumDeclarationNode;
use PackageFactory\ComponentEngine\Transpiler\Php\EnumDeclaration\EnumDeclarationTranspiler;
use PHPUnit\Framework\TestCase;

final class EnumDeclarationTranspilerTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function transpilesEnumDeclarationNodes(): void
    {
        $enumDeclarationAsString = <<<EOT
        enum ButtonType {
            BUTTON
            SUBMIT
        }
        EOT;
        $enumDeclarationTranspiler = new EnumDeclarationTranspiler();
        $enumDeclarationNode = EnumDeclarationNode::fromString($enumDeclarationAsString);

        $expectedTranspilationResult = <<<EOT
        <?php

        declare(strict_types=1);

        namespace Vendor\\Project\\Component;

        enum ButtonType : string
        {
            case BUTTON = 'BUTTON';
            case SUBMIT = 'SUBMIT';
        }

        EOT;

        $actualTranspilationResult = $enumDeclarationTranspiler->transpile(
            $enumDeclarationNode
        );

        $this->assertEquals(
            $expectedTranspilationResult,
            $actualTranspilationResult
        );
    }
}