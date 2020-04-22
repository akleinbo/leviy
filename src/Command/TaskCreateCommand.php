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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskCreateCommand extends Command
{
    const FIRST_WEEKDAY_THE_MONTH = 'first weekday the month';
    const LAST_WEEKDAY_THE_MONTH = 'last weekday the month';

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
        $taskDuration       = new Question('Please enter task DURATION in minutes(30): ', 30);
        $taskSchedule       = new ChoiceQuestion('Please SCHEDULE task (monday): ', [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
            self::FIRST_WEEKDAY_THE_MONTH,
            self::LAST_WEEKDAY_THE_MONTH,
        ], 0);
        $taskRepeat         = new ChoiceQuestion('Repeat task(weekly): ', [
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
                ['Scheduled', $taskSchedule],
                ['Start', $this->getDateTime($taskSchedule)->format('Y-m-d')],
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
            $task->setScheduled($taskSchedule);
            $task->setStart($this->getDateTime($taskSchedule));
            $task->setToRepeat($taskRepeat);

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $io->success('Success, your task is now saved to the DB.');
            return 0;
        }

        return 0;
    }

    /**
     * @param $taskSchedule
     * @return DateTime
     */
    public function getDateTime($taskSchedule)
    {
        if ($taskSchedule === self::FIRST_WEEKDAY_THE_MONTH) {
            // todo: find the first weekday of workingday of a month
            $dateTime = new DateTime('now');
        } elseif ($taskSchedule === self::LAST_WEEKDAY_THE_MONTH) {
            $dateTime = new DateTime('now');
            $getDate = getdate(mktime(
                null,
                null,
                null,
                $dateTime->format('m') + 1,
                0,
                $dateTime->format('Y')
            ));
            $dateTime->setTimestamp($getDate[0]);
        } else {
            $dateTime = new DateTime('now');
            $dateTime->setTimestamp(strtotime($taskSchedule));
        }

        return $dateTime;
    }
}
