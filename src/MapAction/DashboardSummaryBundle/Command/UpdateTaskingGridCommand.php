<?php

namespace MapAction\DashboardSummaryBundle\Command;

use Symfony\Component\Console\Helper\ProgressHelper;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTaskingGridCommand extends AbstractUpdateYmlCommand
{
    protected $ymlPrefix = 'tasking-grid';
    protected $googleSpreadsheetId = '0Ah9EHnHhF-SQdE9mcXhIOXRYblRISHJmanZmVnVES0E';

    protected function configure()
    {
        parent::configure();
        $this
        ->setName('mapaction:update-tasking-grid');
    }

    protected function csv2yml(OutputInterface $output, $csvData)
    {
        // Get List of names

        // Get l

        // Index of names
        $rowToName = [];
        foreach($csvData as $index=>&$row) {
            $nameCandidate = trim(str_replace('  ', '', $row[0]));
            //var_dump($index, $nameCandidate);
            if ($index < 14
                    || $nameCandidate == ''
                    || is_numeric($row[1])
                    || $nameCandidate == 'TOTAL AVAILABLE - OPERATIONAL + STAFF + OST'
                    || $nameCandidate == 'Operational'
                    || $nameCandidate == 'Staff'
                    || $nameCandidate == 'Staff'
                ) {
                continue;
            }

            $rowToName[$index] = $nameCandidate;
        }

        // Index of events

        // Init structure
        $yml = [
            'events' => []
        ];

        $eventsNode = &$yml['events'];

        $progress = new ProgressHelper();
        $progress->start($output, count($csvData[0]));

        // Fill the structure
        foreach($csvData[0] as $columnIndex => $eventName) {
            $progress->advance();

            if ($columnIndex < 2 || !$eventName) {
                continue;
            }


            $event = [
                'name' => $csvData[0][$columnIndex],
                'time_range' => $csvData[1][$columnIndex],
                'type' => $csvData[2][$columnIndex],
                'location_name' => $csvData[3][$columnIndex],
                '_location_lat' => null,
                '_location_lon' => null,
                'vol_reqs' => $csvData[4][$columnIndex],
                //'expectations' => $csvData[6][$columnIndex],
                'availability' => [],
            ];

            // Get coordinates
            try {
                //$a = 0/0;
                $nominatumURL = sprintf('http://nominatim.openstreetmap.org/search?q=%s&format=json', urlencode($event['location_name']));
                $nominatumResult = file_get_contents($nominatumURL);
                $nominatumResultJson = json_decode($nominatumResult, true);
                $event['_location_lat'] = round($nominatumResultJson[0]['lat'], 2);
                $event['_location_lon'] = round($nominatumResultJson[0]['lon'], 2);
            } catch (\Exception $e) {
            }

            foreach($rowToName as $rowIndex => $personName) {
                $reply = $csvData[$rowIndex][$columnIndex];
                switch(strtolower($reply)) {
                    case 'y':
                    case 'yes':
                        $replyBundle = 'yes';
                        break;
                    case 'n':
                    case 'no':
                        $replyBundle = 'no';
                        break;
                    case 'p':
                    case 'possibly':
                        $replyBundle = 'possibly';
                        break;
                    case 's':
                    case 'selected':
                        $replyBundle = 'selected';
                        break;
                    case '':
                        $replyBundle = 'unknown';
                        break;
                    default:
                        $replyBundle = 'wrong';
                        $reply = '!'.$reply;
                        break;
                }

                $availabilityNode = &$event['availability'];
                if (!array_key_exists($replyBundle, $availabilityNode)) {
                    $availabilityNode[$replyBundle] = ['count' => 1, 'names' => [$personName]];
                } else {
                    $availabilityNode[$replyBundle]['count'] += 1;
                    $availabilityNode[$replyBundle]['names'][] = $personName;
                }
            }

            $eventsNode []= $event;
        }

        $progress->finish();

        return $yml;
    }
}
