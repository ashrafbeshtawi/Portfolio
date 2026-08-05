<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ContentSeeder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed',
    description: 'Seed the database with the initial portfolio content (no-op when content already exists).',
)]
final class SeedCommand extends Command
{
    public function __construct(private readonly ContentSeeder $seeder)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->seeder->seed()) {
            $io->success('Database seeded with initial portfolio content.');
        } else {
            $io->info('Database already contains content — nothing to do.');
        }

        return Command::SUCCESS;
    }
}
