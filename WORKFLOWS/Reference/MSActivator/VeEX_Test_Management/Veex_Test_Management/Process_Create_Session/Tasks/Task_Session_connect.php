<?php 

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

function list_args()
{
	create_var_def('device_id', 'Device');
	create_var_def('RESOURCE_LOCATION', 'String');
}

check_mandatory_param('device_id');

$PROCESSINSTANCEID = $context['PROCESSINSTANCEID'];
$EXECNUMBER = $context['EXECNUMBER'];
$TASKID = $context['TASKID'];
$process_params = array('PROCESSINSTANCEID' => $PROCESSINSTANCEID,
						'EXECNUMBER' => $EXECNUMBER,
						'TASKID' => $TASKID);
	
$device_id = substr($context['device_id'], 3);
$resource_location = $context['RESOURCE_LOCATION'];

$configuration = "session new\nresource assign {$resource_location}";
$response = _device_do_push_configuration_by_id($device_id, $configuration);
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = wait_for_pushconfig_completion($device_id, $process_params);
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = prepare_json_response(FAILED, $response['wo_comment'], $context, true);
	echo $response;
	exit;
}
$pushconfig_status_message = $response['wo_comment'];
$pushconfig_status_message_array = explode("\\n", $pushconfig_status_message);

$session_id = substr($pushconfig_status_message_array[1], strlen("session "), 2);
$context['SESSION_ID'] = $session_id;

$response = prepare_json_response(ENDED, "Session connected and resource $resource_location assigned successfully successfully.\nSession Id : $session_id", $context, true);
echo $response;

?>