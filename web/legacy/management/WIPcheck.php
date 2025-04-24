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
    private $organization;

    /**
     * Constructor initializes connections and configurations
     *
     * @param string $kbHostIp The host IP/URL for the knowledge base
     * @param mysqli $dbConnection Active MySQL connection
     * @param string $organization Organization identifier for filtering database results
     */
    public function __construct(string $kbHostIp, mysqli $dbConnection, string $organization) {
        $this->kbHostIp = $kbHostIp;
        $this->link = $dbConnection;
        $this->organization = $organization;
        $this->link->set_charset("utf8mb4");
    }

    /**
     * Fetches ownership URIs from the profiledb database for the specific organization
     *
     * @return array List of URIs from ownership records for the current organization
     * @throws Exception If there's an error with the database query
     */
    public function fetchOwnershipData(): array {
        // Query to get non-deleted IOT device URIs from ownership table
        // Using REGEXP for exact organization match at the beginning of elementId
        $query = "SELECT elementUrl FROM profiledb.ownership 
             WHERE elementType = 'IOTID' AND deleted IS NULL
             AND elementId REGEXP ?";

        // Create pattern that matches exact organization name at start followed by colon
        $organizationPattern = "^" . $this->organization . ":";

        try {
            $stmt = $this->link->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare ownership query: " . $this->link->error);
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
            $ownershipData = $result->fetch_all(MYSQLI_ASSOC);
            $ownershipUris = array_column($ownershipData, 'elementUrl');

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
        OFFSET " . ($offset) . "
        LIMIT " . $this->batchSize;
        //return "SELECT DISTINCT ?s WHERE { ?s ?p ?o } LIMIT {$this->batchSize} OFFSET {$offset}";
    }

//    public function fetchReconstructableUris(): array {
//
//        //magari fare la stessa query per recuperare quelli con gli uri mancanti dalla ownwership
//        //
//        // Query to get entries without URIs but with enough information to reconstruct them
//        $query = "SELECT contextBroker, organization, id FROM iotdb.devices
//                 WHERE uri IS NULL AND organization = ?
//                 AND contextBroker IS NOT NULL AND id IS NOT NULL";
//
//        try {
//            $stmt = $this->link->prepare($query);
//            if (!$stmt) {
//                throw new Exception("Failed to prepare reconstructable query: " . $this->link->error);
//            }
//
//            // Bind the organization parameter
//            $stmt->bind_param("s", $this->organization);
//
//            if (!$stmt->execute()) {
//                throw new Exception("Failed to execute reconstructable query: " . $stmt->error);
//            }
//
//            $result = $stmt->get_result();
//            if (!$result) {
//                throw new Exception("Failed to get reconstructable results: " . $stmt->error);
//            }
//
//            // Fetch all results
//            $reconstructableRecords = $result->fetch_all(MYSQLI_ASSOC);
//
//            // Reconstruct URIs using the pattern: contextBroker/organization/id
//            $reconstructedUris = [];
//            foreach ($reconstructableRecords as $record) {
//                if (isset($record['contextBroker'], $record['organization'], $record['id'])) {
//                    $reconstructedUris[] = sprintf("%s/%s/%s/%s",
//                        "http://www.disit.org/km4city/resource/iot",
//                        $record['contextBroker'],
//                        $record['organization'],
//                        $record['id']
//                    );
//                }
//            }
        //
//            $stmt->close();
//            return array_unique($reconstructedUris);
//
//        } catch (Exception $e) {
//            error_log("Error in fetchReconstructableUris: " . $e->getMessage());
//            throw $e;
//        }
//    }

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
            $databaseUris = $this->fetchAllDBData($reconstructedUris);
//            $reconstructableUris = $this->fetchReconstructableUris();
            $knowledgebaseUris = $this->fetchAllKBData();
            $ownershipUris = $this->fetchOwnershipData();

            // Perform the comparison with all sources
            return $this->compareUriLists(
                $databaseUris,
                $knowledgebaseUris,
                $ownershipUris,
                $reconstructedUris
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
//    public function fetchDBData(int $offset): array {
//        //TODO:controllare anche l'id eventualmente ricostruirlo e metterlo in reconstructed uri
//        // Prepare the query using a prepared statement to prevent SQL injection
//        $query = "SELECT uri FROM iotdb.devices WHERE organization = ? LIMIT ? OFFSET ?";
//
//        try {
//            $stmt = $this->link->prepare($query);
//            if (!$stmt) {
//                throw new Exception("Failed to prepare database query: " . $this->link->error);
//            }
//
//            // Bind parameters safely
//            $stmt->bind_param("sii", $this->organization, $this->batchSize, $offset);
//
//
//
//            // Execute the query
//            if (!$stmt->execute()) {
//                throw new Exception("Failed to execute database query: " . $stmt->error);
//            }
//
//            // Get the results
//            $result = $stmt->get_result();
//
//            if (!$result) {
//                throw new Exception("Failed to get query results: " . $stmt->error);
//            }
//
//            // Fetch all URIs as an associative array and extract the 'uri' column
//            $uris = array_column($result->fetch_all(MYSQLI_ASSOC), 'uri');
//
//            $stmt->close();
//            return $uris;
//
//        } catch (Exception $e) {
//            error_log("Error in fetchDBData: " . $e->getMessage());
//            throw $e;
//        }
//    }
    public function fetchDBData(int $offset,&$reconstructedUris): array {
        //TODO:controllare anche l'id eventualmente ricostruirlo e metterlo in reconstructed uri
        // Prepare the query using a prepared statement to prevent SQL injection
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


//    /**
//     * Compare URIs from all sources automatically
//     *
//     * @param array $ownershipUris URIs from ownership records
//     * @param array $reconstructableUris Optional list of URIs that can be reconstructed
//     * @return array List of URIs with their presence status in each system
//     */
//    public function compareAllSources(array $ownershipUris, array $reconstructableUris = []): array {
//        try {
//            // Fetch both DB and KB data automatically
//            $databaseUris = $this->fetchAllDBData();
//            $knowledgebaseUris = $this->fetchAllKBData();
//
//            return $this->compareUriLists($databaseUris, $knowledgebaseUris, $ownershipUris, $reconstructableUris);
//        } catch (Exception $e) {
//            error_log("Error in compareAllSources: " . $e->getMessage());
//            throw $e;
//        }
//    }

//    public function handleMissingUri(){
//
//    }


    //TODO: da vedere come fare, perchè forse merita togliere uri e devo aggiungere il controllo col broker
    public function compareUriLists(
        array $databaseUris,
        array $knowledgebaseUris,
        array $ownershipUris,
        array $reconstructedUris
    ): array {
        // Create lookup arrays for faster checks
        $dbSet = array_flip($databaseUris);
        $kbSet = array_flip($knowledgebaseUris);
        $ownSet = array_flip($ownershipUris);
        $reconstructedSet = array_flip($reconstructedUris);

        // Combine all URIs to process
        $allUris = array_unique(array_merge(
            $databaseUris,
            $knowledgebaseUris,
            $ownershipUris

        ));

        //con AllUris trovo gli uri univoci, ovvero che sono salvati almeno in un posto
        //poi ciclo su essi e guardo dove sono presenti, se ci sono allora lì l'inserimento è andato a buon fine.
        //TODO: potrei prendere la lista degli univoci e poi fare le chiamate per controllare anche il broker
        $results = [];

        foreach ($allUris as $uri) {
            $inDatabase = isset($dbSet[$uri]);
            $inKnowledgebase = isset($kbSet[$uri]);
            $inOwnership = isset($ownSet[$uri]);
            $isReconstructed = isset($reconstructedSet[$uri]);

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

            // Create status object with consistent property names
            // At this point, we know the URI is partially present (in at least one system but not all)
            $status = [
                'uri' => $uri,
                'in_database' => $inDatabase,
                'in_knowledgebase' => $inKnowledgebase,
                'in_ownership' => $inOwnership,
                'has_uri' => $isReconstructed // Consistent naming with API output
            ];

            $results[] = $status;
        }
        $myfile = fopen("WIPcheckDump.txt", "w") or die("Unable to open file!");
// Convert array to JSON format for better readability
        $jsonResults = json_encode($results, JSON_PRETTY_PRINT);
        fwrite($myfile, $jsonResults);
        fclose($myfile);

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
$processId = 1;     // 0 = SELECT & UPDATE; 1 = UPSERT
$link = mysqli_connect($host, $usernamedb, $passworddb);
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_reporting(E_ERROR | E_PARSE);




$oidc = new OpenIDConnectClient($keycloakHostUri, $clientId, $clientSecret);
$oidc->providerConfigParam(array(
    'token_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/token',
    'userinfo_endpoint' => $keycloakHostUri . '/auth/realms/master/protocol/openid-connect/userinfo'
));
$accessToken1 ="";
if (isset($_REQUEST['token'])) {

    $mctime = microtime(true);
    $tkn = $oidc->refreshToken($_REQUEST['token']);
    error_log("---- device.php:" . (microtime(true) - $mctime));

    $accessToken1 = $tkn->access_token;

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
        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }

        // Initialize the comparison class
        $comparison = new UriComparison(
            $kbUrl,
            $dbConnection,
            $_REQUEST['organization']
        );

        // Get comparison results from all sources
        $results = $comparison->compareAllSourcesComplete();

        // Process and display the results in a clear, organized way


        foreach ($results as $result) {
            $apiResult['result'][$result['uri']] = [
                'database_record' => (bool)$result['in_database'],
                'knowledge_base' => (bool)$result['in_knowledgebase'],
                'ownership_record' => (bool)$result['in_ownership'],
                //'is_reconstructable' => $result['is_reconstructable'] ?? false,
                'has_uri' => (bool)$result['has_uri'],
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
                $haveURI = $link->real_escape_string($value['has_uri'] ? 'true' : 'false');

                // Prepare the values for SQL insertion
                $values[] = "('$uri', '$checkdate', '$organization', '-', $isInDB, $isInKB, $isInOwnership, $haveURI)";

        }

        $sql = "INSERT INTO iotdb.devicecheck (uri, checkdate, organization, log, isInDB, isInKB, isInOwnership, haveURI) VALUES " . implode(", ", $values);

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
            SELECT uri, isInDB, isInKB, isInOwnership, haveURI,log
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
                        'has_uri' => (bool)$row['haveURI'],
                        'action_taken' => $row["log"]  // Assuming no action has been taken
                    ];
                }
            }
        }
    }
    echo json_encode($apiResult);
}else if ($action == "applyRecoverDelete"){
    Global $apiResult;
    $apiResult = [];  // Initialize the API result array
    $apiResult["status"] = 'ok';
    $apiResult["opResult"]=[];

    $kbUrl =$_REQUEST['kbUrl'];
    //echo $_REQUEST['kbUrl'];

    $k1=$_REQUEST["k1"];
    $k2=$_REQUEST["k2"];

    //se manca il token non faccio nulla
    if(isset($_REQUEST["token"])){
    $accessToken=$_REQUEST["token"];

        //ricevo selectedInfo che contiene i dati dei device che ho selezionato dal front end
        //Esempio [String uri,bool retryChecked,bool deleteChecked, bool isinDB, bool isinKB, bool isinOwn, bool haveUri]
        //retryChecked e deleteChecked indicano il tipo di operazione e sono auto esclusivi
        if(isset($_REQUEST["selectedInfo"])) {
            $selectedInfo = $_REQUEST["selectedInfo"];
            $selectedInfo = json_decode($selectedInfo, true);

            //ciclo su tutti i device che ho selezionato
            for ($i = 0; $i < count($selectedInfo); $i++) {

                //caso RETRY
                if ($selectedInfo[$i]["retryChecked"] == true && $selectedInfo[$i]["deleteChecked"] == false) {

                    $resultArray = [$selectedInfo[$i]["uri"], $selectedInfo[$i]["isInDb"], $selectedInfo[$i]["isInKb"], $selectedInfo[$i]["isInOwn"], $selectedInfo[$i]["haveUri"], ""];

                    $delete=false;

                    //se retryUriEntry ritorna vero, allora l'uri ha avuto recupero positivo o non aveva bisogno di essere recuperato
                    //altrimenti manca completamente la entry nel db e lo considero non recuperabile
                    if (retryUriEntry($selectedInfo[$i], $link,$delete, $apiResult)) {

                        //se resultArray[3] è falso vuol dire che manca dalla ownership
                        if ($resultArray[3] == false) {

                            //se il reinserimento ha successo cambio isInOwn a true
                            if (retryOwnershipEntry($selectedInfo[$i], $link, $k1, $k2, $apiResult)) {
                                $resultArray[3] = true;
                            } else {
                                $resultArray[3] = false;
                            }
                        }
                        //se resultArray[2] è falso vuol dire che manca dalla KB
                        if ($resultArray[2] == false) {

                            //se il reinserimento ha successo cambio isInKB a true
                            if (retryKBEntry($selectedInfo[$i], $link, $apiResult)) {
                                $resultArray[2] = true;
                            } else {
                                $resultArray[2] = false;
                            }
            }

                        //se arrivato qua, il recupero dell'uri sicuramente ha avuto esito positivo quindi
                        //imposto $resultArray[4] (haveURI)
                        $resultArray[4] = true;

                        //se arrivo qui retryUriEntry ha restituito true, perciò vuol dire che nel db la entry esiste,
                        //o quantomeno ha trovato qualcosa
                        $resultArray[1] = true;

                    } else {
                        $resultArray[4] = false;
                    }

                    //aggiorno la lista delle azioni intraprese per quel device
                    $resultArray[5] .= $apiResult["actionTaken"] . ",\n";

                    //ad ogni oggetto resetto la lista delle azioni fatte
                    $apiResult["actionTaken"] = "";

                    //questo appende ad OpResult, un array per ogni dispositivo selezionato
                    $apiResult["opResult"][$i] = $resultArray;

                }elseif ($selectedInfo[$i]["retryChecked"] == false && $selectedInfo[$i]["deleteChecked"]== true){

                    //caso DELETE
                    $delete=true;
                    $resultArray = [$selectedInfo[$i]["uri"], $selectedInfo[$i]["isInDb"], $selectedInfo[$i]["isInKb"], $selectedInfo[$i]["isInOwn"], $selectedInfo[$i]["haveUri"], ""];

                    //provo a recuperare l'uri, ma con delete==true
                    if (retryUriEntry($selectedInfo[$i], $link,$delete, $apiResult)) {

                        //se ritorna vero imposto isinDb and haveURI to true
                        $resultArray[4] = true;
                        $resultArray[1] = true;

                        //caso manchi sia in kb che in ownership ma sia nel DB
                        if($resultArray[1]==true && $resultArray[3]== false && $resultArray[2]==false){

                            //elimino semplicemente dal DB e in di rimbalzo anche da event_values
                            deleteDbEntry($selectedInfo[$i], $link, $apiResult);
                            $resultArray[1]=false;

                            //caso manchi solo dalla KB ma sia in DB e in ownership
                        }elseif($resultArray[1]==true && $resultArray[3]== true && $resultArray[2]==false){

                            //cancello prima la ownership
                            if(deleteOwnershipEntry($selectedInfo[$i], $link, $apiResult)){
                                $resultArray[3] = false;

                                //Se la delete nella ownership ha ritornato true posso, come ultimo step,
                                //eliminare dal db
                                if(deleteDbEntry($selectedInfo[$i], $link, $apiResult));{
                                    $resultArray[1]=false;
                                    $resultArray[4] = false;
                                }
                            };

                            //caso in cui sia in KB e in DB ma non nella ownership
                        }elseif ($resultArray[1]==false && $resultArray[3]== true && $resultArray[2]==true){

                            //TODO:da vedere
                            //TODO:per eliminare dalla kb probabilmente deve essere nella ownership
                            deleteKbEntry($selectedInfo[$i], $link,$kbUrl, $apiResult);



                        }

                    }else{
                        $resultArray[4] = false;
                    }
                        $resultArray[5] .= $apiResult["actionTaken"] . ",\n";

                        //ad ogni oggetto resetto la lista delle azioni fatte
                        $apiResult["actionTaken"] = "";

                        //questo appende ad OpResult, un array per ogni dispositivo selezionato
                        $apiResult["opResult"][$i] = $resultArray;
                        $apiResult["status"]='ok';
                    }


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


                $stmt = $dbConnection->prepare("UPDATE iotdb.devicecheck SET log=?,isInDB=?,isInKB=?,isInOwnership=?, haveURI=? WHERE uri=?");
                $i1 = (int)$apiResult["opResult"][$i][1];
                $i2 = (int)$apiResult["opResult"][$i][2];
                $i3 = (int)$apiResult["opResult"][$i][3];
                if($i1 == 0 && $i2==0 && $i3==0){
                    $i4 = (int)false;
                }else {
                    $i4 = (int)$apiResult["opResult"][$i][4];
                }
                $stmt->bind_param("ssssss", $apiResult["opResult"][$i][5], $i1, $i2, $i3,$i4,$uri);
                $stmt->execute();
                $nrows = $stmt->affected_rows;


                }catch (Exception $e){
                    echo "cannot save result in db";
        }
    }

            }


    echo json_encode($apiResult);

        }else{
        $apiResult["status"]='ko';
        $apiResult["log"]= "access token not present";
        echo json_encode($apiResult);

    }
}


