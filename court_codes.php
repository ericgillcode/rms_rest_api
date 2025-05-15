<?php 
require '/intranet/includes/strict.php'; 

if ($_POST['auth'] !== 'XXXXX') {
    http_response_code(400);
    exit( json_encode( ['auth_error' => ''] ) ) ;
}

$court_code = $_POST['court_code'] ;
$db2 = new PDO('odbc:DRIVER={iSeries Access ODBC Driver};SYSTEM=XXXXX;DATABASE=XXXXX.XXXXX;', 'XXXXX', 'XXXXX');


$rmspsysasn = "SELECT RMSASNDESC, ASNADDR1, ASNADDR2, ASNCITY, ASNCOUNTY, ASNSTATE, ASNZIPCDE, ASNPHONE FROM XXXXX.RMSPSYSASN WHERE 
(RMSOFFCRCD = :code AND RMSRECTYPE = '3')";

$lci_courts = "SELECT * FROM XXXXX.LCICOURTS WHERE RMSCOURT = :code";

$queries = [
    'RMS COURTS' => $rmspsysasn,
    'LCI COURTS' => $lci_courts,
];
$results = [];
foreach ($queries as $type => $sql) {
    try {
        $statement = $db2->prepare($sql);
        $statement->execute( [':code' => $court_code] );
    } catch (PDOException $e) {
        http_response_code(501);
        exit(json_encode(['error' => $e->getMessage(), 'command' => $sql, 'line' => $e->getLine(), ]) ) ;
    }
    $shelf = [];
    while ($rows = $statement->fetch(PDO::FETCH_ASSOC)) {
        $tmp = [];
        foreach ($rows as $key => $row) {
            if ($row == NULL) {
                $row = '';
            }
            $tmp[$key] = trim($row);
        }
        $shelf[] = $tmp;
        $tmp = NULL;
    }

    $results[$type] = $shelf;
    $shelf = NULL;

}
$db2 = NULL;

http_response_code(202);
echo json_encode($results);
$results = NULL;



?>
