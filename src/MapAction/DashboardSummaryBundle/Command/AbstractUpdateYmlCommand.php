<?php

namespace MapAction\DashboardSummaryBundle\Command;

use Symfony\Component\Yaml\Yaml;

use Symfony\Bundle\FrameworkBundle\Client;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractUpdateYmlCommand extends ContainerAwareCommand
{
    protected $ymlPrefix = null;
    protected $googleSpreadsheetId = null;

    protected function configure()
    {
        $this->setDescription(sprintf('Gets data from a corresponding google spreadsheet and saves it to %s.latest.yml', $this->ymlPrefix));

    }

    abstract protected function csv2yml(OutputInterface $output, $csvData);

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timestamp = time();
        $outputPath = sprintf('%s/../data/yml', $this->getContainer()->getParameter('kernel.root_dir'));


        if (false) {
            // get stuff from google
            $googleSpreadsheetURL = sprintf('https://docs.google.com/feeds/download/spreadsheets/Export?key=%s&exportFormat=csv&gid=0', $this->googleSpreadsheetId);
            // $csv =
        } else {
            // dummy file source
            $csvStr = file_get_contents(sprintf('%s/../data/spreadsheet-cache/%s.csv', $this->getContainer()->getParameter('kernel.root_dir'), $this->googleSpreadsheetId));
        }

        $scvRows = str_getcsv($csvStr, "\n"); //parse the rows
        foreach($scvRows as &$row) $row = str_getcsv($row, ","); //parse the items in rows

        // Convert csv to yml
        $yml = $this->csv2yml($output, $scvRows);

        // Save the file + symlink
        $filenameFixed = sprintf('%s/%s.%s.yml', $outputPath, $this->ymlPrefix, date('Y-m-d_His', $timestamp));
        $filenameLatest = sprintf('%s/%s.%s.yml', $outputPath, $this->ymlPrefix, 'latest');
        file_put_contents($filenameFixed, Yaml::dump($yml, 5));
        if (file_exists($filenameLatest)) {
            unlink($filenameLatest);
        }
        symlink(basename($filenameFixed), $filenameLatest);
    }
}
