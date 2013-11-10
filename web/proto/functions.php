<?php
require_once 'vendor/autoload.php'; //symphony stuff
use Symfony\Component\Yaml\Parser;

function getAvailabilityData() {
   $yaml = new Parser();
   return $yaml->parse(file_get_contents("deployment_availability.latest.yml"));
}

function getTaskingGridData() {
   $yaml = new Parser();
   return $yaml->parse(file_get_contents("tasking_grid.latest.yml"));
}


function availabilityCountersDisplay() {
   $availability_data = getAvailabilityData();
   
   /*
   print "<pre>"; 
   var_dump($availability_data);
   print "</pre>";
   */
   
   $oneday = 24 * 60 * 60;
   
   $todaysDate = intval( ceil( time() / $oneday) * $oneday);
   $todaysDate = 1384473600;
   
   if (!array_key_exists($todaysDate, $availability_data["dates"])) {
   	   
      $display = "Today's date not found in spreadsheet data (" . $todaysDate . ")";
      
      
   } else {
   	   
      $today_data = $availability_data["dates"][$todaysDate];
      
      $display = "";
      
      foreach ($today_data as $response_type => $data) {
      	 if ($display!="") $display.=", ";
      	 $mouseover = implode( ", " , $data["names"] );
      	 $display .= '<span title="' . $mouseover . '">' . $response_type . ':' . $data["count"] . '</span>';
      }
   }
   
   return $display;
}

