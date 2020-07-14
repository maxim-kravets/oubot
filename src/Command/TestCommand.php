<?php

namespace App\Command;

use App\Repository\ItemRepositoryInterface;
use App\Repository\PromocodeRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static $defaultName = 'test';

    private UserRepositoryInterface $userRepository;
    private ItemRepositoryInterface $itemRepository;
    private PromocodeRepositoryInterface $promocodeRepository;

    public function __construct(string $name = null, UserRepositoryInterface $userRepository, ItemRepositoryInterface $itemRepository, PromocodeRepositoryInterface $promocodeRepository)
    {
        parent::__construct($name);
        $this->userRepository = $userRepository;
        $this->itemRepository = $itemRepository;
        $this->promocodeRepository = $promocodeRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $item = $this->itemRepository->findById(100);
        $promocode = $this->promocodeRepository->findById(5);

        $users = $this->userRepository->getListForMailing($item, $promocode);

        foreach ($users as $user) {
            dd($user);
        }

        return 0;
    }
}
