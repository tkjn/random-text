<?php
namespace Tkjn\RandomText\Text;

use Tkjn\Random\Integer\Random;

class Generator
{
    private $grammar;
    private $random;

    public function __construct(Grammar $grammar, Random $random)
    {
        $this->grammar = $grammar;
        $this->random  = $random;
    }

    public function generateText(string $type): string
    {
        $word = '';
        $typeGrammar = $this->grammar->getTypeData($type);
        foreach ($typeGrammar as $part) {
            $options = [];
            if (isset($part['vals'])) {
                foreach ($part['vals'] as $val) {
                    $options[] = [
                        'type' => 'val',
                        'value' => $val,
                    ];
                }
            }

            if (isset($part['refs'])) {
                foreach ($part['refs'] as $ref) {
                    $options[] = [
                        'type' => 'ref',
                        'value' => $ref,
                    ];
                }
            }

            $option = $this->pickRandomEntry($options);
            if (null === $option) {
                continue;
            }

            if ('ref' === $option['type']) {
                $word .= $this->generateText($option['value']);
            } else {
                $word .= $option['value'];
            }
        }

        return $word;
    }

    private function pickRandomEntry(array $entries): ?array
    {
        if (empty($entries)) {
            return null;
        }

        if (1 === count($entries)) {
            return array_pop($entries);
        }
        $entry = $this->random->rand(0, count($entries) - 1);
        return $entries[$entry];
    }
}