/**
 * Funzione per riprovare ad inserire la uri nel caso mancasse
 *
 * @param Array $selectedDevice informazioni sul dispositivo preso in esame
 * @param mysqli $link Connessione al DB
 * @param bool $delete True/false se sono in modalità delete, se false sono in recovery
 * @param Array &$apiResult reference all'array contenente i log
 */
function retryUriEntry($selectedDevice,$link,$delete,&$apiResult)
{
    // se c'è nel db prendi quello e mettilo nella ownership
    //se c'è nella ownership prendilo e mettilo nel db


    //recupera il broker,org e deviceId dall'uri splittando sulle slash
    global $ownershipdburl;

    try {
        //estraggo i dati del dispositivo sto analizzando
        $broker = explode('/', $selectedDevice["uri"])[0];
        $broker = str_replace("...", "", $broker);
        $organization = explode('/', $selectedDevice["uri"])[1];
        $deviceId = explode('/', $selectedDevice["uri"])[2];
        $uri = "http://www.disit.org/km4city/resource/iot/" . $broker . "/" . $organization . "/" . $deviceId;

        $apiResult["log"] .= $broker . $organization . $deviceId;

        //questi due sono array di ritorno dalle funzioni [true/false , uri], se ho trovato o meno l'uri
        //e in caso positivo l'uri stesso
        $urifoundDevices = queryDevicesForUri($deviceId, $organization, $broker, $link, $apiResult);
        $urifoundOwnership = queryOwnershipForUri($deviceId, $organization, $broker, $link, $apiResult);

        //CASO Uri non presente ne in device ne in ownership
        if ( $urifoundDevices[0] == false && $urifoundOwnership[0] == false ) {



            $dbConnection = $link;
            if ($dbConnection->connect_error) {
                throw new Exception("Database connection failed: " . $dbConnection->connect_error);
            }

            //query sull'id, se ritorna almeno un risultato, la entry in devices è presente e manca solo l'uri
            $stmt = $dbConnection->prepare("SELECT id FROM iotdb.devices WHERE uri=? AND organization=? AND contextBroker=?");
            $stmt->bind_param("sss", $uri, $organization,$broker);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                //ricostruisco l'uri dai dati che ho e lo metto in devices, update sul device che prima ho appurato esistesse in devices
                $uri="http://www.disit.org/km4city/resource/iot/".$broker."/".$organization."/".$deviceId;
                $stmt = $dbConnection->prepare("UPDATE iotdb.devices SET uri=? WHERE contextBroker=? AND organization=? AND id=?");
                $stmt->bind_param("ssss", $uri,$broker,$organization,$deviceId);
                $stmt->execute();
                $stmt->close();
                $apiResult["log"] .= "\n uri missing in both Ownership and devices.Rebuilded in devices\n";
                return true;
            }else{

                //se la query SELECT ritorna un risultato che non è univoco oppure è 0 vuol dire che nel database manca completamente la entry
                //ad ora questo vuol dire che il device è irrecuperabile
                $apiResult["log"] .= "\n missing complete entry in devices, NOT RECOVERABLE \n";
                $stmt->close();
                return false;

            }



        } else if ($urifoundDevices[0] == true && $urifoundOwnership[0] == false) {
            //caso uri trovato in device(di conseguenza assumo che ci sia tutta la entry) ma non trovato nella ownership
            $cleanUri = str_replace("\/", "/", $urifoundDevices[1]);

            //costruisco elementId che nella ownership un device è indicizzato con quello
            $elementId = $organization . ":" . $broker . ":" . $deviceId;


            $dbConnection = $link;
            if ($dbConnection->connect_error) {
                throw new Exception("Database connection failed: " . $dbConnection->connect_error);
            }

            //estraggo l'elementName usando l'element id come selettore
            $stmt = $dbConnection->prepare("SELECT elementName FROM $ownershipdburl WHERE elementId=?");
            $stmt->bind_param("s", $elementId);
            $stmt->execute();
            $result = $stmt->get_result();

            //se mi rende un valore maggiore di zero vuol dire che la entry esiste, quindi manca solo l'elementurl
            // e lo inserisco
            if ($result->num_rows > 0) {

                $query = "UPDATE $ownershipdburl SET elementUrl=? WHERE elementId=? ";
                $stmt = $dbConnection->prepare($query);
                $stmt->bind_param("ss", $cleanUri, $elementId);
                $stmt->execute();
                $stmt->close();
                $apiResult["log"] .= "\n added Elementurl in ownership from devices\n";
                return true;
            }else{
                //se restituisce diverso da 1 manca completamente la entry, ma ancora è recuperabile, quindi ritorno comunque true.
                $apiResult["log"] .= "\n Missing complete entry in own\n";
                $stmt->close();

                return true;


            }



        } else if ($urifoundDevices[0] == false && $urifoundOwnership[0] == true) {
            //caso uri trovato nella ownership ma non in devices

            $cleanUri = str_replace("\/", "/", $urifoundOwnership[1]);



            $dbConnection = $link;
            if ($dbConnection->connect_error) {
                throw new Exception("Database connection failed: " . $dbConnection->connect_error);
            }

            //se non sono in delete provo ad aggiungere l'uri nei devices se comunque la entry cè
            if($delete==false) {
                $stmt = $dbConnection->prepare("SELECT id FROM iotdb.devices WHERE id=? AND organization=? AND contextBroker=?");
                $stmt->bind_param("sss", $deviceId, $organization, $broker);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {

                    $query = "UPDATE iotdb.devices SET uri=? WHERE contextBroker=? AND organization=? AND id=?";
                    $stmt = $dbConnection->prepare($query);
                    $stmt->bind_param("ssss", $cleanUri, $broker, $organization, $deviceId);
                    $stmt->execute();
                    $stmt->close();
                    $apiResult["log"] .= "\n added Uri in device from Ownership\n";
                    return true;
                } else {
                    //se manca tutta la entry nel db e non solo l'uri il db non è recuperabile
                    $apiResult["actionTaken"] .= " Not Recoverable ";
                    $apiResult["log"] .= "\n Missing complete entry in db NOT RECOVERABLE\n";
                    return false;
                }
                //se sono in delete non mi interessa, tanto lo dovrò cancellare lo marco come se lo avessi trovato
            }else if($delete==true){

                return true;

            }
        }
        //se arrivo qua l'unico caso possibile è che sia in devices che in ownership l'uri sia stato trovato,
        //quindi non devo recuperare nulla
        $apiResult["actionTaken"].=" uri doesn't need recovery ";
        return true;

    }catch (Exception $e){
        $stmt->close();
        $apiResult["actionTaken"].=" Excp recovering uri ";
        return false;
    }

}



