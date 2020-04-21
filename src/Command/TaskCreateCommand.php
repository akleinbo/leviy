<?php

namespace App\Command;

use App\Entity\Tasks;
use DateInterval;
use DatePeriod;
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
use Symfony\Component\Console\Question\ChoiceQuestion;
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
        $taskTitle          = new Question('Please give your task a TITLE: ', 'Title');
        $taskDescription    = new Question('Please give your task a brief DESCRIPTION: ', 'Description');
        $taskResponsible    = new Question('Please assign task to RESPONSIBLE: ', 'Responsible');
        $taskClient         = new Question('Please assign task to CLIENT: ', 'Client');
        $taskDuration       = new Question('Please enter task DURATION in minutes: ', 30);
        $taskSchedule       = new ChoiceQuestion('Please SCHEDULE task: ', [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ], 0);
        $taskRepeat         = new ChoiceQuestion('Repeat task ', [
            'none',
            'daily',
            'weekly',
            'monthly'
        ], 2);

        # questions
        $taskTitle          = $helper->ask($input, $output, $taskTitle);
        $taskDescription    = $helper->ask($input, $output, $taskDescription);
        $taskResponsible    = $helper->ask($input, $output, $taskResponsible);
        $taskClient         = $helper->ask($input, $output, $taskClient);
        $taskDuration       = $helper->ask($input, $output, $taskDuration);
        $taskSchedule       = $helper->ask($input, $output, $taskSchedule);
        $taskRepeat         = $helper->ask($input, $output, $taskRepeat);

        # start
        $taskStart = new DateTime('now');
        // $taskStart->setTimestamp(strtotime($taskSchedule));

        // dd($taskDuration);

        # output
        $io->title('Task Summary');
        $io->table(
            ['Items', 'Values'],
            [
                ['Title', $taskTitle],
                ['Description', $taskDescription],
                ['Responsible', $taskResponsible],
                ['Client', $taskClient],
                ['Duration', $taskDuration],
                ['Date', $taskStart->format('Y-m-d')],
                ['Scheduled', $taskSchedule],
                ['Repeat', $taskRepeat]
            ]
        );

        $question = new ConfirmationQuestion('Is this correct?', false);

        if (!$helper->ask($input, $output, $question)) {
            $task = new Tasks();
            $task->setTitle($taskTitle);
            $task->setDescription($taskDescription);
            $task->setResponsible($taskResponsible);
            $task->setClient($taskClient);
            $task->setDuration((int)$taskDuration);
            $task->setScheduled($taskStart);
            $task->setToRepeat($taskRepeat);

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $io->success('Success, your task is now saved to the DB.');
            return 0;
        }

        return 0;
    }
}
