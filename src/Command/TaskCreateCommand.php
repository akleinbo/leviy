<?php

namespace App\Command;

use App\Entity\Tasks;
use DateTime;
use Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskCreateCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:create-task';

    /**
     * [$entityManager description]
     * @var EntityManager
     */
    private $entityManager;

    /**
     * [__construct description]
     * @param EntityManagerInterface $entityManager [description]
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

//    /**
//     *
//     */
//    protected function configure()
//    {
//        $this
//            ->setDescription('Add a short description for your command')
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
//        ;
//    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        # task
        $taskTitle          = new Question('Please give your task a title: ', '');
        $taskDescription    = new Question('Please give your task a brief description: ', '');
        $taskResponsible    = new Question('Please assign task to responsible: ', '');
        $taskClient         = new Question('Please assign task to client(customer): ', '');
        $taskStart          = new Question('Please enter start datetime(Y-m-d H:i:s): ', '');
        $taskEnd            = new Question('Please enter end datetime(Y-m-d H:i:s): ', '');

        # questions
        $taskTitle          = $helper->ask($input, $output, $taskTitle);
        $taskDescription    = $helper->ask($input, $output, $taskDescription);
        $taskResponsible    = $helper->ask($input, $output, $taskResponsible);
        $taskClient         = $helper->ask($input, $output, $taskClient);
        $taskStart          = $helper->ask($input, $output, $taskStart);
        $taskEnd            = $helper->ask($input, $output, $taskEnd);

        # output
        $io->title('Task Summary');
        $io->table(
            ['Items', 'Values'],
            [
                ['Title', $taskTitle],
                ['Description', $taskDescription],
                ['Responsible', $taskResponsible],
                ['Client', $taskClient],
                ['Start', $taskStart],
                ['End', $taskEnd]
            ]
        );

        $question = new ConfirmationQuestion('Is this correct?', false);

        if (!$helper->ask($input, $output, $question)) {
            $task = new Tasks();
            $task->setTitle($taskTitle);
            $task->setDescription($taskDescription);
            $task->setResponsible($taskResponsible);
            $task->setClient($taskClient);
            $task->setStart(new DateTime($taskStart));
            $task->setEnd(new DateTime($taskEnd));

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $io->success('Success, your task is now saved to the DB.');
            return 0;
        }

        return 0;
    }
}