/**
 * Funzione per recuperare e controllare lo stato dell'uri e della entry nel database "devices"
 *
 * @param String $deviceId id del device
 * @param String $organization organizzazione a cui appartiene il device
 * @param String $broker broker a cui fa rifermento il device
 * @param mysqli $link connessione al DB
 * @param Array &$apiResult reference all'array contenente i log
 * @return Array [bool $urifound, String $dburi] se ho trovato l'uri e l'uri in caso positivo
 */
function queryDevicesForUri($deviceId,$organization,$broker,$link,&$apiResult)
{
    $dbUri = null;
    $uri = "http://www.disit.org/km4city/resource/iot/" . $broker . "/" . $organization . "/" . $deviceId;

    $dbConnection = $link;
    if ($dbConnection->connect_error) {
        throw new Exception("Database connection failed: " . $dbConnection->connect_error);
    }

    $stmt = $dbConnection->prepare("SELECT uri FROM iotdb.devices WHERE uri=? AND organization=? AND contextBroker=?");
    $stmt->bind_param("sss", $uri, $organization,$broker);
    $stmt->execute();


    $result = $stmt->get_result();
    //se mi ritorna >0 risultati vuol dire che almeno la entry è presente nella tabella devices
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            //se row["uri"] è falso il campo uri è vuoto
            if(!$row["uri"]){
                $urifound=false;
                $apiResult["log"].= "\n URI vuoto in devices \n";
            }else{
                $urifound=true;
                $apiResult["log"].= "\n URI trovato nei devices\n";
                $dbUri=$row["uri"];
            }
        }
    } else {
        $apiResult["missingEntryInDb"]=true;
        $apiResult["log"].= " Entry completamente mancante nei devices";
    }

    // Close statement
    $stmt->close();

    return array($urifound,$dbUri);

}




