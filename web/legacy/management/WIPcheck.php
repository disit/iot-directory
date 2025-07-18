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

class UriComparison {
    private $kbHostIp;
    private $batchSize = 1000;
    private $link;
    private $link_own;
    private $organization;

    /**
     * Constructor initializes connections and configurations
     *
     * @param string $kbHostIp The host IP/URL for the knowledge base
     * @param mysqli $dbConnection Active MySQL connection
     * @param string $organization Organization identifier for filtering database results
     * @param mysqli $dbConnection_own Ownership db connection
     */
    public function __construct(string $kbHostIp, mysqli $dbConnection, string $organization, mysqli $dbConnection_own) {
        $this->kbHostIp = $kbHostIp;
        $this->link = $dbConnection;
        $this->organization = $organization;
        $this->link_own= $dbConnection_own;
        $this->link->set_charset("utf8mb4");
    }

    /**
     * Fetches ownership URIs from the profiledb database for the specific organization
     *
     * @return array List of URIs from ownership records for the current organization
     * @throws Exception If there's an error with the database query
     */
    public function fetchOwnershipData(&$reconstructedElementUrl): array {
        global $ownershipdburl;
        // Query to get non-deleted IOT device URIs from ownership table
        // Using REGEXP for exact organization match at the beginning of elementId
        $query = "SELECT elementId, elementUrl FROM $ownershipdburl
             WHERE elementType = 'IOTID' AND deleted IS NULL
             AND elementId REGEXP ?";

        // Create pattern that matches exact organization name at start followed by colon
        $organizationPattern = "^" . $this->organization . ":";

        try {
            $stmt = $this->link_own->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare ownership query: " . $this->link_own->error);
            }

            // Bind the organization pattern parameter
            $stmt->bind_param("s", $organizationPattern);

            if (!$stmt->execute()) {
                throw new Exception("Failed to execute ownership query: " . $stmt->error);
            }

            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Failed to get ownership results: " . $stmt->error);
            }

            // Fetch all results and extract URIs
            //$ownershipData = $result->fetch_all(MYSQLI_ASSOC);
            //$ownershipUris = array_column($ownershipData, 'elementUrl');
            $ownershipUris = [];
            while ($row = $result->fetch_assoc()) {

                // Se c'è l'id ma non l'uri, lo ricostruisco e lo aggiungo sia agli uri che ai recostructed uri
                if (!empty($row['elementId']) && (empty($row['elementUrl']) || $row['elementUrl'] === null)) {
                    $elementIdParts=explode(':',$row['elementId']);
                    $organization=$elementIdParts[0];
                    $broker=$elementIdParts[1];
                    $deviceName=$elementIdParts[2];


                    $rebuiltElementUrl="http://www.disit.org/km4city/resource/iot/".$broker."/".$organization."/".$deviceName;

                    $ownershipUris[] = $rebuiltElementUrl;
                    $reconstructedElementUrl[] = $rebuiltElementUrl;
                }

                // Only add uri to results if it's not empty
                if (!empty($row['elementUrl'])) {
                    $ownershipUris[] = $row['elementUrl'];
                }
            }

