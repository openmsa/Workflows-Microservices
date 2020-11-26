<?php 

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

function list_args()
{
}

$PROCESSINSTANCEID = $context['PROCESSINSTANCEID'];
$EXECNUMBER = $context['EXECNUMBER'];
$TASKID = $context['TASKID'];
$process_params = array('PROCESSINSTANCEID' => $PROCESSINSTANCEID,
						'EXECNUMBER' => $EXECNUMBER,
						'TASKID' => $TASKID);
	
$device_id = substr($context['device_id'], 3);
$session_id = $context['SESSION_ID'];
$test_mode = $context['TEST_MODE'];

$start_command = ":P2:THRPT:START;";
$configuration = "session {$session_id}\n{$test_mode}\n{$start_command}";
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
$pushconfig_status_message = str_replace("\\n", "\n", $response['wo_comment']);

$response = prepare_json_response(ENDED, "Test Started successfully.\n{$pushconfig_status_message}", $context, true);
echo $response;

?>