<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Parser\Ast\Expression;

use PackageFactory\ComponentEngine\Parser\Lexer\TokenStream;
use PackageFactory\ComponentEngine\Parser\Lexer\TokenType;
use PackageFactory\ComponentEngine\Parser\Util;

final class Call implements \JsonSerializable
{
    /**
     * @var array|Operand[]
     */
    private $arguments;

    /**
     * @param array|Operand[] $arguments
     */
    private function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param TokenStream $stream
     * @return self
     */
    public static function createFromTokenStream(TokenStream $stream): self
    {
        Util::skipWhiteSpaceAndComments($stream);
        Util::expect($stream, TokenType::BRACKETS_ROUND_OPEN());

        $arguments = [];
        while ($stream->valid()) {
            Util::skipWhiteSpaceAndComments($stream);

            switch ($stream->current()->getType()) {
                case TokenType::BRACKETS_ROUND_CLOSE():
                    $stream->next();
                    return new self($arguments);

                default:
                    $argument = Expression::createFromTokenStream($stream);
                    if ($argument === null) {
                        throw new \Exception('@TODO: Unexpected empty argument');
                    }
                    $arguments[] = $argument;
                    break;
            }

            Util::skipWhiteSpaceAndComments($stream);

            if ($stream->current()->getType() === TokenType::COMMA()) {
                $stream->next();
            } else {
                Util::skipWhiteSpaceAndComments($stream);
                Util::expect($stream, TokenType::BRACKETS_ROUND_CLOSE());
                break;
            }
        }

        return new self($arguments);
    }

    /**
     * @return array|Operand[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'Call',
            'arguments' => $this->arguments
        ];
    }
}