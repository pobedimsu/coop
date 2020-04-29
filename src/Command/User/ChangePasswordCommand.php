<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use App\Utils\UserValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangePasswordCommand extends Command
{
    protected static $defaultName = 'user:change-password';

    protected $io;
    protected $em;
    protected $passwordEncoder;
    protected $validator;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, UserValidator $validator)
    {
        parent::__construct();

        $this->em = $em;
        $this->passwordEncoder = $encoder;
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this
            ->setDescription('Change password for a user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the user')
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

        // Ask for the password if it's not defined
        $password = $input->getArgument('password');
        if (null !== $password) {
            $this->io->text(' > <info>Password</info>: '.str_repeat('*', mb_strlen($password)));
        } else {
            $password = $this->io->askHidden('Password (your type will be hidden)', [$this->validator, 'validatePassword']);
            $input->setArgument('password', $password);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);

        if (empty($user)) {
            $this->io->warning('User not found');

            return 0;
        }

        $this->validator->validatePassword($plainPassword);

        // See https://symfony.com/doc/current/book/security.html#security-encoding-password
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        $this->em->flush();

        $this->io->success(sprintf('Password for user "%s" was successfully updated.', $username));

        return 0;
    }
}
