<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
use PackageFactory\ComponentEngine\Runtime\Context\Value\DictionaryValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class OnModule
{
    /**
     * @param Runtime $runtime
     * @param Module $module
     * @param string $exportName
     * @return mixed
     */
    public static function evaluate(Runtime $runtime, Module $module, string $exportName = 'default') {
        $export = $module->getExport($exportName);
        $context = $runtime->getContext();

        $imports = [];
        foreach ($module->getImports() as $import) {
            $imports[(string) $import->getDomesticName()] = OnImport::evaluate($runtime, $module, $import);
        }
        
        $constants = [];
        foreach ($module->getConstants() as $constant) {
            $constants[(string) $constant->getName()] = OnConstant::evaluate($runtime->withContext($context), $constant)->getValue($runtime);
        }

        /** @var DictionaryValue $context */
        $context = $context
            ->merge(DictionaryValue::fromArray($imports))
            ->merge(DictionaryValue::fromArray($constants));

        return OnExport::evaluate($runtime->withContext($context), $export);
    }
}
