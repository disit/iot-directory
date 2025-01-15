<?php

/* Dashboard Builder.
   Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence
   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as
   published by the Free Software Foundation, either version 3 of the
   License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU Affero General Public License for more details.
   You should have received a copy of the GNU Affero General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>. */
#edooooooooo
class DataComparator {
    private $link;
    private $kbHostIp;
    private $batchSize;

    public function __construct($dbConnection, $kbHostIp, $batchSize = 500) {
        $this->link = $dbConnection;
        $this->kbHostIp = $kbHostIp;
        $this->batchSize = $batchSize;
        $this->organization= $_GET['organization'];
}

    public function compareData() {
        $bufferDB = [];
        $bufferKB = [];
        $offset = 0;

        do {
            // Fetch KB data
            $kbData = $this->fetchKBData($offset);
            if ($kbData === false) {
                throw new Exception("Failed to fetch KB data");
    }

            // Fetch DB data
            $dbData = $this->fetchDBData($offset);
            if ($dbData === false) {
                throw new Exception("Failed to fetch DB data");
    }

            // Add new items to buffers
            $bufferDB = array_merge($bufferDB, $dbData);
            $bufferKB = array_merge($bufferKB, $kbData);

            $offset += $this->batchSize;

            // Break if both sources are exhausted
            if (empty($kbData) && empty($dbData)) {
                break;
    }

        } while (true);

        // Process final arrays to remove matching items
        $this->removeDuplicates($bufferDB, $bufferKB);

        return [
            'uniqueInDB' => array_values(array_filter($bufferDB)),
            'uniqueInKB' => array_values(array_filter($bufferKB))
        ];
    }

    private function fetchKBData($offset) {
        $query = $this->buildSPARQLQuery($offset);
        $encodedQuery = $this->kbHostIp . "/sparql?default-graph-uri=&query=" .
            urlencode($query) . "&format=application%2Fsparql-results%2Bjson&timeout=0&debug=on";

        $results = @file_get_contents($encodedQuery);
        if ($results === false) {
echo $query;
            return false;
    }

        $decodedResults = json_decode($results, true);
        return array_map(function($item) {
            return $item['s']['value'];
        }, $decodedResults['results']['bindings']);
    }

    private function fetchDBData($offset) {
//        $query = sprintf(
//            "SELECT uri FROM iotdb.devices WHERE organization = '%s' LIMIT %d OFFSET %d",
//            $this->organization,
//            $this->batchSize,
//            $offset
//        );
        $query ="SELECT uri FROM iotdb.devices WHERE organization='".$this->organization."' LIMIT ".$this->batchSize." OFFSET ".$offset;
        echo ("FETCHDBDATA query: ".$query."<br>");
        $result = mysqli_query($this->link, $query);
        if (!$result) {
echo mysqli_error($this->link);
    return false;
}

        return array_column(mysqli_fetch_all($result, MYSQLI_ASSOC), 'uri');
}

    private function removeDuplicates(&$dbArray, &$kbArray) {
        foreach ($dbArray as $key => $dbItem) {
            $dbItem = trim($dbItem);
            if (empty($dbItem)) {
                unset($dbArray[$key]);
                continue;
}

            foreach ($kbArray as $kbKey => $kbItem) {
                $kbItem = trim($kbItem);
                if (empty($kbItem)) {
                    unset($kbArray[$kbKey]);
                    continue;
}

                if ($dbItem === $kbItem) {
                    unset($dbArray[$key]);
                    unset($kbArray[$kbKey]);
            break;
        }
            }
        }

    }
    public function handleDbWithoutURI(&$results) {
        $noURIArray = [];
        $query ="SELECT contextBroker,id,organization FROM iotdb.devices WHERE uri is null AND organization='".$this->organization."'";
        echo ("handleDbWithoutURI query: ".$query."<br>");

        $result = mysqli_query($this->link, $query);
        if (!$result) {
            return false;
        }
        $result=mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($result as $key => $noURIitem) {
            array_push($noURIArray,$result[$key]["contextBroker"]."/".$result[$key]["organization"]."/".$result[$key]["id"]);
        }

        $filtered_noURI = array_values(array_filter($noURIArray));

        $uniqueInKBsubstr=[];

        foreach ($results["uniqueInKB"] as $key => $uniqueKBitem) {
            // Find the position of 'iot/' in the URI
            $pos = strpos($uniqueKBitem, 'iot/');

            // Check if 'iot/' is found
            if ($pos !== false) {
                // Extract the substring starting from 'iot/' and take the last part
                $lastPart = substr($uniqueKBitem, $pos + 4);
                //$lastPart = substr($lastPart, strrpos($lastPart, '/') + 1); // Extract after the last '/'
                array_push($uniqueInKBsubstr,$lastPart);
        }
    }

        $noURIbutInKBandDB = (array_intersect($filtered_noURI, $uniqueInKBsubstr));
        $noURIonlyinDB = array_diff($filtered_noURI, $uniqueInKBsubstr);


        return array_merge($results, ["noURIbutInKBandDB" => $noURIbutInKBandDB],["noURIonlyinDB" => $noURIonlyinDB]);


    }

