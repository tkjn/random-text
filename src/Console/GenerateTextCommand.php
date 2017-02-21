<?php
namespace Tkjn\RandomText\Console;

use Assert;
use Tkjn\Random\Integer\Random;
use Tkjn\Random\Integer\XorshiftStar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class GenerateTextCommand extends Command
{
    protected function configure()
    {
        $this->setName('text:generate')
             ->setDescription('Generate text')
             ->addArgument(
                 'grammar_file',
                 InputArgument::REQUIRED,
                 'The file defining the available grammar. Currently JSON'
             )
             ->addArgument('type', InputArgument::REQUIRED, 'The type of text to generate from the grammar file')
             ->addArgument(
                 'quantity',
                 InputArgument::OPTIONAL,
                 'The number of permutations to generate. Defaults to 1'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $grammarFile = $input->getArgument('grammar_file');
        $type = $input->getArgument('type');
        $quantity = $input->getArgument('quantity');

        Assert\that($grammarFile)->file();

        if (!$quantity) {
            $quantity = 1;
        } else {
            Assert\that($quantity)->integerish()->min(1);
        }

        $random = new XorshiftStar();

        $jsonDecode = new JsonDecode(true);
        $grammar = $jsonDecode->decode(file_get_contents($grammarFile), JsonEncoder::FORMAT);

        for ($i = 0; $i < $quantity; $i++) {
            $word = $this->generateText($type, $grammar, $random);
            $output->writeln($word);
        }
    }

    private function generateText(string $type, array $grammar, Random $random): string
    {
        $word = '';
        foreach ($grammar[$type] as $part) {
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

            $option = $this->pickRandomEntry($options, $random);
            if (null === $option) {
                continue;
            }

            if ('ref' === $option['type']) {
                $word .= $this->generateText($option['value'], $grammar, $random);
            } else {
                $word .= $option['value'];
            }
        }

        return $word;
    }

    private function pickRandomEntry(array $entries, Random $random)
    {
        if (empty($entries)) {
            return;
        }

        if (1 === count($entries)) {
            return array_pop($entries);
        }
        $entry = $random->rand(0, count($entries) - 1);
        return $entries[$entry];
    }
}
