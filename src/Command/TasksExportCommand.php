<?php

namespace App\Command;

use App\Entity\Tasks;
use DateInterval;
use DatePeriod;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use DateTime;

class TasksExportCommand extends Command
{
    protected static $defaultName = 'app:export-tasks';

    const DELIMITER = ';';

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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var $task Tasks */

        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        # path
        $userPath = new Question('Please enter file export path: ', '/Users/albert/Downloads');
        $userPath = $helper->ask($input, $output, $userPath);

        # tasks
        $tasks = [];
        $tasks[] = ['Title', 'Description', 'Responsible', 'Client', 'Duration', 'Scheduled', 'Start', 'Repeat'];

        foreach($this->entityManager->getRepository('App:Tasks')->findAll() as $task) {
            foreach ($this->getDateInterval($task) as $interval) {
                $tasks[] = [
                    'title' => $task->getTitle(),
                    'description' => $task->getDescription(),
                    'responsible' => $task->getResponsible(),
                    'client' => $task->getClient(),
                    'duration' => $task->getDuration(),
                    'scheduled' => $task->getScheduled(),
                    'start' => $interval->format('Y-m-d'),
                    'repeat' => $task->getToRepeat()
                ];
            }
        }

        $path = $userPath . '/Tasks-Export-' . date('YmdHis') . '.csv';

        $outputBuffer = fopen($path, 'w');
        foreach($tasks as $task) {
            fputcsv($outputBuffer, $task, self::DELIMITER);
        }
        fclose($outputBuffer);

        # output
        $io->title('Export Summary');
        $io->writeln('Items:' . count($tasks));
        $io->writeln('Path:' . $path);

        $io->success('Success, your tasks are exported.');

        return 0;
    }

    public function getDateInterval(Tasks $task)
    {
        if ($task->getToRepeat() == 'weekly') {
            $interval = new DateInterval('P1W');
        } elseif($task->getToRepeat() == 'monthly') {
            $interval = new DateInterval('P1M');
        } else {
            $interval = new DateInterval('');
        }

        $end = new DateTime('now');
        $end->modify('+3 months');

        return new DatePeriod($task->getStart(), $interval ,$end);
    }
}
