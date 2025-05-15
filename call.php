<?php 
/*
    Used for running:
        - IBM i Commands
        - OS/400 Commands
        - "CALL" programs (CL, COBOL, RPG)
        - IBM Queries/WRKQRY's
        - IQ Queries
    
    This endpoint accounts for any quirks needed to get programs to run, like parameters.
    
    Examples:
    //Running a CALL Program without a parameter
    'command' : 'CALL WRITENOTES'
    OR
    //Running a CALL Program with parameter(s)
    'command' : "CALL RCLOSURE '20240101' '20240131' "
    or
    //Standard IBM i/OS 400 system command
    'command' : 'CPYF FROM(EXCHANGE/ECPFILE) TOFILE(EXCHANGE/EXCHNEWMST) *MBROPT(ADD)'

*/

require '/intranet/includes/strict.php'; 
if ($_POST['auth'] !== 'XXXXXXXXX') {
    http_response_code(400);
    exit( json_encode( ['auth_error' => ''] ) ) ;
}
$cmd = $_POST['command'] ;
$cmd = str_replace(['-', ';'], '', $cmd) ;
$cmd = str_replace("'", "''", $cmd) ;

$db2 = new PDO('odbc:DRIVER={iSeries Access ODBC Driver};SYSTEM=XXXXXXXXX;DATABASE=XXXXXX.XXXXX;', 'XXXX', 'XXXXXXX');

$call = sprintf("CALL QSYS2.QCMDEXC ('%s', '%s')", strtoupper($cmd), strlen($cmd) - substr_count($cmd, "''")  );
try {
    $db2->exec($call);
} catch (PDOException $e) {
    http_response_code(501);
    exit(json_encode(['error' => $e->getMessage(), 'command' => $call, 'line' => $e->getLine(), ]) ) ;
} 


$db2 = NULL;
http_response_code(204);
?>
