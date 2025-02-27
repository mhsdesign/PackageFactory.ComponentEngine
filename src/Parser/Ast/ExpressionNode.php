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

use PackageFactory\ComponentEngine\Definition\Precedence;
use PackageFactory\ComponentEngine\Parser\Source\Source;
use PackageFactory\ComponentEngine\Parser\Tokenizer\LookAhead;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Scanner;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Token;
use PackageFactory\ComponentEngine\Parser\Tokenizer\Tokenizer;
use PackageFactory\ComponentEngine\Parser\Tokenizer\TokenType;

final class ExpressionNode implements \JsonSerializable
{
    private function __construct(
        public readonly IdentifierNode | NumberLiteralNode | BinaryOperationNode | AccessNode | TernaryOperationNode | TagNode | StringLiteralNode | MatchNode | TemplateLiteralNode | BooleanLiteralNode $root
    ) {
    }

    public static function fromString(string $expressionAsString): self
    {
        return self::fromTokens(
            Tokenizer::fromSource(
                Source::fromString($expressionAsString)
            )->getIterator()
        );
    }

    /**
     * @param \Iterator<mixed,Token> $tokens
     * @param Precedence $precedence
     * @return self
     */
    public static function fromTokens(\Iterator $tokens, Precedence $precedence = Precedence::SEQUENCE): self
    {
        Scanner::skipSpaceAndComments($tokens);

        switch (Scanner::type($tokens)) {
            case TokenType::BRACKET_ROUND_OPEN:
                $lookAhead = LookAhead::fromTokens($tokens);
                $lookAhead->shift();

                while (true) {
                    switch ($lookAhead->type()) {
                        case TokenType::STRING:
                        case TokenType::COLON:
                        case TokenType::COMMA:
                        case TokenType::BRACKET_SQUARE_OPEN:
                        case TokenType::BRACKET_SQUARE_CLOSE:
                        case TokenType::BRACKET_ROUND_CLOSE:
                        case TokenType::SPACE:
                        case TokenType::END_OF_LINE:
                            $lookAhead->shift();
                            break;
                        default:
                            $tokens = $lookAhead->getIterator();
                            Scanner::skipOne($tokens);
                            $root = self::fromTokens($tokens)->root;
                            Scanner::assertType($tokens, TokenType::BRACKET_ROUND_CLOSE);
                            Scanner::skipOne($tokens);
                            break 2;
                    }
                }
                break;
            case TokenType::NUMBER_BINARY:
            case TokenType::NUMBER_OCTAL:
            case TokenType::NUMBER_DECIMAL:
            case TokenType::NUMBER_HEXADECIMAL:
            case TokenType::PERIOD:
                $root = NumberLiteralNode::fromTokens($tokens);
                break;
            case TokenType::KEYWORD_TRUE:
            case TokenType::KEYWORD_FALSE:
                $root = BooleanLiteralNode::fromTokens($tokens);
                break;
            case TokenType::KEYWORD_MATCH:
                $root = MatchNode::fromTokens($tokens);
                break;
            case TokenType::TAG_START_OPENING:
                $root = TagNode::fromTokens($tokens);
                break;
            case TokenType::STRING_QUOTED:
                $root = StringLiteralNode::fromTokens($tokens);
                break;
            case TokenType::TEMPLATE_LITERAL_START:
                $root = TemplateLiteralNode::fromTokens($tokens);
                break;
            default:
                $root = IdentifierNode::fromTokens($tokens);
                break;
        }

        Scanner::skipSpaceAndComments($tokens);
        if (Scanner::isEnd($tokens) || $precedence->mustStopAt(Scanner::type($tokens))) {
            return new self(
                root: $root
            );
        }

        while ($tokens->valid()) {
            Scanner::skipSpaceAndComments($tokens);

            switch (Scanner::type($tokens)) {
                case TokenType::OPERATOR_BOOLEAN_AND:
                case TokenType::OPERATOR_BOOLEAN_OR:
                case TokenType::OPERATOR_ARITHMETIC_PLUS:
                case TokenType::OPERATOR_ARITHMETIC_MINUS:
                case TokenType::OPERATOR_ARITHMETIC_MULTIPLY_BY:
                case TokenType::OPERATOR_ARITHMETIC_DIVIDE_BY:
                case TokenType::OPERATOR_ARITHMETIC_MODULO:
                case TokenType::COMPARATOR_EQUAL:
                case TokenType::COMPARATOR_NOT_EQUAL:
                case TokenType::COMPARATOR_GREATER_THAN:
                case TokenType::COMPARATOR_GREATER_THAN_OR_EQUAL:
                case TokenType::COMPARATOR_LESS_THAN:
                case TokenType::COMPARATOR_LESS_THAN_OR_EQUAL:
                    $root = BinaryOperationNode::fromTokens(new self(root: $root), $tokens);
                    break;
                case TokenType::PERIOD:
                    $root = AccessNode::fromTokens(new self(root: $root), $tokens);
                    break;
                case TokenType::QUESTIONMARK:
                    $root = TernaryOperationNode::fromTokens(new self(root: $root), $tokens);
                    break;
                case TokenType::ARROW_SINGLE:
                default:
                    break 2;
            }
        }

        return new self(
            root: $root
        );
    }

    public function jsonSerialize(): mixed
    {
        return $this->root;
    }
}
