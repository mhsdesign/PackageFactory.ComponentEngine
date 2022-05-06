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

namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Ast\Declaration\Parameter\Parameters;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ArrowFunction implements \JsonSerializable
{
    private function __construct(
        public readonly Parameters $parameters,
        public readonly Expression $body
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        Scanner::skipSpaceAndComments($tokens);
        Scanner::assertType($tokens, TokenType::BRACKET_ROUND_OPEN);
        Scanner::skipOne($tokens);

        $parameters = Parameters::fromTokens($tokens);

        Scanner::skipSpace($tokens);
        Scanner::assertType($tokens, TokenType::BRACKET_ROUND_CLOSE);
        Scanner::skipOne($tokens);
        Scanner::skipSpace($tokens);
        Scanner::assertType($tokens, TokenType::ARROW);
        Scanner::skipOne($tokens);

        $body = Expression::fromTokens($tokens);

        return new self(
            parameters: $parameters,
            body: $body
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'type' => 'ArrowFunction',
            'payload' => [
                'parameters' => $this->parameters,
                'body' => $this->body
            ]
        ];
    }
}
