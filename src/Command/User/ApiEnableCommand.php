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

class ApiEnableCommand extends Command
{
    protected static $defaultName = 'user:api:enable';

    private $io;
    private $em;

    protected function configure()
    {
        $this
            ->setDescription('Включение API для юзера')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user')
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

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: '.$username);
        } else {
            $username = $this->io->ask('Username');
            $input->setArgument('username', $username);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

        if ($user) {
            do {
                $token = $this->gneratetToken(64);
                $user2 = $this->em->getRepository(User::class)->findOneBy(['api_token' => $token]);

            } while (!empty($user2));

            $user->setApiToken($token);
            $this->em->flush();

            $this->io->writeln("<info>Токен создан</info>");
            $this->io->writeln('');
            $this->io->writeln($user->getApiToken());
            $this->io->writeln('');
        } else {
            $this->io->writeln("<error>Пользователь не найден</error>");
        }

        return 0;
    }

    protected function gneratetToken(int $length): string
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "_-=.@#";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet);

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }

        return $token;
    }
}
