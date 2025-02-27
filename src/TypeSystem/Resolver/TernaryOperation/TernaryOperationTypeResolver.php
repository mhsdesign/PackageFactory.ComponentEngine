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

namespace PackageFactory\ComponentEngine\TypeSystem\Resolver\TernaryOperation;

use PackageFactory\ComponentEngine\Parser\Ast\BooleanLiteralNode;
use PackageFactory\ComponentEngine\Parser\Ast\TernaryOperationNode;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\UnionType\UnionType;
use PackageFactory\ComponentEngine\TypeSystem\TypeInterface;

final class TernaryOperationTypeResolver
{
    public function __construct(
        private readonly ScopeInterface $scope
    ) {
    }

    public function resolveTypeOf(TernaryOperationNode $ternaryOperationNode): TypeInterface
    {
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $this->scope
        );
        $conditionNode = $ternaryOperationNode->condition->root;

        if ($conditionNode instanceof BooleanLiteralNode) {
            return $conditionNode->value
                ? $expressionTypeResolver->resolveTypeOf($ternaryOperationNode->true)
                : $expressionTypeResolver->resolveTypeOf($ternaryOperationNode->false);
        }

        return UnionType::of(
            $expressionTypeResolver->resolveTypeOf($ternaryOperationNode->true),
            $expressionTypeResolver->resolveTypeOf($ternaryOperationNode->false)
        );
    }
}