/**
 * Funzione per recuperare e controllare lo stato dell'uri e della entry nel database "ownership"
 *
 * @param String $deviceId id del device
 * @param String $organization organizzazione a cui appartiene il device
 * @param String $broker broker a cui fa rifermento il device
 * @param mysqli $link connessione al DB
 * @param Array &$apiResult reference all'array contenente i log
 */
function queryOwnershipForUri($deviceId,$organization,$broker,$link,&$apiResult)
{
    global $ownershipdburl;
    $ownElementUrl= null;


    $dbConnection = $link;
    if ($dbConnection->connect_error) {
        throw new Exception("Database connection failed: " . $dbConnection->connect_error);
    }

    $elementId = $organization.":".$broker.":".$deviceId;


    $query = "SELECT elementUrl FROM " . $ownershipdburl. " WHERE elementId=?";
    $stmt = $dbConnection->prepare($query);
    $stmt->bind_param("s", $elementId);
    $stmt->execute();


    $result = $stmt->get_result();
    //se mi restituisce >0 risultati vuol dire che almeno la entry è presente nella ownership
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            //se elementUrl è falso manca l'uri
            if(!$row["elementUrl"]){
                $urifound=false;
                $apiResult["log"].= "\n ElementUrl vuoto nella ownership\n";
            }else{
                $urifound=true;
                $apiResult["log"].= "\n ElementUrl trovato nella ownership\n";
                $ownElementUrl=$row["elementUrl"];
            }
        }
    } else {
        $apiResult["missingEntryInOwn"]=true;
        $apiResult["log"].= " Entry completamente mancante nella ownership";
    }

    // Close statement
    $stmt->close();

    return array($urifound,$ownElementUrl);

}



