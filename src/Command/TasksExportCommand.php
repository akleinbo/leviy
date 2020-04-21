<?php

namespace App\Command;

use App\Entity\Tasks;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $tasks[] = ['Title', 'Description', 'Responsible', 'Client', 'Start', 'End', 'Total'];
        foreach($this->entityManager->getRepository('App:Tasks')->findAll() as $task) {
            $taskTotalTime = ($task->getEnd()->diff($task->getStart(), true));
            $tasks[] = [
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'responsible' => $task->getResponsible(),
                'client' => $task->getClient(),
                'start' => $task->getStart()->format('H:i'),
                'end' => $task->getEnd()->format('H:i'),
                'total' => $taskTotalTime->format('%H') . ':' . $taskTotalTime->format('%I')
            ];
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
}