            $stmt->close();
            return array_unique($ownershipUris);

        } catch (Exception $e) {
            error_log("Error in fetchOwnershipData: " . $e->getMessage());
            throw $e;
        }
    }
    private function buildSPARQLQuery(int $offset): string {
        // Note: You'll need to customize this query based on your KB structure
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
        OFFSET " . ($offset ) . "
        LIMIT " . $this->batchSize;
        //return "SELECT DISTINCT ?s WHERE { ?s ?p ?o } LIMIT {$this->batchSize} OFFSET {$offset}";
    }



    /**
     * Compare URIs from all sources automatically, including reconstructable URIs
     * This method now automatically fetches ownership data as well
     *
     * @return array List of URIs with their presence status in each system
     */
    public function compareAllSourcesComplete(): array {
        try {
            // Fetch URIs from all sources
            $reconstructedUris=[];
            $reconstructedElementUrl=[];
            $databaseUris = $this->fetchAllDBData($reconstructedUris);
//            $reconstructableUris = $this->fetchReconstructableUris();
            $knowledgebaseUris = $this->fetchAllKBData();
            $ownershipUris = $this->fetchOwnershipData($reconstructedElementUrl);

            // Perform the comparison with all sources
            return $this->compareUriLists(
                $databaseUris,
                $knowledgebaseUris,
                $ownershipUris,
                $reconstructedUris,
                $reconstructedElementUrl
            );
        } catch (Exception $e) {
            error_log("Error in compareAllSourcesComplete: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetches URIs from the database with pagination
     *
     * @param int $offset Starting offset for the query
     * @return array List of URIs from the database
     * @throws Exception If there's an error with the database query
     */
    public function fetchDBData(int $offset,&$reconstructedUris): array {

        $query = "SELECT uri, id,contextBroker FROM iotdb.devices WHERE organization = ? LIMIT ? OFFSET ?";
        try {
            $stmt = $this->link->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare database query: " . $this->link->error);
            }
            // Bind parameters safely
            $stmt->bind_param("sii", $this->organization, $this->batchSize, $offset);
            // Execute the query
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute database query: " . $stmt->error);
            }
            // Get the results
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Failed to get query results: " . $stmt->error);
            }

            $uris = [];
            while ($row = $result->fetch_assoc()) {

                // Se c'è l'id ma non l'uri, lo ricostruisco e lo aggiungo sia agli uri che ai recostructed uri
                if (!empty($row['id']) && (empty($row['uri']) || $row['uri'] === null)) {

                    $rebuiltUri="http://www.disit.org/km4city/resource/iot/".$row["contextBroker"]."/".$this->organization."/".$row["id"];

                    $uris[] = $rebuiltUri;
                    $reconstructedUris[] = $rebuiltUri;
                }

                // Only add uri to results if it's not empty
                if (!empty($row['uri'])) {
                    $uris[] = $row['uri'];
                }
            }

            $stmt->close();
            return $uris;
        } catch (Exception $e) {
            error_log("Error in fetchDBData: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetches all URIs from the database using pagination
     *
     * @return array Complete list of URIs from the database
     */
    public function fetchAllDBData(&$reconstructedUris): array {
        $allUris = [];
        $offset = 0;

        try {
            while (true) {
                $uris = $this->fetchDBData($offset,$reconstructedUris);
                if (empty($uris)) {
                    break;
                }
                $allUris = array_merge($allUris, $uris);
                $offset += $this->batchSize;
            }

            return array_unique($allUris);
        } catch (Exception $e) {
            error_log("Error in fetchAllDBData: " . $e->getMessage());
            throw $e;
        }
    }

    public function fetchKBData(int $offset): array {
        $query = $this->buildSPARQLQuery($offset);
        $encodedQuery = $this->kbHostIp . "/sparql?default-graph-uri=&query=" .
            urlencode($query) . "&format=json";

        // Use a try-catch block for better error handling
        try {
            $sparqlResults = @file_get_contents($encodedQuery);
            if ($sparqlResults === false) {
                throw new Exception("Failed to fetch data from SPARQL endpoint");
            }

            $decodedResults = json_decode($sparqlResults, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to decode SPARQL results: " . json_last_error_msg() . $encodedQuery);
            }

            // Extract URIs from the results
            $uris = [];
            if (isset($decodedResults['results']['bindings'])) {
                foreach ($decodedResults['results']['bindings'] as $binding) {
                    if (isset($binding['s']['value'])) {
                        $uris[] = $binding['s']['value'];
                    }
                }
            }

            return $uris;
        } catch (Exception $e) {
            // Log the error or handle it as needed
            error_log("Error in fetchKBData: " . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Fetches all URIs from the knowledge base using pagination
     *
     * @return array Complete list of URIs from the knowledge base
     */
    public function fetchAllKBData(): array {
        $allUris = [];
        $offset = 0;

        while (true) {
            $uris = $this->fetchKBData($offset);
            if (empty($uris)) {
                break;
            }
            $allUris = array_merge($allUris, $uris);
            $offset += $this->batchSize;
        }

        return array_unique($allUris);
    }



public function fetchBrokerData(String $uri){
    $pattern = '/iot\/([^\/]+)\/([^\/]+)\/([^\/]+)$/';
    $matches = [];

    if (preg_match($pattern, $uri, $matches)){
        $contextbroker=$matches[1];
        $organization=$matches[2];
        $deviceId=$matches[3];
    }


    $query = "SELECT port,ip from iotdb.contextbroker WHERE name = '$contextbroker' AND organization='$organization'";
    $r = mysqli_query($this->link, $query);
    if (!$r) {//row should also be NOT empty since enforcement has been already made in the api
        $result["status"] = 'ko';
        $result["error_msg"] .= "Error in reading data from context broker.";
        $result["msg"] .= ' error in reading data from context broker ' . mysqli_error($this->link);
        $result["log"] .= ' error in reading data from context broker ' . mysqli_error($this->link) . $query;

    }
    $rowCB = mysqli_fetch_assoc($r);
    if ($rowCB["kind"] == 'external')
        $shouldbeRegistered = 'no';
    $ip = $rowCB["ip"];
    $port = $rowCB["port"];

    $url = 'http://'.$ip.':'.$port.'/v2/entities/'.$deviceId;

// Initialize cURL session
    $curl = curl_init();

// Set cURL options
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => false,     // We want the body too, for error messages
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST => "GET",
        // Add any required headers
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
        ],
    ]);

// Execute the request
    $response = curl_exec($curl);

// Get the HTTP response code
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Close cURL session
    curl_close($curl);


// Optionally check if it's a success code
    if ($httpCode == 200) {
        //deviceTrovato
        return true;
    } else {
        //device non trovato
        return false;
    }
    }

    public function compareUriLists(
        array $databaseUris,
        array $knowledgebaseUris,
        array $ownershipUris,
        array $reconstructedUris,
        array $reconstructedElementUrl
    ): array {
        // Create lookup arrays for faster checks
        $dbSet = array_flip($databaseUris);
        $kbSet = array_flip($knowledgebaseUris);
        $ownSet = array_flip($ownershipUris);
        $reconstructedSet = array_flip($reconstructedUris);
        $reconstructedSetOwnership = array_flip($reconstructedElementUrl);

        // Combine all URIs to process
        $allUris = array_unique(array_merge(
            $databaseUris,
            $knowledgebaseUris,
            $ownershipUris

        ));

        //con AllUris trovo gli uri univoci, ovvero che sono salvati almeno in un posto
        //poi ciclo su essi e guardo dove sono presenti, se ci sono allora lì l'inserimento è andato a buon fine.
        $results = [];

        foreach ($allUris as $uri) {
            $inDatabase = isset($dbSet[$uri]);
            $inKnowledgebase = isset($kbSet[$uri]);
            $inOwnership = isset($ownSet[$uri]);
            $isReconstructed = isset($reconstructedSet[$uri]);
            $isReconstructedOwnership = isset($reconstructedSetOwnership[$uri]);

            // Skip URIs that are present in all three main systems
            if ($inDatabase && $inKnowledgebase && $inOwnership) {
                continue;
            }

            // Skip URIs that are missing from all three main systems
            if (!$inDatabase && !$inKnowledgebase && !$inOwnership) {
                continue;
            }
            if($inDatabase){
                $isReconstructed = !$isReconstructed;
            }else{
                $isReconstructed=false;
            }
            if($inOwnership){
                $isReconstructedOwnership=!$isReconstructedOwnership;
            }else{
                $isReconstructedOwnership=false;
            }


            $inBroker=$this->fetchBrokerData($uri);


            // Create status object with consistent property names
            // At this point, we know the URI is partially present (in at least one system but not all)
            $status = [
                'uri' => $uri,
                'in_database' => $inDatabase,
                'in_knowledgebase' => $inKnowledgebase,
                'in_ownership' => $inOwnership,
                'has_uri_db' => $isReconstructed,
                'in_broker'=> $inBroker,
                'has_uri_own' => $isReconstructedOwnership
            ];

            $results[] = $status;
        }


        if(isset($log_path_device_check)){
            $log_path=$log_path_device_check."WIPcheckDump.txt";
        }else{
            $log_path="/tmp/WIPcheckDump.txt";
        }
        $logfile = fopen($log_path, "a") or die("Unable to open file!");
// Convert array to JSON format for better readability
        $jsonResults = json_encode($results, JSON_PRETTY_PRINT);
        fwrite($logfile, $jsonResults);
        fclose($logfile);

        return $results;
    }
}



include '../../config.php';
include '../../api/common.php';
require '../../sso/autoload.php';
use Jumbojett\OpenIDConnectClient;
#error_reporting(E_ERROR);

session_start();

//ricontrollare sia root admin

if ($_SESSION[loggedRole] !== 'RootAdmin') {
    $result['status'] = 'ko';
    $result['msg'] = 'unauthorized';
    $result['error_msg'] = 'you are not Root';
    $result['log'] = 'Please log as an admin';
    echo json_encode($result);
    die();
}









$allowedElementIDs = [];
$allowedElementCouples = [];
$ownedElements = [];
$encrCpls = [];                 // MOD OWN-DEL
$encrDelCpls = [];              // MOD OWN-DEL
$encrDelGroupCpls = [];         // MOD OWN-DEL

$genFileContent = parse_ini_file("../../conf/environment.ini");
$personalDataFileContent = parse_ini_file("../../../dashboardSmartCity/conf/personalData.ini");
$serviceURIFileContent = parse_ini_file("../../conf/serviceURI.ini");
$sso = parse_ini_file("../../conf/sso.ini");
$env = $genFileContent['environment']['value'];

$host_PD= $personalDataFileContent["host_PD"][$env];
$token_endpoint= $personalDataFileContent["token_endpoint_PD"][$env];
$clientId= $personalDataFileContent["client_id_PD"][$genFileContent['environment']['value']];
$clientSecret= $personalDataFileContent["client_secret_PD"][$genFileContent['environment']['value']];
$username= $personalDataFileContent["usernamePD"][$genFileContent['environment']['value']];
$password= $personalDataFileContent["passwordPD"][$genFileContent['environment']['value']];
$organizationServiceURI = $serviceURIFileContent["organizationApiURI"][$env];
$keycloakHostUri= $sso["keycloakHostUri"][$env];


$generalFileContent = parse_ini_file("../../conf/general.ini");
$dbFileContent = parse_ini_file("../../conf/database.ini");
$dbname = $dbFileContent['dbname'][$env];
$host=$generalFileContent["host"][$env];
$usernamedb=$dbFileContent['username'][$env];
$passworddb=$dbFileContent['password'][$env];
$link = mysqli_connect($host, $usernamedb, $passworddb);
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ERROR | E_PARSE);


//////////////////////////////////////////////////
//Ownership access parametrization
//////////////////////////////////////////////////
if($db_own_FileContent = parse_ini_file("../../conf/ownership_db.ini")) {
    $dbname_own = $db_own_FileContent['dbname_own'][$env];
    $host_own = $db_own_FileContent["host_own"][$env];
    $usernamedb_own = $db_own_FileContent['username_own'][$env];
    $passworddb_own = $db_own_FileContent['password_own'][$env];
    $link_own = mysqli_connect($host_own, $usernamedb_own, $passworddb_own);
}else{
    $link_own=$link;
}
//////////////////////////////////////////////////////////////////////////



$oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
$oidc->providerConfigParam(array(
    'token_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/token',
    'userinfo_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/userinfo'
));
$accessToken ="";
if (isset($_REQUEST['token'])) {

    $mctime = microtime(true);
    $tkn = $oidc->refreshToken($_REQUEST['token']);
    error_log("---- device.php:" . (microtime(true) - $mctime));
    $accessToken = $tkn->access_token;
}



$ownershipdburl="profiledb.ownership";


if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
} else {
    $result['status'] = 'ko';
    $result['msg'] = 'action not present';
    $result['error_msg'] = 'action not present';
    $result['log'] = 'WIPcheck.php action not present';
    my_log($result);
    mysqli_close($link);
    exit();
}