    private function buildSPARQLQuery($offset) {
        return "SELECT ?s { 
            #?s a sosa:Sensor option (inference \"urn:ontology\").
            ?s schema:name ?n.
            ?s <http://purl.oclc.org/NET/UNIS/fiware/iot-lite#exposedBy> ?broker.
            ?broker <http://schema.org/name> ?brokerName.
            ?s km4c:organization \"".$this->organization."\".
            #OPTIONAL {?s km4c:model ?model.}
            #OPTIONAL {?s km4c:isMobile ?mobile.}
            #?s a ?sType.
            #?sType rdfs:subClassOf* ?sCategory.
            #?sCategory rdfs:subClassOf km4c:Service.
        }
        #ORDER BY ?s
        OFFSET " . ($offset + 1) . "
        LIMIT " . $this->batchSize;
    }

    public function displayResults($results) {
        if (!empty($results['uniqueInDB'])) {
            $countDB = count($results['uniqueInDB']);
            echo "<h3>Items only in Database ($countDB):</h3>";
            foreach ($results['uniqueInDB'] as $item) {
                echo htmlspecialchars(trim($item)) . "<br>";
        }
    }

        if (!empty($results['uniqueInKB'])) {
            $countKB = count($results['uniqueInKB']);
            echo "<h3>Items only in Knowledge Base ($countKB):</h3>";
            foreach ($results['uniqueInKB'] as $item) {
                echo htmlspecialchars(trim($item)) . "<br>";
            }
        }
        if (!empty($results['noURIbutInKBandDB'])) {
            $countNoURI = count($results['noURIbutInKBandDB']);
            echo "<h3>Items without URI but in DB and in KB: ($countNoURI):</h3>";
            foreach ($results['noURIbutInKBandDB'] as $item) {
                echo htmlspecialchars(trim($item)) . "<br>";
            }
        }
        if (!empty($results['noURIonlyinDB'])) {
            $countNoURI = count($results['noURIonlyinDB']);
            echo "<h3>Items without URI and only in DB: ($countNoURI):</h3>";
            foreach ($results['noURIonlyinDB'] as $item) {
                echo htmlspecialchars(trim($item)) . "<br>";
            }
        }

    }
}



include '../../config.php';
include '../../api/common.php';

require '../../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;
#error_reporting(E_ERROR);
session_start();




$encryptionMethod = "AES-256-CBC";
$encryptionInitKey = 'EncryptionIniKey';
$encryptionIvKey = 'IVKeyivKey123456';


$allowedElementIDs = [];
$allowedElementCouples = [];
$ownedElements = [];
$encrCpls = [];                 // MOD OWN-DEL
$encrDelCpls = [];              // MOD OWN-DEL
$encrDelGroupCpls = [];         // MOD OWN-DEL

$genFileContent = parse_ini_file("../../conf/environment.ini");
$personalDataFileContent = parse_ini_file("../../../dashboardSmartCity/conf/personalData.ini");
$env = $genFileContent['environment']['value'];

$host_PD= $personalDataFileContent["host_PD"][$env];
$token_endpoint= $personalDataFileContent["token_endpoint_PD"][$env];
$client_id= $personalDataFileContent["client_id_PD"][$genFileContent['environment']['value']];
$client_secret= $personalDataFileContent["client_secret_PD"][$genFileContent['environment']['value']];
$username= $personalDataFileContent["usernamePD"][$genFileContent['environment']['value']];
$password= $personalDataFileContent["passwordPD"][$genFileContent['environment']['value']];

$orgServiceUri=parse_ini_file("../../conf/serviceURI.ini");
$organizationApiURI= $orgServiceUri["organizationApiURI"][$env];


$generalFileContent = parse_ini_file("../../conf/general.ini");
$dbFileContent = parse_ini_file("../../conf/database.ini");
$dbname = $dbFileContent['dbname'][$env];
$host=$generalFileContent["host"][$env];
$usernamedb=$dbFileContent['username'][$env];
$passworddb=$dbFileContent['password'][$env];
$processId = 1;     // 0 = SELECT & UPDATE; 1 = UPSERT
$link = mysqli_connect($host, $usernamedb, $passworddb);
error_reporting(E_ALL);
ini_set('display_errors', 1);
//mysqli_select_db($link, $dbname);





$startTime = new DateTime(null, new DateTimeZone('Europe/Rome'));
$start_scritp_time = $startTime->format('c');
$start_scritp_time_string = explode("+", $start_scritp_time);
$start_time_ok = str_replace("T", " ", $start_scritp_time_string[0]);
echo("*** Starting IOT_Device_Update_DashboardWizard SCRIPT at: ".$start_time_ok."<br>");





//$queryIP = "SELECT DISTINCT kbIP FROM Dashboard.Organizations;";
//$rsIP = mysqli_query($link, $queryIP);

$kbInfo=get_organization_info($organizationApiURI,$_GET["organization"]);
echo $organizationApiURI." ".json_encode($kbInfo)."<br>";
$kbHostIp=$kbInfo['kbIP'];
echo ("KBURL: ".$kbHostIp. "</br>");

if($kbHostIp) {
    $totCount = 0;
//    while ($rowIP = mysqli_fetch_assoc($rsIP)) {

        //$kbHostIp = $rowIP['kbIP'];

        echo("</br>--------- Ingestion IOT for kbIP: " . $kbHostIp . "</br>");
        try {
            $comparator = new DataComparator($link, $kbHostIp);

            $results = $comparator->compareData();
            $results = $comparator ->handleDbWithoutURI($results);
            $comparator->displayResults($results);

        } catch (Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
//}
    }
}


$endTime = new DateTime(null, new DateTimeZone('Europe/Rome'));
$end_scritp_time = $endTime->format('c');
$end_scritp_time_string = explode("+", $end_scritp_time);
$end_time_ok = str_replace("T", " ", $end_scritp_time_string[0]);
echo("End IOT_Device_Update_DashboardWizard SCRIPT at: ".$end_time_ok);