/**
 * Funzione per recuperare i dati mancanti e riprovare l'inserimento nella KB
 *
 *  @param Array $selectedDevice informazioni sul dispositivo preso in esame
 *  @param mysqli $DBlink Connessione al DB
 *  @param Array &$apiResult reference all'array contenente i log
 */
function retryKbEntry($selectedDevice,$DBlink,&$apiResult){

    global $accessToken1;
    $apiResult["access"]=$accessToken1;

    try {

        $contextbroker = explode('/', $selectedDevice["uri"])[0];
        $contextbroker = str_replace("...", "", $contextbroker);
        $organization = explode('/', $selectedDevice["uri"])[1];
        $deviceId = explode('/', $selectedDevice["uri"])[2];


        $dbConnection = $DBlink;
        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }


        //Recupero i dati che mi servono da devices per un inserimento sulla kb
        $query = "SELECT * FROM iotdb.devices WHERE contextBroker=? AND id=? AND organization=? ";
        $stmt = $dbConnection->prepare($query);
        $stmt->bind_param("sss", $contextbroker, $deviceId, $organization);
        $stmt->execute();


        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $type = $row["devicetype"];
                $kind = $row["kind"];
                $protocol = $row["protocol"];
                $format = $row["format"];
                $macaddress = $row["macaddress"];
                $model = $row["model"];
                $producer = $row["producer"];
                $latitude = $row["latitude"];
                $longitude = $row["longitude"];
                $visibility = $row["visibility"];
                $frequency = $row["frequency"];
                $subnature = $row["subnature"];
                $staticAttributes = $row["static_attributes"];
                if (isset($row["wktGeometry"])) {
                    $wktGeometry = $row["wktGeometry"];
                } else {
                    $wktGeometry = null;
                }
                if (isset($row["hlt"])) {
                    $hlt = $row["hlt"];
                } else {
                    $hlt = null;
                }
                //$hlt = $row["hlt"];
                $service = $row["service"];
                $servicePath = $row["servicePath"];

            }
        } else {
            $apiResult["missingEntryInDb"] = true;
            $apiResult["log"] .= "Entry completamente mancante nei devices";
        }


        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }

        //Recupero da event_values i valori del device
        $query = "SELECT * FROM iotdb.event_values WHERE cb = ? AND device = ?";

        $stmt = $dbConnection->prepare($query);

        $stmt->bind_param("ss", $contextbroker, $deviceId);

        $stmt->execute();

        $result = $stmt->get_result();

        $listAttributes = [];

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

                $listAttributes[] = $att;
            }
        }else{
            $apiResult["log"] .= "event_values are empty not recoverable";
            $query = "SELECT log FROM iotdb.devicecheck WHERE uri=? AND organization=?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("ss", $uri,$organization);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $newlog = $row["log"] . "- Event_values are empty";


            $query = "UPDATE iotdb.devicecheck SET log=? WHERE uri=? AND organization=?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("sss",$newlog, $uri,$organization);
            $stmt->execute();

            $apiResult["log"] .= "\n Failed insertion into the KB\n";
            $apiResult["actionTaken"] .= "- Event_values are empty";
            $apiResult["actionTaken"].=$kbLog["log"];
            $stmt->close();
            return false;
        }




        $apiResult["log"] .= "\n recovered from DB to insert in KB\n";
        $kbUrl = "";

        //questo serve per identificare il db, visto che io qua opero ad un livello più alto il nome della tabella glielo devo dare qua
        $dbConnection->select_db('iotdb');

        $kbLog = [];
        //chiamo register kb con i dati che ho estratto prima
        registerKB($dbConnection, $deviceId, $type, $contextbroker, $kind, $protocol,
            $format, $macaddress, $model, $producer, $latitude, $longitude, $visibility,
            $frequency, $listAttributes, $subnature, $staticAttributes, $kbLog, 'yes', $organization, $kbUrl, $service, $servicePath, $accessToken1, $wktGeometry, $hlt);

        //se ha successo, aggiorno la mia tabella dei device da recuperare
        if ($kbLog["status"] == 'ok') {
            $uri="http://www.disit.org/km4city/resource/iot/".$contextbroker."/".$organization."/".$deviceId;

            $query = "UPDATE iotdb.devicecheck SET isInKB=true WHERE uri=? AND organization=?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("ss", $uri,$organization);
            $stmt->execute();

            $apiResult["log"] .= "\n recovered and inserted into the KB\n";
            $apiResult["actionTaken"] .= ", KB insert";
            $stmt->close();
            return true;


        } else {

            $uri="http://www.disit.org/km4city/resource/iot/".$contextbroker."/".$organization."/".$deviceId;


            $query = "SELECT log FROM iotdb.devicecheck WHERE uri=? AND organization=?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("ss", $uri,$organization);
            $stmt->execute();

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $newlog = $row["log"] . "- KB Failed";


            $query = "UPDATE iotdb.devicecheck SET log=? WHERE uri=? AND organization=?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("sss",$newlog, $uri,$organization);
            $stmt->execute();

            $apiResult["log"] .= "\n Failed insertion into the KB\n";
            $apiResult["actionTaken"] .= ", KB failed";
            $apiResult["actionTaken"].=$kbLog["log"];
            $stmt->close();
            return false;

        }
    }catch (Exception $e){
        $stmt->close();
        $apiResult["log"].= "exception during insert in KB (".$e.")";
        $apiResult["actionTaken"] .= ", KB Exception";
        return false;
    }


}




