<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Lexer\Scope;

use PackageFactory\ComponentEngine\Parser\Lexer\Token;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Source\SourceIterator;

final class Module
{
    public const KEYWORD_IMPORT = 'import ';
    public const KEYWORD_FROM = 'from ';
    public const KEYWORD_AS = 'as ';
    public const KEYWORD_CONST = 'const ';
    public const KEYWORD_EXPORT = 'export ';
    public const KEYWORD_DEFAULT = 'default ';

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenize(SourceIterator $iterator): \Iterator
    {
        while ($iterator->valid()) {
            yield from Whitespace::tokenize($iterator);
            if (!$iterator->valid()) {
                return;
            }

            $value = $iterator->current()->getValue();

            if ($value === self::KEYWORD_IMPORT[0]) {
                $lookAhead = $iterator->lookAhead(7);

                if ($lookAhead->getValue() === self::KEYWORD_IMPORT) {
                    yield Token::createFromFragment(
                        TokenType::MODULE_KEYWORD_IMPORT(),
                        $iterator->lookAhead(6)
                    );
                    $iterator->skip(6);

                    yield from self::tokenizeImport($iterator);
                    continue;
                }
            } elseif ($value === self::KEYWORD_CONST[0]) {
                $lookAhead = $iterator->lookAhead(6);

                if ($lookAhead->getValue() === self::KEYWORD_CONST) {
                    yield Token::createFromFragment(
                        TokenType::MODULE_KEYWORD_CONST(),
                        $iterator->lookAhead(5)
                    );
                    $iterator->skip(5);
                    continue;
                }
            } elseif ($value === self::KEYWORD_EXPORT[0]) {
                $lookAhead = $iterator->lookAhead(7);

                if ($lookAhead->getValue() === self::KEYWORD_EXPORT) {
                    yield Token::createFromFragment(
                        TokenType::MODULE_KEYWORD_EXPORT(),
                        $iterator->lookAhead(6)
                    );
                    $iterator->skip(6);
                    continue;
                }
            } elseif ($value === self::KEYWORD_DEFAULT[0]) {
                $lookAhead = $iterator->lookAhead(8);

                if ($lookAhead->getValue() === self::KEYWORD_DEFAULT) {
                    yield Token::createFromFragment(
                        TokenType::MODULE_KEYWORD_DEFAULT(),
                        $iterator->lookAhead(7)
                    );
                    $iterator->skip(7);

                    yield from self::tokenizeAssignmentValue($iterator);
                    continue;
                }
            } 
            
            if ($value === '=') {
                yield Token::createFromFragment(
                    TokenType::MODULE_ASSIGNMENT(),
                    $iterator->current()
                );
                $iterator->next();

                yield from self::tokenizeAssignmentValue($iterator);
            } elseif ($value === '(') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_ROUND_OPEN(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === ')') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_ROUND_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '[') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_SQUARE_OPEN(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === ']') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_SQUARE_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '{') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_CURLY_OPEN(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '}') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_CURLY_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === ',') {
                yield Token::createFromFragment(
                    TokenType::COMMA(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '.') {
                $lookAhead = $iterator->lookAhead(3);
                if ($lookAhead->getValue() === '...') {
                    yield Token::createFromFragment(
                        TokenType::OPERATOR_SPREAD(),
                        $lookAhead
                    );
                    $iterator->skip(3);
                } else {
                    break;
                }
            } elseif (ctype_alpha($value)) {
                yield from Identifier::tokenize($iterator);
            } else {
                break;
            }
        }
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeImport(SourceIterator $iterator): \Iterator
    {
        while ($iterator->valid()) {
            yield from Whitespace::tokenize($iterator);

            $value = $iterator->current()->getValue();

            if ($value === self::KEYWORD_AS[0]) {
                $lookAhead = $iterator->lookAhead(3);

                if ($lookAhead->getValue() === self::KEYWORD_AS) {
                    yield Token::createFromFragment(
                        TokenType::MODULE_KEYWORD_AS(),
                        $iterator->lookAhead(2)
                    );
                    $iterator->skip(2);
                    continue;
                }
            } elseif ($value === self::KEYWORD_FROM[0]) {
                $lookAhead = $iterator->lookAhead(5);

                if ($lookAhead->getValue() === self::KEYWORD_FROM) {
                    yield Token::createFromFragment(
                        TokenType::MODULE_KEYWORD_FROM(),
                        $iterator->lookAhead(4)
                    );
                    $iterator->skip(4);
                    continue;
                }
            }

            if ($value === '*') {
                yield Token::createFromFragment(
                    TokenType::MODULE_WILDCARD(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '"' || $value === '\'') {
                yield from StringLiteral::tokenize($iterator);
            } elseif ($value === '{') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_CURLY_OPEN(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === '}') {
                yield Token::createFromFragment(
                    TokenType::BRACKETS_CURLY_CLOSE(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif ($value === ',') {
                yield Token::createFromFragment(
                    TokenType::COMMA(),
                    $iterator->current()
                );
                $iterator->next();
            } elseif (ctype_alpha($value)) {
                yield from Identifier::tokenize($iterator);
            } else {
                break;
            }
        }
    }

    /**
     * @param SourceIterator $iterator
     * @return \Iterator<Token>
     */
    public static function tokenizeAssignmentValue(SourceIterator $iterator): \Iterator
    {
        while ($iterator->valid()) {
            yield from Whitespace::tokenize($iterator);

            if ($iterator->valid()) {
                if ($iterator->current()->getValue() === '<') {
                    yield from Afx::tokenize($iterator);
                } else {
                    yield from Expression::tokenize($iterator, [
                        'const ',
                        'const' . PHP_EOL,
                        'import ',
                        'import' . PHP_EOL,
                        'export ',
                        'export' . PHP_EOL,
                    ]);
                }

                return;
            }
        }
    }
}