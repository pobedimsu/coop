<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use SmartCore\Bundle\TexterBundle\Entity\Text;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends Command
{
    protected static $defaultName = 'app:init';

    private SymfonyStyle $io;
    private EntityManagerInterface $em;

    protected function configure()
    {
        $this
            ->setDescription('Инициализация нового проекта')
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
        $isUpdated = false;
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

            $isUpdated = true;
        }

        $texts = [
            'homepage' => '<h1>Главная</h1>',
            'on_login_page' => '',
        ];

        foreach ($texts as $name => $content) {
            $text = $this->em->getRepository(Text::class)->findOneBy(['name' => $name]);

            if (empty($text)) {
                $text = new Text();
                $text
                    ->setName($name)
                    ->setText($content)
                ;
                $this->em->persist($text);
                $this->em->flush();

                $this->io->writeln("<info>Создан текст '" . $name . "'</info>");

                $isUpdated = true;
            }
        }

        if (!$isUpdated) {
            $this->io->writeln('Инициализация не требуется');
        }

        return self::SUCCESS;
    }
}
