<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Parser\Ast;

final class Context
{
    /**
     * @var array<string, mixed>
     */
    protected $properties;

    /**
     * @param array<string, mixed> $properties
     */
    private function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return self
     */
    public static function createEmpty(): self
    {
        return new self([]);
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @param string $propertyName
     * @return mixed
     */
    public function getProperty(string $propertyName)
    {
        if (!isset($this->properties[$propertyName])) {
            throw new \Exception('@TODO: unknown context property');
        }

        return $this->properties[$propertyName];
    }

    /**
     * @param string $propertyName
     * @return boolean
     */
    public function hasProperty(string $propertyName)
    {
        return isset($this->properties[$propertyName]);
    }

    /**
     * @param Ast\Chain $chain
     * @return null|mixed
     */
    public function evaluateChain(Ast\Chain $chain)
    {
        $value = $this->properties;

        foreach ($chain->getElements() as $key) {
            if (is_array($value)) {
                if (isset($value[$key])) {
                    $value = $value[$key];
                }
                else {
                    throw new \RuntimeException('@TODO: Invalid array key access ' . $key);
                }
            }
            else if (is_object($value)) {
                $getter = 'get' . ucfirst($key);
                if (method_exists($value, $getter)) {
                    if (is_callable([$value, $getter])) {
                        $value = $value->{ $getter }();
                    }
                    else {
                        throw new \RuntimeException('@TODO: Method is not callable');
                    }
                }
                else {
                    throw new \RuntimeException('@TODO: Method does not exist');
                }
            }
            else {
                throw new \RuntimeException('@TODO: Invalid property access');
            }
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $newProperties
     * @return self
     */
    public function withMergedProperties(array $newProperties): self
    {
        $nextProperties = $this->properties;

        foreach ($newProperties as $key => $value) {
            $nextProperties[$key] = $value;
        }

        return new self($nextProperties);
    }
}