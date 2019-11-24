<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends Command
{
    protected static $defaultName = 'app:init';

    /** @var SymfonyStyle */
    private $io;
    private $em;

    protected function configure()
    {
        $this
            ->setDescription('Инициализация нового проекта')
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $categories = $this->em->getRepository(Category::class)->findBy([]);

        if (empty($categories)) {
            $category = new Category();
            $category
                ->setName('first')
                ->setTitle('Первая категория')
            ;

            $this->em->persist($category);
            $this->em->flush();

            $this->io->writeln("<info>Создана 'Первая категория'</info>");
        } else {
            $this->io->writeln('Инициализация не требуется');
        }
    }
}
