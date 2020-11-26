<?php

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';
require_once '/opt/fmc_repository/Process/UBI/Veex_Test_Management/Library/common.php';

function list_args()
{
}

$_index = "veexrtu-" . date("Y-d-m");
$_type = "logs";
$device_id = $context['device_id'];
$rawlog = $context['es_rawlog_write'];

$customer_id = substr($context['UBIQUBEID'], 4);

// Informational
$severity = 6;

$index['index'] = array("_index" => $_index, "_type" => $_type);
$index = json_encode($index);

$data['device_id'] = $device_id;
$data['device_ref'] = $device_id;
$data['rawlog'] = $rawlog;
$data['customer_id'] = $customer_id;
$data['customer_name'] = $context['UBIQUBEID'];
$data['date'] = date("Y-d-m H:i:s");

#$data['severity'] = $severity;
#$data['date'] = date(DATE_ISO8601);
#$data['type'] = "VNOC";
#$data['subtype'] = $context['es_log_subtype'];
#$data['man_id'] = "17010302";
#$data['mod_id'] = "17010302";

$data = json_encode($data);
$es_rest_api = "_bulk";
$curl_cmd = create_es_operation_request("POST", $es_rest_api, "$index\n$data\n");
$response = perform_curl_operation($curl_cmd, "WRITE ES DATA");
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}
$context['es_data_write'] = json_encode($response['wo_newparams']['response_body']);

$response = prepare_json_response(ENDED, "Write ES Data successful", $context, true);
echo $response;

?>