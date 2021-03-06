<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WorkflowBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;
use Symfony\Component\Workflow\Marking;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class WorkflowDumpCommand extends ContainerAwareCommand
{
    public function isEnabled()
    {
        return $this->getContainer()->has('workflow.registry');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('workflow:dump')
            ->setDefinition(array(
                new InputArgument('name', InputArgument::REQUIRED, 'A workflow name'),
                new InputArgument('marking', InputArgument::IS_ARRAY, 'A marking (a list of places)'),
            ))
            ->setDescription('Dump a workflow')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command dumps the graphical representation of a
workflow in DOT format

    %command.full_name% <workflow name> | dot -Tpng > workflow.png

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workflow = $this->getContainer()->get('workflow.'.$input->getArgument('name'));
        $definition = $this->getProperty($workflow, 'definition');

        $dumper = new GraphvizDumper();

        $marking = new Marking();
        foreach ($input->getArgument('marking') as $place) {
            $marking->mark($place);
        }

        $output->writeln($dumper->dump($definition, $marking));
    }

    private function getProperty($object, $property)
    {
        $reflectionProperty = new \ReflectionProperty(get_class($object), $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
