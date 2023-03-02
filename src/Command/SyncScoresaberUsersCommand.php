<?php

namespace App\Command;

use App\Connector\ScoresaberApi;
use App\Entity\Method;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-scoresaber-users',
    description: 'Synchronize the users with the scoresaber.',
)]
class SyncScoresaberUsersCommand extends Command
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('method', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('method', null, InputOption::VALUE_OPTIONAL, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $methodAlias = $input->getArgument('method');

        if ($methodAlias) {
            $io->note(sprintf('<info>You passed an argument: %s</info>', $methodAlias));
        }

        if ($methodAlias = $input->getOption('method')) {
            $io->note(sprintf('You passed an option (method name): %s', $methodAlias));
        } else {
            $io->note('<info>You did not pass an option (method name)</info>');
            return Command::INVALID;
        }

        if (!$method = $this->em->getRepository(Method::class)->findOneBy(['alias' => $methodAlias])) {
            $io->error(sprintf('Method with alias %s does not exist', $methodAlias));
            return Command::INVALID;
        }

        $method->setType('in process');
        $this->em->persist($method);
        $this->em->flush();

        $users = $this->em->getRepository(User::class)->findAll();
        $scoresaberApi = new ScoresaberApi();
        $mappedUsers = $scoresaberApi->mapUsersData($users);

        $io->title('Start synchronizing scoresaber users');
        $io->info([
            'Basic infrormation about synchronization:',
            sprintf('Total users on web: %s', count($users)),
            sprintf('Total mapped users: %s', array_count_values(array_column($mappedUsers, 'mapped'))[1]),
            sprintf('Started: %s', date('Y-m-d H:i:s')),
        ]);
        $io->newLine(1);
        foreach ($mappedUsers as $key => $mappedUser) {
            $io->section(sprintf('User ID: %s', $key));
            $io->text([
                sprintf('Performance points: %s', $mappedUser['pp']),
                sprintf('Rank: %s', $mappedUser['rank']),
                sprintf('Country rank: %s', $mappedUser['countryRank']),
                sprintf('Average ranked accuracy: %s', $mappedUser['averageRankedAccuracy']),
            ]);

            if($user = $this->em->getRepository(User::class)->findOneBy(['id' => $key])) {
                $user->setRank($mappedUser['rank']);
                $user->setCountryRank($mappedUser['countryRank']);
                $user->setAvgPercentage($mappedUser['averageRankedAccuracy']);
                $user->setPp($mappedUser['pp']);
                $this->em->persist($user);
                $this->em->flush();
            } else {
                $io->note(sprintf('User with ID %s does not exist', $key));
            }
        }

        $method->setLog(sprintf('Total users on web: %s. ', count($users)) . sprintf('Total mapped users: %s', array_count_values(array_column($mappedUsers, 'mapped'))[1]));
        $method->setType(LOGGER_TYPE_SUCCESS);
        $this->em->persist($method);
        $this->em->flush();

        $io->success(sprintf('Synchronization was successful, ended at: %s', date('Y-m-d H:i:s')));

        return Command::SUCCESS;
    }
}
