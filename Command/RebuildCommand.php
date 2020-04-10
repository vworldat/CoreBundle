<?php

namespace C33s\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RebuildCommand extends ContainerAwareCommand
{
    use CommandSetsTrait;

    protected $users = [];

    protected $commandSets = [
        ['description' => 'cache:clear', 'command' => 'php app/console cache:clear'],
        ['description' => 'cache:clear', 'command' => 'php app/console cache:clear --env=prod'],
        ['description' => 'propel:build', 'command' => 'php app/console propel:build --insert-sql'],
        ['description' => 'assets:install', 'command' => 'php app/console assets:install'],
        ['description' => 'cache:warmup', 'command' => 'php app/console cache:warmup'],
    ];

    protected function configure()
    {
        $this
            ->setName('c33s:rebuild')
            ->setDescription('replaces the rebuild.bat/rebuild.sh. the command builds the model, loads the fixtures and creates the fos users based upon the user.yml')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will overwrite the existing config files'
            )
            ->addOption(
                'update',
                null,
                InputOption::VALUE_NONE,
                'If set Fos user password and roles get updated and not created'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $fs->mkdir('app/data');

        if ($input->getOption('update'))
        {
            $this->addLoadPropelFixtures();
            $this->addCreateFosUsersUpdateToCommandSet();
        }
        else
        {
            $this->addCreateFosUsersToCommandSet();
            $this->addLoadPropelFixtures();
        }

        $this->runCommandSets($input,$output);
    }

    protected function runCommandSets(InputInterface $input, OutputInterface $output)
    {
        $this->executeCommandSets($this->commandSets, $output);
    }

    protected function addCreateFosUsersToCommandSet()
    {
        $users = $this->getContainer()->getParameter('fos_users');

        if (is_array($users))
        {
            $fosCommandSet = array();

            foreach ($users as $key => $user)
            {
                $fosCommandSet[] = array('description' => 'fos:user:create', 'command' => "php app/console fos:user:create ${user['name']} ${user['email']} '${user['password']}'");
                foreach ($user['roles'] as $role)
                {
                    $fosCommandSet[] = array('description' => 'fos:user:promote', 'command' => "php app/console fos:user:promote ${user['name']} ${role}");
                }
            }

            $this->commandSets = array_merge($this->commandSets, $fosCommandSet);
        }
    }

    protected function addLoadPropelFixtures()
    {
        $this->commandSets[] = array('description' => 'propel:fixtures:load', 'command' => 'php app/console propel:fixtures:load');
    }

    protected function addCreateFosUsersUpdateToCommandSet()
    {
        $users = $this->getContainer()->getParameter('fos_users');

        if (is_array($users))
        {
            $fosCommandSet = array();

            foreach ($users as $key => $user)
            {
                $fosCommandSet[] = array('description' => 'fos:user:change-password', 'command' => "php app/console fos:user:change-password ${user['name']} '${user['password']}'");
                $fosCommandSet[] = array('description' => 'fos:user:demote', 'command' => "php app/console fos:user:demote --super ${user['name']}");

                foreach ($user['roles'] as $role)
                {
                    $fosCommandSet[] = array('description' => 'fos:user:promote', 'command' => "php app/console fos:user:promote ${user['name']} ${role}");
                }
            }

            $this->commandSets = array_merge($this->commandSets, $fosCommandSet);
        }
    }
}
