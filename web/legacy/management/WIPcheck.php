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
     * Fetches ownership URIs from the profiledb database
     *
     * @return array List of URIs from ownership records
     * @throws Exception If there's an error with the database query
     */
    public function fetchOwnershipData(): array {
        // Query to get non-deleted IOT device URIs from ownership table
        $query = "SELECT elementUrl FROM profiledb.ownership 
                 WHERE elementType = 'IOTID' AND deleted IS NULL";

        try {
            // Execute the query
            $result = $this->link->query($query);
        if (!$result) {
                throw new Exception("Failed to execute ownership query: " . $this->link->error);
        }

            // Fetch all results and extract URIs
            $ownershipData = $result->fetch_all(MYSQLI_ASSOC);
            $ownershipUris = array_column($ownershipData, 'elementUrl');

            // Free the result set
            $result->free();

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
        OFFSET " . ($offset + 1) . "
        LIMIT " . $this->batchSize;
        //return "SELECT DISTINCT ?s WHERE { ?s ?p ?o } LIMIT {$this->batchSize} OFFSET {$offset}";
    }

    public function fetchReconstructableUris(): array {

        //magari fare la stessa query per recuperare quelli con gli uri mancanti dalla ownwership
        //
        // Query to get entries without URIs but with enough information to reconstruct them
        $query = "SELECT contextBroker, organization, id FROM iotdb.devices 
                 WHERE uri IS NULL AND organization = ? 
                 AND contextBroker IS NOT NULL AND id IS NOT NULL";

        try {
            $stmt = $this->link->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare reconstructable query: " . $this->link->error);
        }

            // Bind the organization parameter
            $stmt->bind_param("s", $this->organization);

            if (!$stmt->execute()) {
                throw new Exception("Failed to execute reconstructable query: " . $stmt->error);
            }

            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Failed to get reconstructable results: " . $stmt->error);
            }

            // Fetch all results
            $reconstructableRecords = $result->fetch_all(MYSQLI_ASSOC);

            // Reconstruct URIs using the pattern: contextBroker/organization/id
            $reconstructedUris = [];
            foreach ($reconstructableRecords as $record) {
                if (isset($record['contextBroker'], $record['organization'], $record['id'])) {
                    $reconstructedUris[] = sprintf("%s/%s/%s/%s",
                        "http://www.disit.org/km4city/resource/iot",
                        $record['contextBroker'],
                        $record['organization'],
                        $record['id']
                    );
                }
            }

            $stmt->close();
            return array_unique($reconstructedUris);

        } catch (Exception $e) {
            error_log("Error in fetchReconstructableUris: " . $e->getMessage());
            throw $e;
        }
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
            $databaseUris = $this->fetchAllDBData();
            $reconstructableUris = $this->fetchReconstructableUris();
            $knowledgebaseUris = $this->fetchAllKBData();
            $ownershipUris = $this->fetchOwnershipData();

            // Perform the comparison with all sources
            return $this->compareUriLists(
                $databaseUris,
                $knowledgebaseUris,
                $ownershipUris,
                $reconstructableUris
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
    public function fetchDBData(int $offset): array {
        // Prepare the query using a prepared statement to prevent SQL injection
        $query = "SELECT uri FROM iotdb.devices WHERE organization = ? LIMIT ? OFFSET ?";

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

            // Fetch all URIs as an associative array and extract the 'uri' column
            $uris = array_column($result->fetch_all(MYSQLI_ASSOC), 'uri');

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
    public function fetchAllDBData(): array {
        $allUris = [];
        $offset = 0;

        try {
            while (true) {
                $uris = $this->fetchDBData($offset);
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


    /**
     * Compare URIs from all sources automatically
     *
     * @param array $ownershipUris URIs from ownership records
     * @param array $reconstructableUris Optional list of URIs that can be reconstructed
     * @return array List of URIs with their presence status in each system
     */
    public function compareAllSources(array $ownershipUris, array $reconstructableUris = []): array {
        try {
            // Fetch both DB and KB data automatically
            $databaseUris = $this->fetchAllDBData();
            $knowledgebaseUris = $this->fetchAllKBData();

            return $this->compareUriLists($databaseUris, $knowledgebaseUris, $ownershipUris, $reconstructableUris);
        } catch (Exception $e) {
            error_log("Error in compareAllSources: " . $e->getMessage());
            throw $e;
            }
        }

    public function handleMissingUri(){

            }

    public function compareUriLists(
        array $databaseUris,
        array $knowledgebaseUris,
        array $ownershipUris,
        array $reconstructableUris = []
    ): array {
        // Previous implementation remains the same
        $dbSet = array_flip($databaseUris);
        $kbSet = array_flip($knowledgebaseUris);
        $ownSet = array_flip($ownershipUris);
        $reconstructableSet = array_flip($reconstructableUris);

        $allUris = array_unique(array_merge(
            $databaseUris,
            $knowledgebaseUris,
            $ownershipUris,
            $reconstructableUris
        ));
        //echo var_dump($allUris);

        $brokenUris = [];

        foreach ($allUris as $uri) {
            if (isset($dbSet[$uri]) && isset($kbSet[$uri]) && isset($ownSet[$uri])) {
                continue;
        }

            $status = [
                'uri' => $uri,
                'in_database' => isset($dbSet[$uri]),
                'in_knowledgebase' => isset($kbSet[$uri]),
                'in_ownership' => isset($ownSet[$uri]),
                'is_reconstructable' => isset($reconstructableSet[$uri]),
                'has_uri' => isset($dbSet[$uri]) || isset($reconstructableSet[$uri])
            ];

            $brokenUris[] = $status;
        }

        return $brokenUris;
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

$env = $genFileContent['environment']['value'];

$host_PD= $personalDataFileContent["host_PD"][$env];
$token_endpoint= $personalDataFileContent["token_endpoint_PD"][$env];
$client_id= $personalDataFileContent["client_id_PD"][$genFileContent['environment']['value']];
$client_secret= $personalDataFileContent["client_secret_PD"][$genFileContent['environment']['value']];
$username= $personalDataFileContent["usernamePD"][$genFileContent['environment']['value']];
$password= $personalDataFileContent["passwordPD"][$genFileContent['environment']['value']];
$organizationServiceURI = $serviceURIFileContent["organizationApiURI"][$env];


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
                'is_reconstructable' => $result['is_reconstructable'] ?? false,
                'has_uri' => $result['has_uri'] ?? true,
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
                $values[] = "('$uri', '-', '$checkdate', '$organization', '-', $isInDB, $isInKB, $isInOwnership, $haveURI)";

        }

        $sql = "INSERT INTO iotdb.devicecheck (uri, problem, checkdate, organization, log, isInDB, isInKB, isInOwnership, haveURI) VALUES " . implode(", ", $values);

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
            SELECT uri, problem, isInDB, isInKB, isInOwnership, haveURI
            FROM iotdb.devicecheck
            WHERE organization = '" . $link->real_escape_string($organization) . "'
        ";

        $devices = [];
        $result = $link->query($sql);

        // If the query returns results
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $uri = $row['uri'];
                $problem = $row['problem'];

                // Rebuild the structure for the result array

                if (!isset($apiResult['result'][$uri])) {
                    $apiResult['result'][$uri] = [
                        'database_record' => (bool)$row['isInDB'],
                        'knowledge_base' => (bool)$row['isInKB'],
                        'ownership_record' => (bool)$row['isInOwnership'],
                        'is_reconstructable' => $row['is_reconstructable'] ?? false,
                        'has_uri' => $row['haveURI'] ?? true,
                        'action_taken' => ''  // Assuming no action has been taken
                    ];
                }

                $apiResult['result'][$uri]['problem'] = $problem;
            }
        }
    }
    echo json_encode($apiResult);
}
