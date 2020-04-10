<?php

namespace C33s\CoreBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

trait CommandSetsTrait
{
    private function replacePhpExecutable(string $command): string
    {
        if (0 !== strpos($command, 'php ')) {
            return $command;
        }
        $php = (new PhpExecutableFinder())->find();

        return $php.substr($command, 3);
    }

    protected function executeCommandSets(array $commandSets, OutputInterface $output): void
    {
        foreach ($commandSets as $commandSet) {
            $output->writeln(sprintf('Running <comment>%s</comment> check.', $commandSet['description']));

            $process = new Process($this->replacePhpExecutable($commandSet['command']));
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo $buffer;
                } else {
                    echo $buffer;
                }
            });
        }
    }
}
