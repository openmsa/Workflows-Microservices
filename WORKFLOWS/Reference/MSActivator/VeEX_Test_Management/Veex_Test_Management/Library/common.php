<?php

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';


/**
 * Create MSA ES CURL operation request
 *
 * @param unknown $operation
 * @param unknown $es_rest_api
 * @param string $json_body
 * @return string
 */
function create_es_operation_request ($operation, $es_rest_api, $json_body = "", 
										$connection_timeout = 60, $max_time = 60) {

	global $CURL_CMD;

	$ES_HOST = get_vars_value("UBI_ES_WEBPORTAL_ENDPOINT");
	$ES_PORT = "9200";

	$curl_cmd = "{$CURL_CMD} -i -sw '\nHTTP_CODE=%{http_code}' --connect-timeout $connection_timeout --max-time $max_time -H \"Content-Type: application/json\" -X {$operation} 'http://{$ES_HOST}:{$ES_PORT}/{$es_rest_api}'";
	if ($json_body !== "") {
		if (strpos($es_rest_api, "_bulk") !== false) {
			$curl_cmd .= " --data-binary ";
		}
		else {
			$curl_cmd .= " -d ";
		}
		$curl_cmd .= "'{$json_body}'";
	}
	logToFile('Curl Request : ' . $curl_cmd);
	return $curl_cmd;
}

?>