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

use PackageFactory\ComponentEngine\Definition\AccessType;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class AccessChainSegmentNode implements \JsonSerializable
{
    private function __construct(
        public readonly AccessType $accessType,
        public readonly IdentifierNode $accessor
    ) {
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @return self
     */
    public static function fromTokens(\Iterator $tokens): self
    {
        Scanner::skipSpaceAndComments($tokens);

        switch (Scanner::type($tokens)) {
            case TokenType::PERIOD:
            case TokenType::OPTCHAIN:
                $accessType = AccessType::fromTokenType(Scanner::type($tokens));
                Scanner::skipOne($tokens);
                $accessor = IdentifierNode::fromTokens($tokens);
                return new self(
                    accessType: $accessType,
                    accessor: $accessor
                );
            default:
                Scanner::assertType($tokens, TokenType::PERIOD, TokenType::OPTCHAIN);
                break;
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'accessType' => $this->accessType->value,
            'accessor' => $this->accessor
        ];
    }
}