/**
 * Funzione per recuperare i dati mancanti e riprovare l'inserimento nella Ownership
 *
 *  @param Array $selectedDevice informazioni sul dispositivo preso in esame
 *  @param mysqli $DBlink Connessione al DB
 *  @param String $k1 UUID
 *  @param String $k2 UUID
 *  @param Array &$apiResult reference all'array contenente i log
 */
function retryOwnershipEntry($selectedDevice,$DBlink,$k1,$k2,&$apiResult)
{
    global $ownershipdburl;

    $contextbroker = explode('/', $selectedDevice["uri"])[0];
    $contextbroker = str_replace("...", "", $contextbroker);
    $organization = explode('/', $selectedDevice["uri"])[1];
    $deviceId = explode('/', $selectedDevice["uri"])[2];

    $elementId= $organization.":".$contextbroker.":".$deviceId;

    //hard-coded ma mi servovono obbligatoriamente per riprovare l'inserimento della ownership
    $elementType="IOTID";
    $currentDate = date("Y-m-d H:i:s");
    $username='RootAdmin';
    $elementDetails = array(
        "k1" => $k1,
        "k2" => $k2,
        "contextbroker" => $contextbroker
    );

    $elementDetails = json_encode($elementDetails);


    try {
        $dbConnection = $DBlink;
        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }

        //controllo che manchi tutta la entry e non solo l'uri nella ownership
        $query = "SELECT elementName FROM $ownershipdburl WHERE elementId=? ";
        $stmt = $dbConnection->prepare($query);
        $stmt->bind_param("s", $elementId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        //manca tutta la entry nella ownership
        if ($row === null) {
            try{
                $query = "SELECT uri FROM iotdb.devices WHERE contextBroker=? AND id=? AND organization=? ";
                $stmt = $dbConnection->prepare($query);
                $stmt->bind_param("sss", $contextbroker, $deviceId, $organization);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $uri = $row["uri"];


                $query = "INSERT INTO $ownershipdburl (username, elementId, elementType, elementName, elementUrl,elementDetails,created) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $dbConnection->prepare($query);
                $stmt->bind_param("sssssss", $username, $elementId, $elementType, $deviceId, $uri, $elementDetails, $currentDate);
                $stmt->execute();

                $apiResult["log"] .= "\n reinserted the full device in Ownership\n";
                $newlog = ", Ownership insert(full)";
                $apiResult["actionTaken"] .= ", Ownership insert (full)";

                $query = "UPDATE iotdb.devicecheck SET log=? AND isInOwnership=true WHERE uri=? AND organization=?";
                $stmt = $dbConnection->prepare($query);
                $stmt->bind_param("sss",$newlog, $uri,$organization);
                $stmt->execute();
                $stmt->close();
                return true;
            }catch (Exception $e){
                $apiResult["log"].= "Failed inserting elementUrl in ownership (".$e.")";
                $apiResult["actionTaken"] .= ", Ownership failed";
                $stmt->close();
                return false;
            }

        } else {
            try {
                // Manca solo elementUrl ci metto solo quello
                $query = "SELECT uri FROM iotdb.devices WHERE contextBroker=? AND id=? AND organization=? ";
                $stmt = $dbConnection->prepare($query);
                $stmt->bind_param("sss", $contextbroker, $deviceId, $organization);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $uri = $row["uri"];


                $query = "UPDATE $ownershipdburl SET elementUrl=? WHERE elementId=?";
                $stmt = $dbConnection->prepare($query);
                $stmt->bind_param("ss", $uri, $elementId);
                $stmt->execute();

                $apiResult["log"] .= "\n recovered ElementUrl in Ownership\n";
                $newlog = ", Ownership insert(only elementUrl)";
                $apiResult["actionTaken"] .= ", Ownership insert (only elementUrl)";

                $query = "UPDATE iotdb.devicecheck SET log=? AND isInOwnership=1 WHERE uri=? AND organization=?";
                $stmt = $dbConnection->prepare($query);
                $stmt->bind_param("sss",$newlog, $uri,$organization);
                $stmt->execute();
                $stmt->close();
                return true;


            }catch (Exception $e){

                $apiResult["log"].= "Failed inserting elementUrl in ownership (".$e.")";
                $apiResult["actionTaken"] .= ", Ownership failed";
                $stmt->close();
                return false;

            }
        }

    }catch (Exception $e){
    $stmt->close();
    $apiResult["log"].= "exception during insert in Ownership (".$e.")";
    $apiResult["actionTaken"] .= ", Ownership Exception";
    return false;
}
}




