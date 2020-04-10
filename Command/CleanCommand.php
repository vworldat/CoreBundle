<?php

namespace C33s\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CleanCommand extends ContainerAwareCommand
{
    use CommandSetsTrait;

    protected $commandSets = [
        ['description' => 'cache clear prod', 'command' => 'php app/console cache:clear --env=prod'],
        ['description' => 'cache clear dev', 'command' => 'php app/console cache:clear --env=dev'],
        ['description' => 'assets install', 'command' => 'php app/console assets:install'],
        ['description' => 'assetic dump', 'command' => 'php app/console assetic:dump --env=prod'],
    ];

    protected function configure()
    {
        $this
            ->setName('c33s:clean')
            ->setDescription('c33s clean calls multiple symfony commands to get the project setup')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>c33s:clean</info>');

        $fs = new Filesystem();
        $output->writeln('deleting <info>web/media/generated</info> directory');
        $fs->remove($this->getContainer()->getParameter('kernel.root_dir').'/../web/media/generated');
        $output->writeln('deleting <info>web/bundles</info> directory');
        $fs->remove($this->getContainer()->getParameter('kernel.root_dir').'/../web/bundles');

        $this->executeCommandSets($this->commandSets, $output);
    }
}