if($action=="check_devices"){

    Global $apiResult;
    $apiResult=[];
    $apiResult["status"]='ok';

    $kbUrl = preg_replace('/\/api\/v1\//', '',  $_REQUEST['kbUrl']);

    try {
        // Set up database connection with access to both iotdb and profiledb
        $dbConnection = $link;
        $dbConnection_own = $link_own;
        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }

        // Initialize the comparison class
        $comparison = new UriComparison(
            $kbUrl,
            $dbConnection,
            $_REQUEST['organization'],
            $dbConnection_own
        );

        // Get comparison results from all sources
        $results = $comparison->compareAllSourcesComplete();

        // Process and display the results in a clear, organized way


        foreach ($results as $result) {
            $apiResult['result'][$result['uri']] = [
                'database_record' => (bool)$result['in_database'],
                'knowledge_base' => (bool)$result['in_knowledgebase'],
                'ownership_record' => (bool)$result['in_ownership'],
                'has_uri_db' => (bool)$result['has_uri_db'],
                'broker_record'=>(bool)$result['in_broker'],
                'has_uri_own' =>(bool)$result['has_uri_own'],
                'action_taken' => ''
            ];
        }


        $sql="TRUNCATE TABLE iotdb.devicecheck";
        if ($link->query($sql) !== TRUE) {
            $apiResult['error']=$link->error;
        }


        $values = [];
        $date = new DateTime();


        $formatted_date = $date->format('Y-m-d H:i:s');
        foreach ($apiResult["result"] as $uri => $value) {


                // Escape variables to protect against SQL injection
                $uri = $link->real_escape_string($uri);
                $checkdate = $link->real_escape_string($formatted_date);
                $organization = $link->real_escape_string($_REQUEST['organization']);

                // Correctly access the values in $value
                $isInDB = $link->real_escape_string($value['database_record'] ? 'true' : 'false');
                $isInKB = $link->real_escape_string($value['knowledge_base'] ? 'true' : 'false');
                $isInOwnership = $link->real_escape_string($value['ownership_record'] ? 'true' : 'false');
                $haveUri_db = $link->real_escape_string($value['has_uri_db'] ? 'true' : 'false');
                $isInBroker = $link->real_escape_string($value['broker_record'] ? 'true' : 'false');
                $haveURI_own = $link->real_escape_string($value['has_uri_own'] ? 'true' : 'false');



            // Prepare the values for SQL insertion
                $values[] = "('$uri', '$checkdate', '$organization', '-', $isInDB, $isInKB, $isInOwnership, $haveUri_db,$isInBroker,$haveURI_own)";

        }

        $sql = "INSERT INTO iotdb.devicecheck (uri, checkdate, organization, log, isInDB, isInKB, isInOwnership, haveURI,isInBroker,haveURI_own) VALUES " . implode(", ", $values);

        if ($link->query($sql) !== TRUE) {
            $apiResult['error'] .= $link->error;
        }





        // Close the database connection when done
        $link->close();
        echo json_encode($apiResult);

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        error_log("URI comparison error: " . $e->getMessage());
    }

} else if($action=="checkLastRun"){

    //action per vedere se cè una last run (DOVREBBE ESSERE OK)
    Global $apiResult;
    $apiResult=[];
    $apiResult["status"]='ok';
    if (isset($_REQUEST['organization'])) {
        $organization = $_REQUEST['organization'];


        //$organization='http://virtuoso-kb:8890';


        $query = $link->prepare("SELECT checkdate FROM iotdb.devicecheck WHERE organization=? LIMIT 1");

        if ($query === false) {
            $apiResult["status"] = 'ko';
            $apiResult["message"] = "Error in query preparation: " . $link->error;
            echo json_encode($apiResult);
            exit();
        }

        $query->bind_param('s', $organization);


        if (!$query->execute()) {
            $apiResult["status"] = 'ko';
            $apiResult["message"] = "Error in query execution: " . $query->error;
            echo json_encode($apiResult);
            exit();
        }

        $result = $query->get_result();

        if ($result->num_rows > 0) {

            $result = mysqli_fetch_row($result);
            $apiResult["status"]='ok';
            $apiResult["lastcheck"]=$result[0];
        }else{
            $apiResult["status"]='ok';
            $apiResult["lastcheck"]=null;
        }

        $query->close();
        echo json_encode($apiResult);

    }



} else if ($action == "recoverLastRun") {
    Global $apiResult;
    $apiResult = [];  // Initialize the API result array
    $apiResult["status"] = 'ok';

    if (isset($_REQUEST['organization'])) {
        $organization = $_REQUEST['organization'];

        // Query to select all the necessary fields
        $sql = "
            SELECT uri, isInDB, isInKB, isInOwnership, haveURI,log,isInBroker,haveURI_own
            FROM iotdb.devicecheck
            WHERE organization = '" . $link->real_escape_string($organization) . "'
        ";

        $devices = [];
        $result = $link->query($sql);

        // If the query returns results
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $uri = $row['uri'];


                // Rebuild the structure for the result array

                if (!isset($apiResult['result'][$uri])) {
                    $apiResult['result'][$uri] = [
                        'database_record' => (bool)$row['isInDB'],
                        'knowledge_base' => (bool)$row['isInKB'],
                        'ownership_record' => (bool)$row['isInOwnership'],
                        'is_reconstructable' => $row['is_reconstructable'] ?? false,
                        'broker_record'=>(bool)$row['isInBroker'],
                        'has_uri_db' => (bool)$row['haveURI'],
                        'has_uri_own' =>(bool)$row['haveURI_own'],
                        'action_taken' => $row["log"]  // Assuming no action has been taken
                    ];
                }
            }
        }
    }
    echo json_encode($apiResult);
}else if ($action == "applyRecoverDelete") {
    global $apiResult;
    global $userforOwnership;
    $apiResult = [];  // Initialize the API result array
    $apiResult["status"] = 'ok';
    $apiResult["opResult"] = [];


    $userforOwnership=$_REQUEST['userForOwnership'];


    $kbUrl = $_REQUEST['kbUrl'];
    //echo $_REQUEST['kbUrl'];

    $k1 = $_REQUEST["k1"];
    $k2 = $_REQUEST["k2"];

    //se manca il token non faccio nulla
    if (isset($_REQUEST["token"])) {


        //ricevo selectedInfo che contiene i dati dei device che ho selezionato dal front end
        //Esempio [String uri,bool retryChecked,bool deleteChecked, bool isinDB, bool isinKB, bool isinOwn, bool haveUri]
        //retryChecked e deleteChecked indicano il tipo di operazione e sono auto esclusivi
        if (isset($_REQUEST["selectedInfo"])) {
            $selectedInfo = $_REQUEST["selectedInfo"];
            $selectedInfo = json_decode($selectedInfo, true);

            //ciclo su tutti i device che ho selezionato
            for ($i = 0; $i < count($selectedInfo); $i++) {
                //caso RETRY
                if ($selectedInfo[$i]["retryChecked"] == true && $selectedInfo[$i]["deleteChecked"] == false) {

                    //$apiResult["opResult"].='edo';

                    $resultArray = [$selectedInfo[$i]["uri"], $selectedInfo[$i]["isInDb"], $selectedInfo[$i]["isInKb"], $selectedInfo[$i]["isInOwn"], $selectedInfo[$i]["haveUri_db"], $selectedInfo[$i]["isInBroker"],$selectedInfo[$i]['haveUri_own'], ""];

                    AllAroundRetry($selectedInfo[$i], $link, $link_own, $accessToken, $kbUrl, $apiResult, $resultArray);


                    $resultArray[7] = $apiResult["actionTaken"];
                    $apiResult["opResult"][$i] = $resultArray;



                    $dbConnection = $link;

                    //aggiorno la entry in devicecheck per salvare i log ed eventuali aggiornamenti sui device
                    //che ho processato, in modo tale che se recupero la run di check ho i dispositivi aggiornati e
                    //con il log se ho già processato qualcosa
                    try {
                        if ($dbConnection->connect_error) {
                            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                        }

                        $broker = explode('/', $selectedInfo[$i]["uri"])[0];
                        $broker = str_replace("...", "", $broker);
                        $organization = explode('/', $selectedInfo[$i]["uri"])[1];
                        $deviceId = explode('/', $selectedInfo[$i]["uri"])[2];
                        $uri = "http://www.disit.org/km4city/resource/iot/" . $broker . "/" . $organization . "/" . $deviceId;


                        $stmt = $dbConnection->prepare("UPDATE iotdb.devicecheck SET log=?,isInDB=?,isInKB=?,isInOwnership=?, haveURI=?,haveURI_own=?,isInBroker=? WHERE uri=?");
                        $inDb = (int)$apiResult["opResult"][$i][1];
                        $inKb = (int)$apiResult["opResult"][$i][2];
                        $inOwn = (int)$apiResult["opResult"][$i][3];
                        $haveUri_db = (int)$apiResult["opResult"][$i][4];
                        $inBrk = (int)$apiResult["opResult"][$i][5];
                        $haveUri_own=(int)$apiResult["opResult"][$i][6];

                        //var_dump($apiResult["opResult"][$i][6]);
                        if($apiResult["opResult"][$i][7]==""){
                            $apiResult["opResult"][$i][7]=$apiResult["errors"];
                            $log=$apiResult["errors"];
                        }else{
                            $log=$apiResult["opResult"][$i][7];
                        }

                        $apiResult["errors"] = "";
                        $apiResult["actionTaken"] = "";

                        $stmt->bind_param("ssssssss", $log, $inDb, $inKb, $inOwn, $haveUri_db,$haveUri_own, $inBrk, $uri);
                        $stmt->execute();
                        $nrows = $stmt->affected_rows;



                        if (isset($log_path_device_check)) {
                            $log_path = $log_path_device_check . "WIPResultdump.txt"; // Use dot (.) for string concatenation
                        } else {
                            $log_path = "/tmp/WIPResultdump.txt";
                        }
                        $timestamp = date("Y-m-d H:i:s");
                        $logEntry = "[$timestamp] - Action: RETRY - DeviceURI: $uri – Status: $log\n";
                        $logfile = fopen($log_path, "a") or die("Unable to open file!");
                        fwrite($logfile, $logEntry);
                        fclose($logfile);



                    } catch (Exception $e) {
                        echo "cannot save result in db";
                    }
                }elseif ($selectedInfo[$i]["retryChecked"] == false && $selectedInfo[$i]["deleteChecked"] == true) {

                    //$apiResult["opResult"].='edo';

                    $resultArray = [$selectedInfo[$i]["uri"], $selectedInfo[$i]["isInDb"], $selectedInfo[$i]["isInKb"], $selectedInfo[$i]["isInOwn"], $selectedInfo[$i]["haveUri_db"], $selectedInfo[$i]["isInBroker"],$selectedInfo[$i]['haveUri_own'], ""];

                    AllAroundDelete($selectedInfo[$i], $link,$link_own, $accessToken, $kbUrl, $apiResult, $resultArray);


                    $resultArray[7] = $apiResult["actionTaken"];
                    $apiResult["opResult"][$i] = $resultArray;



                    $dbConnection = $link;

                    //aggiorno la entry in devicecheck per salvare i log ed eventuali aggiornamenti sui device
                    //che ho processato, in modo tale che se recupero la run di check ho i dispositivi aggiornati e
                    //con il log se ho già processato qualcosa
                    try {
                        if ($dbConnection->connect_error) {
                            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                        }

                        $broker = explode('/', $selectedInfo[$i]["uri"])[0];
                        $broker = str_replace("...", "", $broker);
                        $organization = explode('/', $selectedInfo[$i]["uri"])[1];
                        $deviceId = explode('/', $selectedInfo[$i]["uri"])[2];
                        $uri = "http://www.disit.org/km4city/resource/iot/" . $broker . "/" . $organization . "/" . $deviceId;


                        $stmt = $dbConnection->prepare("UPDATE iotdb.devicecheck SET log=?,isInDB=?,isInKB=?,isInOwnership=?, haveURI=?,haveURI_own=?,isInBroker=? WHERE uri=?");
                        $inDb = (int)$apiResult["opResult"][$i][1];
                        $inKb = (int)$apiResult["opResult"][$i][2];
                        $inOwn = (int)$apiResult["opResult"][$i][3];
                        $haveUri_db = (int)$apiResult["opResult"][$i][4];
                        $inBrk = (int)$apiResult["opResult"][$i][5];
                        $haveUri_own=(int)$apiResult["opResult"][$i][6];


                        //var_dump($apiResult["opResult"][$i][6]);
                        if($apiResult["opResult"][$i][7]==""){
                            $apiResult["opResult"][$i][7]=$apiResult["errors"];
                            $log=$apiResult["errors"];
                        }else{
                            $log=$apiResult["opResult"][$i][7];
                        }

                        $apiResult["errors"] = "";
                        $apiResult["actionTaken"] = "";

                        $stmt->bind_param("ssssssss", $log, $inDb, $inKb, $inOwn, $haveUri_db,$haveUri_own, $inBrk, $uri);
                        $stmt->execute();
                        $nrows = $stmt->affected_rows;

                        if (isset($log_path_device_check)) {
                            $log_path = $log_path_device_check . "WIPResultdump.txt";
                        } else {
                            $log_path = "/tmp/WIPResultdump.txt";
                        }
                        $timestamp = date("Y-m-d H:i:s");
                        $logEntry = "[$timestamp] - Action: DELETE - DeviceURI: $uri – Status: $log\n";
                        $logfile = fopen($log_path, "a") or die("Unable to open file!");
                        fwrite($logfile, $logEntry);
                        fclose($logfile);


                    } catch (Exception $e) {
                        echo "cannot save result in db";
                    }


                }else{
                    //ERRORE NON SELEZIONATO NE DELETE NE RETRY
                    $apiResult["status"] = 'ko';
                    $apiResult["log"] = "retry nor delete selected";
                    echo json_encode($apiResult);
                }
            }
            echo json_encode($apiResult);
        } else {
            $apiResult["status"] = 'ko';
            $apiResult["log"] = "access token not present";
            echo json_encode($apiResult);
        }
    }
}



