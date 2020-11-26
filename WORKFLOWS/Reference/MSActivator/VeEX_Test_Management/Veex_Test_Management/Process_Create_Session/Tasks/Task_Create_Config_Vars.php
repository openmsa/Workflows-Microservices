<?php

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

function list_args()
{
	create_var_def('TEST_MODE', 'String');
	create_var_def('FLOW_CONTROL', 'String');
	create_var_def('AUTO_NEGOTIATION', 'String');
	create_var_def('SPEED', 'String');
	create_var_def('NO_OF_STREAMS', 'Integer');
	create_var_def('RTD_MEASUREMENT', 'Integer');
	create_var_def('FRAME_SIZE', 'Integer');
	create_var_def('MAC_SOURCE', 'String');
	create_var_def('MAC_DESTINATION', 'String');
}

$device_id = substr($context['device_id'], 3);

$response = _configuration_variable_create($device_id, 'SESSION_ID', $context['SESSION_ID'], 'Integer');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'NO_OF_STREAMS', $context['NO_OF_STREAMS'], 'Integer');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

if ($context['RTD_MEASUREMENT'] === "true") {
	$RTD_MEASUREMENT = 1;
}
else {
	$RTD_MEASUREMENT = 0;
}
$response = _configuration_variable_create($device_id, 'RTD_MEASUREMENT', $RTD_MEASUREMENT, 'Integer');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'FRAME_SIZE', $context['FRAME_SIZE'], 'Integer');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'AUTO_NEGOTIATION', $context['AUTO_NEGOTIATION'], 'String');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'FLOW_CONTROL', $context['FLOW_CONTROL'], 'String');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'SPEED', $context['SPEED'], 'String');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'TEST_MODE', $context['TEST_MODE'], 'String');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'MAC_SOURCE', $context['MAC_SOURCE'], 'String');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = _configuration_variable_create($device_id, 'MAC_DESTINATION', $context['MAC_DESTINATION'], 'String');
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

task_exit(ENDED, "Template config vars updated successfully on the device $device_id.");

?>