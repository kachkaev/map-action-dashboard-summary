<?php

namespace MapAction\DashboardSummaryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDeploymentAvailabilityCommand extends AbstractUpdateYmlCommand
{
    protected $ymlPrefix = 'deployment-availability';
    protected $googleSpreadsheetId = '0Ah9EHnHhF-SQdHItTHFpRXljRm50UzVHSXpqUlItWmc';

    protected function configure()
    {
        parent::configure();
        $this
        ->setName('mapaction:update-deployment-availability');
    }


    protected function csv2yml(OutputInterface $output, $csvData) {
        //var_dump($csvData);


        // Index of names
        $rowToName = [];
        foreach($csvData as $index=>&$row) {
            $nameCandidate = trim($row[0]);
            if ($nameCandidate == '' || $nameCandidate == 'Name') {
                continue;
            }
            if ($nameCandidate == 'Deployed') {
                break;
            }

            $rowToName[$index] = $nameCandidate;
        }

        // Index of dates
        // Months are on row 1 [0]
        // Dates are on row 3 [2]
        $currentMonth = null;
        $columnToDate = [];
        foreach($csvData[0] as $index=>$cell) {
            $monthCandidate = strtolower(trim($cell));
            $dateCandidate = $csvData[2][$index];

            if (!is_numeric($dateCandidate)) {
                continue;
            }

            if ($monthCandidate) {
                switch ($monthCandidate) {
                    case 'january':
                    case 'february':
                    case 'march':
                    case 'april':
                    case 'may':
                    case 'june':
                    case 'july':
                    case 'august':
                    case 'september':
                    case 'october':
                    case 'november':
                    case 'december':
                        $currentMonth = $monthCandidate;
                        break;
                    default:
                        throw new \LogicException(sprintf('Wrong month: %s', $cell));
                }
            }

            $dateStrCandidate = sprintf('%d %s 2013', $dateCandidate, $currentMonth);
            $timestamp = strtotime($dateStrCandidate);
            $date = date('Y-m-d', $timestamp);
            $columnToDate[$index] = $date;
        }

        // Init structures
        $yml = [
            'dates' => [],
            'date_window' => [
                'start' => reset($columnToDate),
                'end' => end($columnToDate),
            ],
            'volunteers' => []
        ];

        $datesNode = &$yml['dates'];
        $volunteersNode = &$yml['volunteers'];

        // Fill the strucures
        foreach($rowToName as $rowIndex => $name) {
            foreach($columnToDate as $columnIndex => $date) {
                $reply = $csvData[$rowIndex][$columnIndex];
                switch(strtolower($reply)) {
                    case 'y':
                        $replyBundle = 'yes';
                        break;
                    case 'n':
                        $replyBundle = 'no';
                        break;
                    case 'c':
                        $replyBundle = 'check';
                        break;
                    case '':
                        $replyBundle = 'unknown';
                        break;
                    default:
                        $replyBundle = 'wrong';
                        $reply = '!'.$reply;
                        break;
                }

                if (!array_key_exists($name, $volunteersNode)) {
                    $volunteersNode[$name] = [];
                }
                if (!array_key_exists($date, $datesNode)) {
                    $datesNode[$date] = [];
                }

                if (!array_key_exists($replyBundle, $datesNode[$date])) {
                    $datesNode[$date][$replyBundle] = ['count' => 1, 'names' => [$name]];
                } else {
                    $datesNode[$date][$replyBundle]['count'] += 1;
                    $datesNode[$date][$replyBundle]['names'][] = $name;
                }
                $volunteersNode[$name][$date] = $replyBundle == 'wrong' ? $reply : $replyBundle;
            }
        }

        return $yml;
    }
}
