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
use PHPUnit\Framework\TestCase;

final class ParserIntegrationTest extends TestCase
{
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
}