function AllAroundRetry($selectedDevice,$link,$link_own,$accessToken,$kbUrl,&$apiResult,&$resultArray){


    $dbConnection = $link;
    $dbConnection_own = $link_own;

    if ($dbConnection->connect_error) {
        throw new Exception("Database connection failed: " . $dbConnection->connect_error);
    }

    $apiResult['actionTaken']='';
    $apiResult['errors']='';
    $apiResult['log']='';
    //se è nel db
    if($selectedDevice["isInDb"] == true){
        //E' NEL DB PROSEGUO, QUESTO MI DICE SE Cè LA ENTRY

        $broker = explode('/', $selectedDevice["uri"])[0];
        $broker = str_replace("...", "", $broker);

        $organization = explode('/', $selectedDevice["uri"])[1];
        $deviceId = explode('/', $selectedDevice["uri"])[2];

        $uri = "http://www.disit.org/km4city/resource/iot/" . $broker . "/" . $organization . "/" . $deviceId;

        //CHECK SE HA URI
        if($selectedDevice["haveUri_db"]){

            $apiResult["log"].= 'db is ok, ';

            if($selectedDevice["isInOwn"]){
                $apiResult["log"].= 'ownership is ok, ';
                //QUA SE CI ARRIVO SONO NEL CASO IN CUI URI E OWNERSHIP SONO OK (VEDERE IL CASO IN CUI NELLA OWN MANCA SOLO L'URI)
                //CASO A SUL MIO FOGLIO
            }else{
                //CASO B: DEVO RECUPERARE DA DEVICE ED INSERIRE NELLA OWNERSHIP TUTTA LA ENTRY
                try{
                    $apiResult["log"].='missing in ownership, ';

                    //CHECK SE NELLA OWN MANCA SOLO L'URI O TUTTA LA ENTRY
                    global $ownershipdburl;
                    $elementId=$organization.":".$broker.":".$deviceId;
                    $stmt = $dbConnection_own->prepare("SELECT elementUrl FROM $ownershipdburl 
                 WHERE elementType = 'IOTID' AND deleted IS NULL
                    AND elementId =?");
                    $stmt->bind_param("s", $elementId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                }catch(Exception $e){
                    $apiResult['errors'].='error recovering uri from own, ';
                    $apiResult['log'].='error inserting uri from own, ';
                    $apiResult['status']='error';
                }

                    if ($result->num_rows === 0) {
                        $apiResult["log"] .= 'all entry missing in ownership, ';

                        //caso dove manca tutta la entry, la reinserisco tutta da devices

                        //Sistemo le variabili necessarie per l'inserimento intero nella ownership
                        try{
                            global $ownershipdburl;
                            global $userforOwnership;
                            $elementType = 'IOTID';
                            $currentDate = date("Y-m-d H:i:s");

                            if($userforOwnership=="") {
                            $username = $_SESSION[loggedUsername];
                            }else{
                                $username=$userforOwnership;
                            }
                            $k1 = $_REQUEST["k1"];
                            $k2 = $_REQUEST["k2"];
                            $elementDetails = array(
                                "k1" => $k1,
                                "k2" => $k2,
                                "contextbroker" => $broker
                            );

                            $query = "INSERT INTO $ownershipdburl (username, elementId, elementType, elementName, elementUrl,elementDetails,created) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $dbConnection_own->prepare($query);
                            $stmt->bind_param("sssssss", $username, $elementId, $elementType, $deviceId, $uri, $elementDetails, $currentDate);
                            $stmt->execute();
                            //ARRIVATO QUA LA OWNERSHIP E DB SONO OK
                            $apiResult['actionTaken'] .= 'recovered ownership entry, ';
                            $resultArray[3]=true;
                            $resultArray[6]=true;
                        }catch(Exception $e){
                            $apiResult['errors'].='error inserting entry in own, ';
                            $apiResult['log'].='error inserting uri in own, ';
                            $apiResult['status']='error';
                        }

                }else{
                    $apiResult["log"].="ownership missing only uri, ";
                    //caso dove rende un risultato
                    $row = $result->fetch_assoc();

                    //caso dove l'url manca
                    if (empty($row['elementUrl'])) {
                        try{
                            //manca solo la uri ma la entry c'è,recupero l'uri da devic
                            $stmt = $dbConnection->prepare("SELECT uri FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
                            $stmt->bind_param("sss", $deviceId, $organization,$broker);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $uri = $row['uri'];

                            //inserisco l'uri nella ownership
                            global $ownershipdburl;
                            $query = "UPDATE $ownershipdburl SET elementUrl=? WHERE elementId=?";
                            $stmt = $dbConnection_own->prepare($query);
                            $stmt->bind_param("ss", $uri, $elementId);
                            $stmt->execute();

                            //ARRIVATO QUA LA OWNERSHIP E DB SONO OK
                            $apiResult['actionTaken'].='inserted uri in ownership, ';
                            $resultArray[3]=true;
                            $resultArray[6]=true;
                        }catch(Exception $e){
                            $apiResult['errors'].='error inserting uri in own, ';
                            $apiResult['log'].='error inserting uri in own, ';
                            $apiResult['status']='error';
                        }
                    }
                }
            }
        }else{
            $apiResult["log"].='db is missing the uri, ';
            //HO LA ENTRY NEL DB MA NON HO L'URI
            if($selectedDevice["isInOwn"]){
                $apiResult["log"].='recovering uri for db from ownership, ';
                try{
                //CASO C: NON HO LA URI NEL DB MA LA ENTRY C'E', RECUPERO L'URI DALLA OWNERSHIP
                    global $ownershipdburl;
                    $elementId=$organization.":".$broker.":".$deviceId;
                    $stmt = $dbConnection_own->prepare("SELECT elementUrl FROM $ownershipdburl
                 WHERE elementType = 'IOTID' AND deleted IS NULL
                    AND elementId =?");
                    $stmt->bind_param("s", $elementId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $uri=$row["elementUrl"];

                    //Inserisco l'uri in devices
                    $query = "UPDATE iotdb.devices SET uri=? WHERE id=? AND contextBroker=? AND organization=?";
                    $stmt = $dbConnection->prepare($query);
                    $stmt->bind_param("ssss", $uri, $deviceId,$broker,$organization);
                    $stmt->execute();
                    $apiResult['actionTaken'].='inserted uri in db, ';
                    $resultArray[1]=true;
                    $resultArray[4]=true;
                //DB e OWNERSHIP QUI OK
                }catch(Exception $e){
                    $apiResult['errors'].='error inserting uri in db, ';
                    $apiResult['log'].='error inserting uri in db, ';
                    $apiResult['status']='error';
                }

            }else{
                //CASO D: URI MANCA SIA IN DB CHE IN OWNERSHIP, LO RICOSTRUISCO E LO METTO IN ENTRAMBI
                $apiResult["log"].='uri missing in both own and db, ';

                try{
                    $query = "UPDATE iotdb.devices SET uri=? WHERE id=? AND contextBroker=? AND organization=?";
                    $stmt = $dbConnection->prepare($query);
                    $stmt->bind_param("ssss", $uri, $deviceId,$broker,$organization);
                    $stmt->execute();
                    $apiResult['actionTaken'].='reinserted uri in db, ';
                    $resultArray[4]=true;
                    $resultArray[1]=true;
                    $elementId=$organization.":".$broker.":".$deviceId;
                    global $ownershipdburl;
                    //guardo nella ownership se manca tutta la entry o solo uri
                    $stmt = $dbConnection_own->prepare("SELECT elementUrl FROM $ownershipdburl 
                 WHERE elementType = 'IOTID' AND deleted IS NULL
                    AND elementId =?");
                    $stmt->bind_param("s", $elementId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                }catch(Exception $e){
                    $apiResult['errors'].='error inserting reconstructed uri in db, ';
                    $apiResult['log'].='error inserting reconstructed uri in db,  ';
                    $apiResult['status']='error';
                }

                if ($result->num_rows === 0) {
                    try{
                        $apiResult["log"].='full entry missing in ownership, ';
                        //caso dove manca tutta la entry, la reinserisco tutta da devices

                        //Sistemo le variabili necessarie per l'inserimento intero nella ownership
                        global $ownershipdburl;
                        global $userforOwnership;
                        $elementType='IOTID';
                        $currentDate = date("Y-m-d H:i:s");

                        if($userforOwnership=="") {
                        $username=$_SESSION[loggedUsername];
                        }else{
                            $username=$userforOwnership;
                        }
                        $k1=$_REQUEST["k1"];
                        $k2=$_REQUEST["k2"];
                        $elementDetails = array(
                            "k1" => $k1,
                            "k2" => $k2,
                            "contextbroker" => $broker
                        );

                        $query = "INSERT INTO $ownershipdburl (username, elementId, elementType, elementName, elementUrl,elementDetails,created) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $dbConnection_own->prepare($query);
                        $stmt->bind_param("sssssss", $username, $elementId, $elementType, $deviceId, $uri, $elementDetails, $currentDate);
                        $stmt->execute();
                        //ARRIVATO QUA LA OWNERSHIP E DB SONO OK
                        $apiResult['actionTaken'].='reinserted entry in ownership, ';
                        $resultArray[3]=true;
                        $resultArray[6]=true;
                    }catch(Exception $e){
                        $apiResult['errors'].='error inserting entry in own, ';
                        $apiResult['log'].='error inserting entry in own,  ';
                        $apiResult['status']='error';
                    }
                }
                else{
                    //caso dove rende un risultato
                    $apiResult["log"].='uri missing in ownership, ';
                    $row = $result->fetch_assoc();

                    //caso dove l'url manca
                    if (is_null($row['elementUrl']) || $row['elementUrl'] === '') {
                        try{
                            //manca solo la uri ma la entry c'è,recupero l'uri da devic
                            $stmt = $dbConnection->prepare("SELECT uri FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
                            $stmt->bind_param("sss", $deviceId, $organization,$broker);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $uri = $row['uri'];

                            //inserisco l'uri nella ownership
                            global $ownershipdburl;
                            $query = "UPDATE $ownershipdburl SET elementUrl=? WHERE elementId=?";
                            $stmt = $dbConnection_own->prepare($query);
                            $stmt->bind_param("ss", $uri, $elementId);
                            $stmt->execute();
                            $apiResult['actionTaken'].='reinserted uri in ownership, ';
                            $resultArray[3]=true;
                            $resultArray[6]=true;
                        }catch(Exception $e){
                            $apiResult['errors'].='error inserting uri in own, ';
                            $apiResult['log'].='error inserting uri in own, ';
                            $apiResult['status']='error';
                        }
                    }
                }
            }
        }


        //A QUESTO PUNTO SE SOPRA è ANDATO TUTTO BENE HO SICURAMENTE LA ENTRY NEL DB E NELLA OWNERSHIP FATTE BENE

        if($selectedDevice["isInKb"]==true){
            $apiResult["log"].='kb is ok, ';

            if($selectedDevice["isInBroker"]){
                $apiResult["log"].='broker is ok, ';
                //CASO E: TUTTO APPOSTO C'E' SIA NELLA KB CHE NEL BROKER
            }else{
                $apiResult["log"].='entry missing in broker, ';
                try{
                    $stmt = $dbConnection->prepare("SELECT * FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
                    $stmt->bind_param("sss", $deviceId, $organization,$broker);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $type=$row['devicetype'];
                    $kind=$row['kind'];
                    $protocol=$row['protocol'];
                    $format=$row['format'];
                    $model=$row['model'];
                    $latitude=$row['latitude'];
                    $longitude=$row['longitude'];
                    $visibility=$row['visibility'];
                    $frequency=$row['$frequency'];
                }catch(Exception $e){
                    $apiResult['errors'].='error recovering data from db, ';
                    $apiResult['log'].='error recovering data from db, ';
                    $apiResult['status']='error';
                }

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $evnt_values_check=checkEventValues($dbConnection,$broker,$deviceId,$uri);
                if($evnt_values_check[1]){
                    $apiResult["log"].='event_values ok, ';
                    $listnewAttributes = $evnt_values_check[0];

                    $brokerAddress = recoverBrokerAddress($dbConnection,$broker,$organization);
                    if($brokerAddress[2]) {
                        $ip = $brokerAddress[0];
                        $port = $brokerAddress[1];


                        $dbConnectionBrok = $link;
                        if ($dbConnection->connect_error) {
                            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                        }
                        $dbConnectionBrok->select_db('iotdb');

                        $resultBrok=[];
                        $brokerResp = insert_ngsi($dbConnectionBrok, $deviceId, $type, $broker, $kind, $protocol, $format, $model, $latitude, $longitude,
                            $visibility, $frequency, $listnewAttributes, $ip, $port, $resultBrok);
                        if($brokerResp=='ok'){
                            $apiResult['actionTaken'].='inserted in broker, ';
                            $resultArray[5]=true;
                        }else{
                            $apiResult['errors'].='error in insert_ngsi() ';
                            $apiResult['log'].='error in insert_ngsi() ';
                            $apiResult['status']='error';
                        }
                    }else{

                        $apiResult['errors'].='error recovering ip and port of the broker, ';
                        $apiResult['log'].='error recovering ip and port of the broker, ';
                        $apiResult['status']='error';
                        //impossibile recuperare ip e port
                    }
                 }else{
                    $apiResult['errors'].='error recovering event_values, ';
                    $apiResult['log'].='error recovering event_values,  ';
                    $apiResult['status']='error';

                    //impossibile recuperare eventvalues
                }
            }
        }else{
            $apiResult["log"].='entry missing from the kb, ';

            $evnt_values_check=checkEventValues($dbConnection,$broker,$deviceId,$uri);
            if($evnt_values_check[1]){
                try {
                    $apiResult["log"] .= 'event_values recovered, ';
                    $listnewAttributes = $evnt_values_check[0];

                    $stmt = $dbConnection->prepare("SELECT * FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
                    $stmt->bind_param("sss", $deviceId, $organization, $broker);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $type = $row['devicetype'];
                    $kind = $row['kind'];
                    $protocol = $row['protocol'];
                    $format = $row['format'];
                    $model = $row['model'];
                    $latitude = $row['latitude'];
                    $longitude = $row['longitude'];
                    $visibility = $row['visibility'];
                    $frequency = $row['frequency'];
                    $macaddress = $row['macaddress'];
                    $producer = $row['producer'];
                    $subnature = $row['subnature'];
                    $staticAttributes = $row['static_attributes'];
                    $service = $row['service'];
                    $servicePath = $row['servicePath'];
                    $wktGeometry = $row['wktGeometry'];
                    $hlt = $row['hlt'];
                }catch (Exception $e){
                    $apiResult['errors'].='error recovering data to insert in the kb, ';
                    $apiResult['log'].='error recovering data to insert in the kb,  ';
                    $apiResult['status']='error';
                }

                $dbConnectionKB = $link;
                if ($dbConnection->connect_error) {
                    throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                }
                $dbConnectionKB->select_db('iotdb');

                registerKB($dbConnectionKB, $deviceId, $type, $broker, $kind, $protocol,
                    $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility,
                    $frequency, $listnewAttributes, $subnature, $staticAttributes, $KBresult, 'yes', $organization, $kbUrl, $service, $servicePath, $accessToken,$wktGeometry,$hlt);

                if($KBresult['status']=='ok') {
                    $apiResult['actionTaken'] .= 'inserted in KB, ';
                    $resultArray[2]=true;
                }else{
                    $apiResult['errors'].='error in registerKB, ';
                    $apiResult['log'].='error in registerKB, {'.$KBresult['log'].'}';
                    $apiResult['status']='error';
                }

                if($selectedDevice["isInBroker"]){
                    $apiResult["log"].='entry in broker ok, ';

                    }else{

                            $apiResult["log"] .= 'entry missing in broker, ';
                            $brokerAddress = recoverBrokerAddress($dbConnection, $broker, $organization);
                            if($brokerAddress[2]) {
                                $ip = $brokerAddress[0];
                                $port = $brokerAddress[1];

                                $dbConnectionBrok = $link;
                                if ($dbConnection->connect_error) {
                                    throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                                }
                                $dbConnectionBrok->select_db('iotdb');



                                $resultBrok=[];
                                $brokerResp = insert_ngsi($dbConnectionBrok, $deviceId, $type, $broker, $kind, $protocol, $format, $model, $latitude, $longitude,
                                    $visibility, $frequency, $listnewAttributes, $ip, $port, $resultBrok);
                                if ($brokerResp == 'ok') {
                                    $apiResult['actionTaken'] .= 'inserted in broker, ';
                                    $resultArray[5]=true;
                                } else {
                                    $apiResult['errors'] .= 'error in insert_ngsi() ';
                                    $apiResult['status'] = 'error';
                                }
                            }else{
                                $apiResult['errors'] .= 'error recovering broker ip and port, ';
                                $apiResult['status'] = 'error';
                            }
                    }

            }else{
                $apiResult["log"].='missing entry in event_values, ';
                $apiResult['errors'].='error recovering_event_values ';
                $apiResult['status']='error';
                //NON RECUPERABILE NON C'E' in event_values
            }
            //CASO G:DEVO CONTROLLARE SIA IN EVENT VALUES POI REINSERIRE NELLA KB
            //ELSE DI EVENT VALUES, NON SI PUò REINSERIRE

        }

    }else{
        $apiResult["log"].='missing entry in db, ';
        $apiResult['errors'].='error missing entry in db, ';
        $apiResult['status']='error';
        //MANCA NEL DB NON RECUPERABILE
    }

}

function AllAroundDelete($selectedDevice,$link,$link_own,$accessToken,$kbUrl,&$apiResult,&$resultArray){
    $dbConnection = $link;
    $dbConnection_own = $link_own;
    if ($dbConnection->connect_error) {
        throw new Exception("Database connection failed: " . $dbConnection->connect_error);
    }

    $apiResult['actionTaken']='';
    $apiResult['errors']='';
    $apiResult['log']='';
    $deletable=true;

    $broker = explode('/', $selectedDevice["uri"])[0];
    $broker = str_replace("...", "", $broker);

    $organization = explode('/', $selectedDevice["uri"])[1];
    $deviceId = explode('/', $selectedDevice["uri"])[2];

    $uri = "http://www.disit.org/km4city/resource/iot/" . $broker . "/" . $organization . "/" . $deviceId;

    if($selectedDevice["isInKb"]){
        if($selectedDevice["isInOwn"]){
            //Caso dove è presente sia in ownership sia nella KB,posso eliminarlo direttamente dalla KB

            try {

                $dbConnectionKB = $link;
                if ($dbConnection->connect_error) {
                    throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                }
                $dbConnectionKB->select_db('iotdb');
                $resultKB=[];

                deleteKB($link, $deviceId, $broker, $kbUrl, $resultKB, $service = "", $servicePath = "", $accessToken);

                if($resultKB['status']=='ok'){
                    $apiResult['actionTaken'].='deleted from the KB';
                    $apiResult['log'].='deleted from the KB, ';
                    $resultArray[2]=false;
                }else{
                    $apiResult['log'].='Error deleting from kb, ';
                    $apiResult['errors'].='error deleting from the kb,{ '.$resultKB['log']."}";
                }


            }catch(Exception $e){
                $apiResult['log'].='Error deleting from kb, ';
                $apiResult['errors'].='error deleting from the kb, ';
                $apiResult['status']='error';
            }



        }else{
            //se sono qua il device non è presente nella ownership, controllo se è in DB
            if($selectedDevice["isInDb"]){
                if($selectedDevice["haveUri_db"]){
                    //se è presente nel db e ha l'uri posso recuperarlo e inseririrlo nella ownership
                    try{
                        global $ownershipdburl;
                        //Controllo la ownership per vedere se manca tutta la entry o manca solo l'URI
                        $elementId=$organization.":".$broker.":".$deviceId;
                        $stmt = $dbConnection_own->prepare("SELECT elementUrl FROM $ownershipdburl 
                 WHERE elementType = 'IOTID' AND deleted IS NULL
                    AND elementId =?");
                        $stmt->bind_param("s", $elementId);
                        $stmt->execute();
                        $result = $stmt->get_result();

                    }catch(Exception $e){
                        $apiResult['errors'].='error checking uri in own(delete), ';
                        $apiResult['log'].='error checking uri in own(delete), ';
                        $apiResult['status']='error';
                    }

                    //Se la query mi ritorna 0 nella ownership manca tutta la entry, quindi la reinserisco tutta dai dati che ho nel DB
                    if ($result->num_rows === 0) {


                        try{
                            global $ownershipdburl;
                            global $userforOwnership;
                            $elementType = 'IOTID';
                            $currentDate = date("Y-m-d H:i:s");

                            if($userforOwnership=="") {
                            $username = $_SESSION[loggedUsername];
                            }else{
                                $username=$userforOwnership;
                            }
                            $k1 = $_REQUEST["k1"];
                            $k2 = $_REQUEST["k2"];
                            $elementDetails = array(
                                "k1" => $k1,
                                "k2" => $k2,
                                "contextbroker" => $broker
                            );

                            $query = "INSERT INTO $ownershipdburl (username, elementId, elementType, elementName, elementUrl,elementDetails,created) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $dbConnection_own->prepare($query);
                            $stmt->bind_param("sssssss", $username, $elementId, $elementType, $deviceId, $uri, $elementDetails, $currentDate);
                            $stmt->execute();

                            //A questo punto Ownership è ok
                            $apiResult['actionTaken'] .= 'recovered ownership entry(delete), ';
                            $resultArray[3]=true;
                            $resultArray[6]=true;

                        }catch(Exception $e){
                            $apiResult['errors'].='error inserting entry in own(delete), ';
                            $apiResult['log'].='error inserting uri in own(delete), ';
                            $apiResult['status']='error';
                        }

                    }else{
                        //caso deve la ownership mi rende un risultato, controllo che ElementUrl(URI) sia vuoto ed eventualmente inserisco solo quello
                        $apiResult["log"].="ownership missing only uri, ";
                        //caso dove rende un risultato
                        $row = $result->fetch_assoc();

                        if (empty($row['elementUrl'])) {
                            try{
                                //Recupero l'uri da devices
                                $stmt = $dbConnection->prepare("SELECT uri FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
                                $stmt->bind_param("sss", $deviceId, $organization,$broker);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();
                                $uri = $row['uri'];

                                //Inserisco l'uri nella ownership
                                global $ownershipdburl;
                                $query = "UPDATE $ownershipdburl SET elementUrl=? WHERE elementId=?";
                                $stmt = $dbConnection_own->prepare($query);
                                $stmt->bind_param("ss", $uri, $elementId);
                                $stmt->execute();

                                //A questo punto Ownership è ok
                                $apiResult['actionTaken'].='inserted uri in ownership(delete), ';
                                $resultArray[3]=true;
                                $resultArray[6]=true;
                            }catch(Exception $e){
                                $apiResult['errors'].='error inserting uri in own(delete), ';
                                $apiResult['log'].='error inserting uri in own(delete), ';
                                $apiResult['status']='error';
                            }
                        }
                    }



                }else {
                    //caso dove il dispositivo non è presente nella ownership e non ha l'uri in devices

                    //genero l'uri e lo reinserisco in devices
                    try {
                        $query = "UPDATE iotdb.devices SET uri=? WHERE id=? AND contextBroker=? AND organization=?";
                        $stmt = $dbConnection->prepare($query);
                        $stmt->bind_param("ssss", $uri, $deviceId, $broker, $organization);
                        $stmt->execute();
                        $apiResult['actionTaken'] .= 'reinserted uri in db, ';
                        $resultArray[4] = true;
                        $resultArray[1] = true;
                        $elementId = $organization . ":" . $broker . ":" . $deviceId;


                        global $ownershipdburl;
                        $stmt = $dbConnection_own->prepare("SELECT elementUrl FROM $ownershipdburl
                 WHERE elementType = 'IOTID' AND deleted IS NULL
                    AND elementId =?");
                        $stmt->bind_param("s", $elementId);
                        $stmt->execute();
                        $result = $stmt->get_result();

                    }catch (Exception $e){
                        $apiResult['errors'].='error checking entry in own(delete), ';
                        $apiResult['log'].='error checking entry in own(delete), ';
                        $apiResult['status']='error';
                    }

                    if ($result->num_rows === 0) {



                        try {
                            global $ownershipdburl;
                            global $userforOwnership;
                            $elementType = 'IOTID';
                            $currentDate = date("Y-m-d H:i:s");

                            if($userforOwnership=="") {
                            $username = $_SESSION[loggedUsername];
                            }else{
                                $username=$userforOwnership;
                            }
                            $k1 = $_REQUEST["k1"];
                            $k2 = $_REQUEST["k2"];
                            $elementDetails = array(
                                "k1" => $k1,
                                "k2" => $k2,
                                "contextbroker" => $broker
                            );

                            $query = "INSERT INTO $ownershipdburl (username, elementId, elementType, elementName, elementUrl,elementDetails,created) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $dbConnection_own->prepare($query);
                            $stmt->bind_param("sssssss", $username, $elementId, $elementType, $deviceId, $uri, $elementDetails, $currentDate);
                            $stmt->execute();

                            //A questo punto Ownership è ok
                            $apiResult['actionTaken'] .= 'recovered ownership entry(delete), ';
                            $resultArray[3] = true;
                            $resultArray[6]=true;

                        } catch (Exception $e) {
                            $apiResult['errors'] .= 'error inserting entry in own(delete), ';
                            $apiResult['log'] .= 'error inserting uri in own(delete), ';
                            $apiResult['status'] = 'error';
                        }
                    } else {
                        //caso dove rende un risultato
                        $row = $result->fetch_assoc();

                        if (empty($row['elementUrl'])) {
                            try {
                                //Recupero l'uri da devices
                                $stmt = $dbConnection->prepare("SELECT uri FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
                                $stmt->bind_param("sss", $deviceId, $organization, $broker);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();
                                $uri = $row['uri'];

                                //Inserisco l'uri nella ownership
                                global $ownershipdburl;
                                $query = "UPDATE $ownershipdburl SET elementUrl=? WHERE elementId=?";
                                $stmt = $dbConnection_own->prepare($query);
                                $stmt->bind_param("ss", $uri, $elementId);
                                $stmt->execute();

                                //A questo punto Ownership è ok
                                $apiResult['actionTaken'] .= 'inserted uri in ownership(delete), ';
                                $resultArray[3] = true;
                                $resultArray[6]=true;
                            } catch (Exception $e) {
                                $apiResult['errors'] .= 'error inserting uri in own (delete), ';
                                $apiResult['log'] .= 'error inserting uri in own (delete), ';
                                $apiResult['status'] = 'error';
                            }
                        }
                    }
                }
            }else{
                //Caso in cui manca sia dalla ownership che dal database, impossibile cancellarlo dalla KB
                $deletable=false;
                $apiResult['errors'] .= 'entry missing from db and own impossible to delete from the kb, ';
                $apiResult['log'] .= 'entry missing from db and own impossible to delete from the kb,  ';
                $apiResult['status'] = 'error';

            }

            //Arrivato qua, dovrei aver ristabilito la ownership, quindi posso cancellare dalla KB
            if($deletable) {

                $dbConnectionKB = $link;
                if ($dbConnection->connect_error) {
                    throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                }
                $dbConnectionKB->select_db('iotdb');
                $resultKB = [];

                deleteKB($link, $deviceId, $broker, $kbUrl, $resultKB, $service = "", $servicePath = "", $accessToken);
                if($resultKB['status']=='ok'){
                    $apiResult['actionTaken'].='deleted from the KB, ';
                    $apiResult['log'].='deleted from the KB, ';
                    $resultArray[2]=false;
                }else{
                    $apiResult['log'].='Error deleting from kb, ';
                    $apiResult['errors'].='error deleting from the kb,{ '.$resultKB['log']."}";
                }
            }else{
                $apiResult['log'] .= 'cannot delete from KB, DB e Own entry are missing, ';
                $apiResult['errors'].='cannot delete from KB, DB e Own entry are missing,  ';
                $apiResult['status']='error';
            }
        }
    }else{
        //Il device non è in KB non c'è bisogno di cancellarlo
        $apiResult["log"].="no need to delete KB, ";
    }

    //////da qui in poi la kb è ok

    if($selectedDevice["isInBroker"]){
        try {
            $stmt = $dbConnection->prepare("SELECT * FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
            $stmt->bind_param("sss", $deviceId, $organization, $broker);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $type = $row['devicetype'];
            $kind = $row['kind'];
            $protocol = $row['protocol'];
            $format = $row['format'];
            $model = $row['model'];
            $latitude = $row['latitude'];
            $longitude = $row['longitude'];
            $visibility = $row['visibility'];
            $frequency = $row['frequency'];
            $service = $row['service'];
            $servicePath = $row['servicePath'];
            $brokerAddress = recoverBrokerAddress($dbConnection, $broker, $organization);
            $evnt_values_check = checkEventValues($dbConnection, $broker, $deviceId,$uri);



            if ($brokerAddress[2]) {
                $ip = $brokerAddress[0];
                $port = $brokerAddress[1];

                $listnewAttributes = $evnt_values_check[0];

                $dbConnectionBrok = $link;
                if ($dbConnection->connect_error) {
                    throw new Exception("Database connection failed: " . $dbConnection->connect_error);
                }
                $dbConnectionBrok->select_db('iotdb');
                $resultBr=[];
                delete_ngsi($deviceId, $type, $broker, $kind, $protocol, $format, $model, $latitude, $longitude,
                    $visibility, $frequency, $listnewAttributes, $ip, $port, $uri, $resultBr, $service, $servicePath);

                if($resultBr["status"]=='ok'){
                    $apiResult['actionTaken'].='deleted from broker, ';
                    $apiResult["log"] .= "deleted from broker, ";
                    $apiResult['errors'].='deleted from broker,';
                    $resultArray[5]=false;
                }else{
                    $apiResult['actionTaken'].='error deleting from broker, ';
                    $apiResult["log"] .= "error deleting from broker, ";
                    $apiResult['errors'].='error deleting from broker,';
                }


            } else {
                $apiResult["log"] .= "impossible to recover broker ip and port, ";
                $apiResult['errors'].='impossible to recover broker ip and port  ';
                $apiResult['status']='error';

            }
        }catch (Exception $e){
            $apiResult["log"] .= "error recovering data needed to delete from broker, ";
            $apiResult['errors'].='error recovering data needed to delete from broker, ';
            $apiResult['status']='error';
        }
        //Di sopra settarlo come false se uso deleteKb che lo cancella anche dal broker, altrimenti poi lo cancello due volte
    }else{
        $apiResult["log"].="no need to delete Broker, ";
    }


    if($selectedDevice["isInDb"]){
        try{
            $query = "DELETE FROM iotdb.devices WHERE id=? AND contextBroker=? AND organization=?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("sss", $deviceId,$broker,$organization);
            $stmt->execute();
        }catch (Exception $e){
            $apiResult["log"].="error deleting from DB";
            $apiResult['errors'].='error deleting from DB ';
            $apiResult['status']='error';
        }
        $apiResult['actionTaken'].='deleted from DB, ';
        $apiResult["log"].="deleted from DB";
        $resultArray[1]=false;
        $resultArray[4]=false;


    }else{
        $apiResult["log"].="no need to delete DB, ";
    }

    if($selectedDevice["isInOwn"]){

        try{
            global $ownershipdburl;
            $deleted = date("Y-m-d H:i:s");
            $deletedBy = "WIPCheck-" . $_SESSION['loggedUsername'];
            $elementId=$organization.":".$broker.":".$deviceId;
            $query = " UPDATE $ownershipdburl SET deleted=?,deletedBy=? WHERE elementId=?";
            $stmt = $dbConnection_own->prepare($query);
            $stmt->bind_param("sss", $deleted,$deletedBy,$elementId);
            $stmt->execute();
        }catch (Exception $e){
            $apiResult["log"].="error deleting from from Ownership";
            $apiResult['errors'].='error deleting from ownership ';
            $apiResult['status']='error';
        }
        $apiResult['actionTaken'].='deleted from ownership, ';
        $apiResult["log"].="deleted from Ownership, ";
        $resultArray[3]=false;
        $resultArray[6]=false;

    }else{
        $apiResult["log"].="no need to delete Ownership, ";
    }


    deleteEventValues($dbConnection,$broker,$deviceId,$apiResult);

    //fine posso ritornare i risultati

}

function checkEventValues($dbConnection,$contextbroker,$deviceId,$uri){

    try {
        $query = "SELECT id FROM iotdb.devices WHERE uri = ? ";

        $stmt = $dbConnection->prepare($query);

        $stmt->bind_param("s", $uri);

        $stmt->execute();
        $result = $stmt->get_result();


        if ($row = $result->fetch_assoc()) {
            $deviceId = $row['id'];
        }


        $query = "SELECT * FROM iotdb.event_values WHERE cb = ? AND device = ?";

        $stmt = $dbConnection->prepare($query);

        $stmt->bind_param("ss", $contextbroker, $deviceId);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $att = new stdClass();
                $att->value_name = $row['value_name'];
                $att->data_type = $row['data_type'];
                $att->value_type = $row['value_type'];
                $att->editable = $row['editable'];
                $att->value_unit = $row['value_unit'];
                $att->healthiness_criteria = $row['healthiness_criteria'];
                $att->real_time_flag = $row['real_time_flag'];

                switch ($row['healthiness_criteria']) {
                    case 'refresh_rate':
                        $att->healthiness_value = $row['value_refresh_rate'];
                        break;
                    case 'different_values':
                        $att->healthiness_value = $row['different_values'];
                        break;
                    default:
                        $att->healthiness_value = $row['value_bounds'];
                        break;
                }

                $listnewAttributes[] = $att;
            }
            return $evnt_val_res = [$listnewAttributes, true];
        } else {
            return $evnt_val_res = ['', false];
        }
    }catch(Exception $e){
        return $evnt_val_res = ['', false];
    }

}

function deleteEventValues($dbConnection,$contextbroker,$deviceId,&$apiResult){
    try {
        $query = "DELETE FROM iotdb.event_values WHERE cb = ? AND device = ?";

        $stmt = $dbConnection->prepare($query);

        $stmt->bind_param("ss", $contextbroker, $deviceId);

        $stmt->execute();

        $affectedRows = $stmt->affected_rows;
        if($affectedRows>0) {
            return true;
        }
        $apiResult["log"].="deleted ".$affectedRows." from event_values";

    }catch(Exception $e){
        $apiResult["log"].="error deleting from event_values";
    }

}

function recoverBrokerAddress($dbConnection,$contextbroker,$organization)
{
    try {
        $stmt = $dbConnection->prepare("SELECT port,ip from iotdb.contextbroker WHERE name = '$contextbroker' AND organization='$organization'");
        $stmt->bind_param("ss", $contextbroker, $organization);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $ip = $row["ip"];
        $port = $row["port"];

        return $brokerAddress = [$ip, $port, true];
    }catch(Exception $e){
        return $brokerAddress = ['', '', false];
    }
}


