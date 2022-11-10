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

namespace PackageFactory\ComponentEngine\Parser\Ast;

use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class TemplateLiteralNode implements \JsonSerializable
{
    /**
     * @var array<int,StringLiteralNode|ExpressionNode>
     */
    public readonly array $segments;

    private function __construct(
        StringLiteralNode|ExpressionNode ...$segments
    ) {
        $this->segments = $segments;
    }

    public static function fromString(string $stringLiteralAsString): self
    {
        return self::fromTokens(
            Tokenizer::fromSource(
                Source::fromString($stringLiteralAsString)
            )->getIterator()
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::TEMPLATE_LITERAL_START);
        Scanner::skipOne($tokens);

        /** @var array<int,StringLiteralNode|ExpressionNode> $segments */
        $segments = [];

        while (true) {
            switch (Scanner::type($tokens)) {
                case TokenType::STRING_QUOTED:
                    $segments[] = StringLiteralNode::fromTokens($tokens);
                    break;
                case TokenType::DOLLAR:
                    Scanner::skipOne($tokens);
                    Scanner::assertType($tokens, TokenType::BRACKET_CURLY_OPEN);
                    Scanner::skipOne($tokens);

                    $segments[] = ExpressionNode::fromTokens($tokens);

                    Scanner::assertType($tokens, TokenType::BRACKET_CURLY_CLOSE);
                    Scanner::skipOne($tokens);
                    break;
                case TokenType::TEMPLATE_LITERAL_END:
                    Scanner::skipOne($tokens);
                    break 2;
                default:
                    Scanner::assertType($tokens, TokenType::STRING_QUOTED, TokenType::DOLLAR, TokenType::TEMPLATE_LITERAL_END);
                    break;
            }
        }

        return new self(...$segments);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'TemplateLiteralNode',
            'payload' => $this->segments
        ];
    }
}
