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

class RolePromoteCommand extends Command
{
    protected static $defaultName = 'user:role:promote';

    /** @var SymfonyStyle */
    protected $io;
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Promotes a user by adding a role')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('role', InputArgument::OPTIONAL, 'The role')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: '.$username);
        } else {
            $username = $this->io->ask('Username');
            $input->setArgument('username', $username);
        }

        $role = $input->getArgument('role');
        if (null !== $role) {
            $this->io->text(' > <info>Role</info>: '.$role);
        } else {
            $role = $this->io->ask('Role');
            $input->setArgument('role', $role);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');

        $user = $this->em->getRepository(User::class)->findOneByUsername($username);

        if (!$user) {
            $this->io->warning('User not found');

            return self::SUCCESS;
        }

        if ($user->hasRole($role)) {
            $this->io->warning(sprintf('User "%s" did already have "%s" role.', $username, $role));

            return self::SUCCESS;
        }

        $user->addRole($role);

        $this->em->flush();

        $this->io->success(sprintf('User "%s" has been promoted as a super administrator. This change will not apply until the user logs out and back in again.', $username));

        return self::SUCCESS;
    }
}
