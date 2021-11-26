<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends Command
{
    protected static $defaultName = 'user:list';

    /** @var SymfonyStyle */
    private $io;
    private $em;

    protected function configure()
    {
        $this
            ->setDescription('Список всех пользователей')
        ;
    }

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var User[] $users */
        $users = $this->em->getRepository(User::class)->findBy([], ['created_at' => 'ASC']);

        $rows = [];
        foreach ($users as $user) {
            $roles = '';

            foreach ($user->getRoles() as $key => $role) {
                if ($role === 'ROLE_USER') {
                    $role = '';
                }

                $roles .= $role;

                if (count($user->getRoles()) > $key + 1) {
                    $roles .= "\n";
                }
            }

            $rows[] = [
                $user->getUsername(),
                $user->__toString(),
                $user->getTelegramUsername(),
                (string) $user->getInvitedByUser(),
                $user->getApiToken() ? '+' : '',
                $roles,
                $user->getCreatedAt()->format('Y-m-d H:i'),
                $user->getLastLogin() ? $user->getLastLogin()->format('Y-m-d H:i') : '',
            ];
        }

        $this->io->table(['Username', 'FIO', 'Telegram', 'Inviter', 'API', 'Roles', 'Created At', 'Last login'], $rows);

        $this->io->writeln("Всего: ".count($users));

        return self::SUCCESS;
    }
}
