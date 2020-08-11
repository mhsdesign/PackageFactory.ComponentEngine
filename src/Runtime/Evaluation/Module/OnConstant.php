<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Afx\Tag;
use PackageFactory\ComponentEngine\Parser\Ast\Module\Constant;
use PackageFactory\ComponentEngine\Runtime\Context\Value\CallableValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Afx;
use PackageFactory\ComponentEngine\Runtime\Evaluation\Expression;

final class OnConstant
{
    /**
     * @param Runtime $runtime
     * @param Constant $constant
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Constant $constant) 
    {
        $value = $constant->getValue();
    
        if ($value instanceof Tag) {
            return CallableValue::fromClosure(function ($props) use ($runtime, $value) {
                return Afx\OnTag::evaluate(
                    $runtime->withContext(DictionaryValue::fromArray(['props' => $props])), 
                    $value
                );
            });
        } else {
            return Expression\OnTerm::evaluate($runtime, $value);
        }
    }
}
