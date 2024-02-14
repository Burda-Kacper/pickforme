<?php

namespace App\Command;

use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'pfm:update',
    description: 'Updates data.',
    hidden: true
)]
class UpdateCommand extends Command
{
    const ACTION_CREATE = "Creating";
    const ACTION_UPDATE = "Updating";
    const DETAILS_ENDPOINT = true;

    /**
     * @var ConsoleSectionOutput $commandStatusOutput
     */
    private ConsoleSectionOutput $commandStatusOutput;

    /**
     * @var ConsoleSectionOutput $commandCurrentOutput
     */
    private ConsoleSectionOutput $commandCurrentOutput;

    /**
     * @var int $currentCounter
     */
    private int $currentCounter = 1;

    /**
     * @var int $maxCounter
     */
    private int $maxCounter;

    /**
     * @var DateTime $lastUpdateValue
     */
    private DateTime $lastUpdateValue;

    /**
     * @var string $entityName
     */
    private string $entityName;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * --- Maybe implement this command to run each other update command in sequence.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("This command should not be ran, it's a base for other Update Commands.");

        return Command::INVALID;
    }

    /**
     * @param OutputInterface $output
     * @param string $entityName
     *
     * @return void
     */
    protected function initCommand(OutputInterface $output, string $entityName): void
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $this->entityName = $entityName;
        $this->commandStatusOutput = $output->section();
        $this->commandCurrentOutput = $output->section();
        $this->lastUpdateValue = new DateTime('now');

        $this->commandStatusOutput->writeln("PFM {$this->entityName} Update in progress...");
    }

    /**
     * @return int
     */
    protected function finishCommand(): int
    {
        $this->commandCurrentOutput->clear();
        $this->commandStatusOutput->overwrite("PFM {$this->entityName} Update FINISHED!");

        return Command::SUCCESS;
    }

    /**
     * @param string $endpoint
     * @param bool $isDetailsEndpoint
     *
     * @return array
     */
    protected function readEndpoint(string $endpoint, bool $isDetailsEndpoint = false): array
    {
        $data = json_decode(file_get_contents($endpoint), true)['data'];

        if (!$isDetailsEndpoint) {
            $this->maxCounter = count($data);
        }

        return $data;
    }

    /**
     * @param string $action
     * @param string $message
     *
     * @return void
     */
    protected function incrementCurrentOutput(string $action, string $message): void
    {
        $this->commandCurrentOutput->overwrite("[{$this->currentCounter}/{$this->maxCounter}] {$action} {$this->entityName}: {$message}");
        $this->currentCounter++;
    }

    /**
     * @return DateTime
     */
    protected function getLastUpdateValue(): DateTime
    {
        return $this->lastUpdateValue;
    }
}
