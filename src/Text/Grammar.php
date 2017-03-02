<?php
namespace Tkjn\RandomText\Text;

use Assert;

class Grammar
{
    private $grammar;

    public function __construct(array $grammar)
    {
        $this->grammar = $grammar;
    }

    public function getTypeList(): array
    {
        return array_keys($this->grammar);
    }

    public function getTypeData(string $type): array
    {
        Assert\that($type)->notBlank();

        if (!isset($this->grammar[$type])) {
            throw new \OutOfBoundsException(sprintf('Type \'%s\' not found in grammar', $type));
        }

        return $this->grammar[$type];
    }
}
