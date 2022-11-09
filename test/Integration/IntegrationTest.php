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

namespace PackageFactory\ComponentEngine\Test\Integration;

use PackageFactory\ComponentEngine\Parser\Ast\ModuleNode;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Transpiler\Php\Module\ModuleTranspiler;
use PHPUnit\Framework\TestCase;

final class IntegrationTest extends TestCase
{
    public function tokenizationExamples(): array
    {
        return [
            'Comment' => ["Comment"],
            'Component' => ["Component"],
            'ComponentWithKeywords' => ["ComponentWithKeywords"],
            'ComponentWithNesting' => ["ComponentWithNesting"],
            'Enum' => ["Enum"],
            'Expression' => ["Expression"],
            'ImportExport' => ["ImportExport"],
            'Match' => ["Match"],
            'Numbers' => ["Numbers"],
            'Struct' => ["Struct"],
            'TemplateLiteral' => ["TemplateLiteral"],
        ];
    }

    /**
     * @dataProvider tokenizationExamples
     * @test
     * @small
     * @param string $input
     * @return void
     */
    public function testTokenizer(string $example): void
    {
        $source = Source::fromFile(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $tokenizer = Tokenizer::fromSource($source);
        $expected = json_decode(
            file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.tokens.json')
        );

        $index = 0;
        foreach ($tokenizer as $token) {
            if (!isset($expected[$index])) {
                $tokenType = $token->type;
                $this->fail("Unfinished expectation at $index [$tokenType->name]($token->value)");
            }
            $this->assertEquals($expected[$index]->type, $token->type->name, "Type mismatch at index $index ($token->value)");
            $this->assertEquals($expected[$index]->value, $token->value, "Value mismatch at index $index");
            $index++;
        }
    }

    public function astExamples(): array
    {
        return [
            'Comment' => ["Comment"],
            'Component' => ["Component"],
            'ComponentWithKeywords' => ["ComponentWithKeywords"],
            'ComponentWithNesting' => ["ComponentWithNesting"],
            'Enum' => ["Enum"],
            'Expression' => ["Expression"],
            'ImportExport' => ["ImportExport"],
            'Match' => ["Match"],
            'Numbers' => ["Numbers"],
            'Struct' => ["Struct"],
            'TemplateLiteral' => ["TemplateLiteral"],
        ];
    }

    /**
     * @dataProvider astExamples
     * @test
     * @small
     * @param string $input
     * @return void
     */
    public function testParser(string $example): void
    {
        $source = Source::fromFile(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $tokenizer = Tokenizer::fromSource($source);
        $expected = json_decode(
            file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.ast.json'),
            true
        );

        $module = ModuleNode::fromTokens($tokenizer->getIterator());

        $this->assertEquals($expected, json_decode(json_encode($module), true));
    }

    public function transpilerExamples(): array
    {
        return [
            'Component' => ["Component"],
            'ComponentWithKeywords' => ["ComponentWithKeywords"],
            'ComponentWithNesting' => ["ComponentWithNesting"],
            'Enum' => ["Enum"],
            'Expression' => ["Expression"],
        ];
    }

    /**
     * @dataProvider transpilerExamples
     * @test
     * @small
     * @param string $input
     * @return void
     */
    public function testTranspiler(string $example): void
    {
        $source = Source::fromFile(__DIR__ . '/Examples/' . $example . '/' . $example . '.afx');
        $tokenizer = Tokenizer::fromSource($source);
        $module = ModuleNode::fromTokens($tokenizer->getIterator());

        $expected = file_get_contents(__DIR__ . '/Examples/' . $example . '/' . $example . '.php');

        $transpiler = new ModuleTranspiler();

        $this->assertEquals($expected, $transpiler->transpile($module));
    }
}
