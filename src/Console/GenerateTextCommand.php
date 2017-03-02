<?php
namespace Tkjn\RandomText\Console;

use Assert;
use Tkjn\Random\Integer\XorshiftStar;
use Tkjn\RandomText\Text\Generator;
use Tkjn\RandomText\Text\Grammar;
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
        $grammarData = $jsonDecode->decode(file_get_contents($grammarFile), JsonEncoder::FORMAT);

        $grammar = new Grammar($grammarData);
        $generator = new Generator($grammar, $random);

        for ($i = 0; $i < $quantity; $i++) {
            $word = $generator->generateText($type);
            $output->writeln($word);
        }
    }
}
