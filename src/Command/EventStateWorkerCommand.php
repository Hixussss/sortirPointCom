<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:event-state-worker',
    description: 'Add a short description for your command',
)]
class EventStateWorkerCommand extends Command
{
    protected static $defaultName = 'app:event-state-worker';
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct();
        $this->messageBus = $messageBus;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Dispatches an event state update message to the Messenger queue.')
            ->setHelp('This command dispatches a message to update event states based on their dates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messageBus->dispatch(new \App\Message\EventStateUpdateMessage());
        $output->writeln('<info>Event state update message dispatched successfully.</info>');

        return Command::SUCCESS;
    }
}