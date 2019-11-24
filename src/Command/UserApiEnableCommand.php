<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserApiEnableCommand extends Command
{
    protected static $defaultName = 'user:api:enable';

    /** @var SymfonyStyle */
    private $io;
    private $em;

    protected function configure()
    {
        $this
            ->setDescription('Включение API для юзера')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user')
        ;
    }

    /**
     * WitnessListCommand constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after initialize() and before execute(). Its purpose
     * is to check if some of the options/arguments are missing and interactively
     * ask the user for those values.
     *
     * This method is completely optional. If you are developing an internal console
     * command, you probably should not implement this method because it requires
     * quite a lot of work. However, if the command is meant to be used by external
     * users, this method is a nice way to fall back and prevent errors.
     */
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
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
    }

    /**
     * @param $length
     *
     * @return string
     */
    protected function gneratetToken($length): string
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