/**
 * Funzione per eliminare le entry di un particolare dispositivo nella tabella "event_values"
 *
 * @param Array $selectedDevice informazioni sul dispositivo preso in esame
 *  @param mysqli $link Connessione al DB
 *  @param Array &$apiResult reference all'array contenente i log
 */
function deleteEventValues($selectedDevice,$link,&$apiResult){

    $broker = explode('/', $selectedDevice["uri"])[0];
    $broker = str_replace("...", "", $broker);
    $deviceId = explode('/', $selectedDevice["uri"])[2];

    try{
        $dbConnection = $link;
        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }


        //cerco e cancello in event_values tutte le entry di un certo device
        $stmt = $dbConnection->prepare("DELETE FROM iotdb.event_values WHERE device=? AND cb=?");
        $stmt->bind_param("ss", $deviceId,  $broker);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        //se eseguo su 0 righe allora il device non era stato salavto in event_values
        if($affectedRows==0){
            $apiResult["log"] .= "\n no need to delete from event_values \n".$affectedRows;
            $apiResult["actionTaken"] .= "\n no need to delete from event_values \n".$affectedRows;
        }else{
            $apiResult["log"] .= "\n deleted from event_values\n".$affectedRows;
            $apiResult["actionTaken"] .= "\n deleted from event_values\n".$affectedRows;
        }
        $stmt->close();
    }catch (Exception $e){
        $apiResult["log"] .= "\n excp deleting from event_values\n";
        $apiResult["actionTaken"] .= "\n excp deleting from event_values\n";
    }
}


/**
 * Funzione per eliminare la entry di un particolare dispositivo nella tabella "devices"
 *
 * @param Array $selectedDevice informazioni sul dispositivo preso in esame
 *  @param mysqli $link Connessione al DB
 *  @param Array &$apiResult reference all'array contenente i log
 */
