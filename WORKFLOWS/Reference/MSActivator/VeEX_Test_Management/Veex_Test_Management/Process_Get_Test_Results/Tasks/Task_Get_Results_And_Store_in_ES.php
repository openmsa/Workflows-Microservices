<?php 

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';
require_once '/opt/fmc_repository/Process/UBI/Veex_Test_Management/Library/common.php';

function list_args()
{
	create_var_def("get_result_sleep_time", "Integer");
	create_var_def("get_result_total_time", "Integer");
}

error_reporting(E_ALL | E_STRICT);
$timezone = 'UTC';

if (is_link('/etc/localtime')) {
	$filename = readlink('/etc/localtime');

	$pos = strpos($filename, 'zoneinfo');
	if ($pos !== false) {
		$timezone = substr($filename, $pos + strlen('zoneinfo/'));
	}
}

date_default_timezone_set($timezone);

function parse_veex_test_results ($result) {
	
	$result_array = explode(',', $result);
	$array = array();
	for ($index = 0; $index < count($result_array); $index += 2) {
	
		$key = $result_array[$index];
		if (!empty($key)) {
	
			$value = "";
			$EOEVLANCNT_flag = false;
			if (array_key_exists($index+2, $result_array)) {
				$val = $result_array[$index + 2];
				if (!empty($val) && strpos($val, ":") !== 0 && strpos($val, "Test") !== 0) {
					$key .= ":" . $result_array[$index + 1];
					$value = $result_array[$index + 2];
					if (strpos($value, ":EOEVLANCNT") !== false) {
						$value = substr($value, 0, strpos($value, ":EOEVLANCNT"));
						$EOEVLANCNT_flag = true;
						$EOEVLANCNT_key = ":EOEVLANCNT:" . $result_array[$index + 3];
						$EOEVLANCNT_value = 0;
						if (array_key_exists($index + 4, $result_array)) {
							$EOEVLANCNT_value = $result_array[$index + 4];
						}
					}
					$index -= 1;
				}
				else {
					$value = $result_array[$index + 1];
					if (strpos($key, ":TRAFLOSSINFO") === 0) {
						$index += 5;
					}
					if (strpos($key, ":TEMPERATURE") === 0) {
						$index += 1;
					}
				}
			}
	
			$array[$key] = $value;
			if ($EOEVLANCNT_flag) {
				$array[$EOEVLANCNT_key] = $EOEVLANCNT_value;
			}
		}
	}
	return $array;
}

$PROCESSINSTANCEID = $context['PROCESSINSTANCEID'];
$EXECNUMBER = $context['EXECNUMBER'];
$TASKID = $context['TASKID'];
$process_params = array('PROCESSINSTANCEID' => $PROCESSINSTANCEID,
						'EXECNUMBER' => $EXECNUMBER,
						'TASKID' => $TASKID);

$get_result_sleep_time = $context['get_result_sleep_time'];
$get_result_total_time = $context['get_result_total_time'];

$device_id = substr($context['device_id'], 3);
$session_id = $context['SESSION_ID'];
$test_mode = $context['TEST_MODE'];

$_index = "veexrtu-" . date("Y-d-m");
$_type = "logs";
$device_id_full = $context['device_id'];
$customer_id_full = $context['UBIQUBEID'];
$customer_id = substr($context['UBIQUBEID'], 4);
$index['index'] = array("_index" => $_index, "_type" => $_type);
$index = json_encode($index);

$start_time = new DateTime("now");
while (1) {

	//$link_status_command = ":P2:LINK:STATUS ?;";
	//$configuration = "session {$session_id}\n{$test_mode}\n{$link_status_command}";
	$result_command = ":P2:THRPT:RESULT ?;";
	$configuration = "session {$session_id}\n{$test_mode}\n{$result_command}";
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

	//$es_rawlog_data = substr($pushconfig_status_message, strpos($pushconfig_status_message, ":STATUS,"));
	$es_rawlog_data = trim(substr($pushconfig_status_message, strpos($pushconfig_status_message, ":GLOBAL")));
	logToFile(debug_dump($es_rawlog_data, "ES rawlog data :\n"));
	
	//$context['es_rawlog_data'] = $es_rawlog_data;
	$es_rawlog_data_parsed = parse_veex_test_results($es_rawlog_data);

	$data = array();
	$data['device_id'] = $device_id_full;
	$data['device_ref'] = $device_id_full;
	$data['customer_id'] = $customer_id;
	$data['customer_name'] = $customer_id_full;
	//$data['date'] = date("Y-d-m H:i:s");
	$data['date'] = date("Y-m-d H:i:s");
	foreach ($es_rawlog_data_parsed as $key => $value) {
		$value = str_replace("%", "", $value);
		$value = str_replace("M", "", $value);
		$data[$key] = $value;
	}
	logToFile(debug_dump($data, "Test data to be pushed to ES :\n"));

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
	$wo_comment = "Write ES data successful.\n\n" . json_encode($response['wo_newparams']['response_body']);
	update_asynchronous_task_details($process_params, $wo_comment);

	$current_time = new DateTime("now");
	$time_diff = $start_time->diff($current_time);
	//$time_diff_in_minutes = $time_diff->format("%I");
	$time_diff_in_minutes = round(((strtotime($current_time->format('d-m-Y H:i:s')) - strtotime($start_time->format('d-m-Y H:i:s'))))/60, 2);
	logToFile("Start time : " . $start_time->format('d-m-Y H:i:s'));
	logToFile("Current time : " . $current_time->format('d-m-Y H:i:s'));
	logToFile("Time diff : " . $time_diff->format("%H:%I:%S"));
	logToFile("Time diff in minutes : $time_diff_in_minutes");
	
	if ($time_diff_in_minutes >= $get_result_total_time) {
		break;
	}
}

$response = prepare_json_response(ENDED, "Test results written successfully to ES.", $context, true);
echo $response;

?>