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

namespace PackageFactory\ComponentEngine\Transpiler\Php\Attribute;

use PackageFactory\ComponentEngine\Parser\Ast\AttributeNode;
use PackageFactory\ComponentEngine\Parser\Ast\ExpressionNode;
use PackageFactory\ComponentEngine\Parser\Ast\StringLiteralNode;
use PackageFactory\ComponentEngine\Transpiler\Php\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\StringLiteral\StringLiteralTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;

final class AttributeTranspiler
{
    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    public function transpile(AttributeNode $attributeNode): string
    {
        return sprintf(
            '%s="%s"',
            $attributeNode->name,
            match ($attributeNode->value::class) {
                ExpressionNode::class => sprintf(
                    '\' . %s . \'',
                    (new ExpressionTranspiler(
                        scope: $this->scope
                    ))->transpile($attributeNode->value)
                ),
                StringLiteralNode::class => (new StringLiteralTranspiler())->transpile($attributeNode->value)
            }
        );
    }
}
