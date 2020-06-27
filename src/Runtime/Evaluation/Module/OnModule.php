<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Evaluation\Module;

use PackageFactory\ComponentEngine\Parser\Ast\Module\Module;
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
    
        foreach ($module->getImports() as $import) {
            $context = $context->withMergedProperties([
                (string) $import->getDomesticName() => 
                    OnImport::evaluate($runtime, $module, $import)
            ]);
        }
    
        foreach ($module->getConstants() as $constant) {
            $context = $context->withMergedProperties([
                (string) $constant->getName() =>
                    OnConstant::evaluate($runtime->withContext($context), $constant)
            ]);
        }
    
        return OnExport::evaluate($runtime->withContext($context), $export);
    }
}
