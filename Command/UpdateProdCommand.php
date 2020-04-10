<?php

namespace C33s\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProdCommand extends ContainerAwareCommand
{
    use CommandSetsTrait;

    protected $commandSets = [
        ['description' => 'git reset', 'command' => 'git reset --hard'],
        ['description' => 'git pull', 'command' => 'git pull'],
        ['description' => 'composer install', 'command' => 'composer.phar install'],
        ['description' => 'cache clear prod', 'command' => 'php app/console c33s:clean'],
        ['description' => 'chown', 'command' => 'www-data:www-data -R *'],
    ];

    protected function configure()
    {
        $this
            ->setName('c33s:updateprod')
            ->setDescription('updates production installation by calling git reset, git pull, composer install,...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>c33s:updateprod</info>');

        $this->executeCommandSets($this->commandSets, $output);
    }
}