function deleteDbEntry($selectedDevice,$link,&$apiResult)
{
    try{
        $broker = explode('/', $selectedDevice["uri"])[0];
        $broker = str_replace("...", "", $broker);
        $organization = explode('/', $selectedDevice["uri"])[1];
        $deviceId = explode('/', $selectedDevice["uri"])[2];

        $uri="http://www.disit.org/km4city/resource/iot/".$broker."/".$organization."/".$deviceId;

        $dbConnection = $link;
        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }
        // select sul device per controllare che la entry esista
        $stmt = $dbConnection->prepare("SELECT uri FROM iotdb.devices WHERE uri=? AND organization=? AND contextBroker=?");
        $stmt->bind_param("sss", $uri, $organization,$broker);
        $stmt->execute();
        $result = $stmt->get_result();
        //se esiste ed è unico posso cancellarlo da devices
        if ($result->num_rows == 1) {
            $stmt = $dbConnection->prepare("DELETE FROM iotdb.devices WHERE uri=? AND organization=? AND contextBroker=?");
            $stmt->bind_param("sss", $uri, $organization, $broker);
            $stmt->execute();
            deleteEventValues($selectedDevice,$link,$apiResult);

            $apiResult["log"] .= "\n deleted from devices\n";
            $apiResult["actionTaken"] .= "\n deleted from devices\n";
            $stmt->close();
            return true;
        }else if($result->num_rows > 1) {
            //se è più di uno c'è un'ambiguità nel db per sicurezza non elimino
            $apiResult["log"] .= "\n cannot delete from devices, ambiguity in db\n";
            $apiResult["actionTaken"] .= "\n failed deletion from devices\n";
            $stmt->close();
            return false;

        }else if($result->num_rows == 0 ) {
            //manca tutta la entry nel db nulla da eliminare, posso eliminare direttamente da event_values
            deleteEventValues($selectedDevice,$link,$apiResult);
            $apiResult["log"] .= "\n no need to delete from db\n";
            $apiResult["actionTaken"] .= "\n no need to delete from db\n";
            return true;
        }

    }catch (Exception $e){
        $apiResult["log"] .= "\n excp during delete from devices\n";
        $apiResult["actionTaken"] .= "\n excp in deletion from devices\n";
        $stmt->close();
        return false;
    }

}



/**
 * Funzione per eliminare la entry di un particolare dispositivo nella ownership
 *
 * @param Array $selectedDevice informazioni sul dispositivo preso in esame
 *  @param mysqli $link Connessione al DB
 *  @param Array &$apiResult reference all'array contenente i log
 */
function deleteOwnershipEntry($selectedDevice, $link, &$apiResult){
    global $ownershipdburl;

    //conviene fare check se esiste una entry sola ed eventualmente cancellarla
    try{
        $broker = explode('/', $selectedDevice["uri"])[0];
        $broker = str_replace("...", "", $broker);
        $organization = explode('/', $selectedDevice["uri"])[1];
        $deviceId = explode('/', $selectedDevice["uri"])[2];


        $elementId = $organization.":".$broker.":".$deviceId;

        $dbConnection = $link;
        if ($dbConnection->connect_error) {
            throw new Exception("Database connection failed: " . $dbConnection->connect_error);
        }
        //controllo se esiste la entry in ownership
        $stmt = $dbConnection->prepare("SELECT id FROM $ownershipdburl WHERE elementId=?");
        $stmt->bind_param("s", $elementId);
        $stmt->execute();
        $result = $stmt->get_result();

        $role=$_SESSION[loggedUsername];
        //se esiste ed è unica posso cancellarlo
        if ($result->num_rows == 1) {
            $stmt = $dbConnection->prepare("UPDATE $ownershipdburl SET deleted = NOW(),deletedBy = ? WHERE elementId = ?");
            $stmt->bind_param("ss", $role,$elementId);
            $stmt->execute();
            $apiResult["log"] .= "\n deleted from ownership\n";
            $apiResult["actionTaken"] .= "\n deleted from ownership\n";
            $stmt->close();
            return true;

        }else if ($result->num_rows < 1) {
            //manca tutta la entry nella ownership nulla da eliminare,
            $apiResult["log"] .= "\n no need to delete from ownership\n";
            $apiResult["actionTaken"] .= "\n no need to delete from ownership\n";
            return  true;
        }else {
            //tutti gli altri casi errore( più di una entry nella ownership)
            $apiResult["log"] .= "\n failed delete from ownership\n";
            $apiResult["actionTaken"] .= "\n failed delete from ownership\n";
            $stmt->close();
            return false;
        }
    }catch (Exception $e){
        $apiResult["log"] .= "\n excp deleting from ownership\n";
        $apiResult["actionTaken"] .= "\n excp deleting from ownership\n";
        $stmt->close();
        return false;
    }

}

function deleteUriEntry()
{

}




function deleteKbEntry($selectedDevice, $link,$kbUrl, &$apiResult){


    global $accessToken1;
    $apiResult["access"]=$accessToken1;

    $broker = explode('/', $selectedDevice["uri"])[0];
    $broker = str_replace("...", "", $broker);

    $deviceId = explode('/', $selectedDevice["uri"])[2];

    $dbConnection = $link;
    if ($dbConnection->connect_error) {
        throw new Exception("Database connection failed: " . $dbConnection->connect_error);
    }

    $dbConnection->select_db('iotdb');


    $deleteKBlog=[];
    deleteKB($dbConnection,$deviceId,$broker,$kbUrl ,$deleteKBlog,"","",$accessToken1);
    $apiResult["deleteKbLog"]=$deleteKBlog;


}
