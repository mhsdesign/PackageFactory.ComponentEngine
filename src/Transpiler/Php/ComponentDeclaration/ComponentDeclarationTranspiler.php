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

namespace PackageFactory\ComponentEngine\Transpiler\Php\ComponentDeclaration;

use PackageFactory\ComponentEngine\Parser\Ast\ComponentDeclarationNode;
use PackageFactory\ComponentEngine\Parser\Ast\PropertyDeclarationNodes;
use PackageFactory\ComponentEngine\Transpiler\Php\Expression\ExpressionTranspiler;
use PackageFactory\ComponentEngine\Transpiler\Php\TypeReference\TypeReferenceTranspiler;
use PackageFactory\ComponentEngine\TypeSystem\Resolver\Expression\ExpressionTypeResolver;
use PackageFactory\ComponentEngine\TypeSystem\Scope\ComponentScope\ComponentScope;
use PackageFactory\ComponentEngine\TypeSystem\ScopeInterface;
use PackageFactory\ComponentEngine\TypeSystem\Type\StringType\StringType;

final class ComponentDeclarationTranspiler
{
    public function __construct(private readonly ScopeInterface $scope)
    {
    }

    public function transpile(ComponentDeclarationNode $componentDeclarationNode): string
    {
        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'declare(strict_types=1);';
        $lines[] = '';
        $lines[] = 'namespace Vendor\\Project\\Component;';
        $lines[] = '';
        $lines[] = 'use Vendor\\Project\\BaseClass;';
        $lines[] = '';
        $lines[] = 'final class ' . $componentDeclarationNode->componentName . ' extends BaseClass';
        $lines[] = '{';

        if (!$componentDeclarationNode->propertyDeclarations->isEmpty()) {
            $lines[] = '    public function __construct(';
            $lines[] = $this->writeConstructorPropertyDeclarations($componentDeclarationNode->propertyDeclarations);
            $lines[] = '    ) {';
            $lines[] = '    }';
            $lines[] = '';
        }

        $lines[] = '    public function render(): string';
        $lines[] = '    {';
        $lines[] = $this->writeReturnExpression($componentDeclarationNode);
        $lines[] = '    }';
        $lines[] = '}';
        $lines[] = '';

        return join("\n", $lines);
    }

    public function writeConstructorPropertyDeclarations(PropertyDeclarationNodes $propertyDeclarations): string
    {
        $typeReferenceTranspiler = new TypeReferenceTranspiler();
        $lines = [];

        foreach ($propertyDeclarations->items as $propertyDeclaration) {
            $lines[] = '        public readonly ' . $typeReferenceTranspiler->transpile($propertyDeclaration->type) . ' $' . $propertyDeclaration->name . ',';
        }

        if ($length = count($lines)) {
            $lines[$length - 1] = substr($lines[$length - 1], 0, -1);
        }

        return join("\n", $lines);
    }

    public function writeReturnExpression(ComponentDeclarationNode $componentDeclarationNode): string
    {
        $componentScope = new ComponentScope(
            componentDeclarationNode: $componentDeclarationNode,
            parentScope: $this->scope
        );
        $expressionTypeResolver = new ExpressionTypeResolver(
            scope: $componentScope
        );
        $expressionTranspiler = new ExpressionTranspiler(
            scope: $componentScope,
            shouldAddQuotesIfNecessary: true
        );

        $returnExpression = $componentDeclarationNode->returnExpression;
        $returnTypeIsString = StringType::get()->is(
            $expressionTypeResolver->resolveTypeOf($returnExpression)
        );
        $transpiledReturnExpression = $expressionTranspiler->transpile($returnExpression);

        if (!$returnTypeIsString) {
            $transpiledReturnExpression = '(string) ' . $transpiledReturnExpression;
        }

        return '        return ' . $transpiledReturnExpression . ';';
    }
}
