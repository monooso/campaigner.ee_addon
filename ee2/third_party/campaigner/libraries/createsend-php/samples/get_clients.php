<?php

require_once '../csrest_general.php';

$wrap = new CS_REST_General('ddec03167e80beff9c2dd867877cd2b3');

$result = $wrap->get_clients();


echo "Result of /api/v3/clients\n<br />";
if($result->was_successful()) {
    echo "Got clients\n<br /><pre>";
    var_dump($result->response);
} else {
    echo 'Failed with code '.$result->http_status_code."\n<br /><pre>";
    var_dump($result->response);
}
echo '</pre>';