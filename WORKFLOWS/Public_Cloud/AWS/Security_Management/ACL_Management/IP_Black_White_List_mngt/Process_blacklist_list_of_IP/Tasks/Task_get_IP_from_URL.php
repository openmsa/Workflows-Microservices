<?php
require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

function list_args()
{
  }

$ip_list_string = '';
$context['ip_list_string'] = '';

global $CURL_CMD;

$url_IP_provider = $context['url_IP_provider'];
 
$curl_cmd = "{$CURL_CMD} -k -s -XGET ".$url_IP_provider;
logToFile('Curl Request : ' . $curl_cmd);


$response = perform_curl_operation($curl_cmd, "LIST IP");

$response = json_decode($response, true);
$wo_newparams = $response['wo_newparams'];


$ip_list_downloaded = $wo_newparams['response_raw_headers'];
$ip_list_array = explode("\n", $ip_list_downloaded);
$ip_list_count = count($ip_list_array);
foreach($ip_list_array as $ip) {
  $ip_list_string = $ip_list_string.' '.$ip;
}

$ip_list_string = trim($ip_list_string);
$context['ip_list_string'] = $ip_list_string;



// for test when access to URL fails
 //$context['ip_list_string'] = "10.0.0.1 20.0.0.2 30.0.0.3 30.0.0.4 30.0.0.5 30.0.0.6 30.0.0.7 30.0.0.8 30.0.0.9 30.0.0.10 30.0.0.11 30.0.0.12 30.0.0.13 30.0.0.14 30.0.0.15 30.0.0.16 40.0.0.1 30.0.0.2 30.0.0.3 30.0.0.4 40.0.0.14 59.0.0.15 67.0.0.16 98.0.0.1 54.0.0.2 43.0.0.3 23.0.0.4";


task_success($ip_list_count . ' IP addresses to blacklist');

?>