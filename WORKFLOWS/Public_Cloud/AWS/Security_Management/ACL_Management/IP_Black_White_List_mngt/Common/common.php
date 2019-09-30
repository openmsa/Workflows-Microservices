<?php

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';


function create_acl_entry($device_id, $acl_id, $rule_number, $ip) {
	
	logToFile ( 'create Network ACL Entry ' . $rule_number . ' for ACL ' . $acl_id . ' with IP ' . $ip );
	
	$micro_service_vars_array = array ();
	$micro_service_vars_array ['object_id'] = "0";
	$micro_service_vars_array ['network_acl_id'] = $acl_id;
	$micro_service_vars_array ['default'] = 'false';
	
	$entry_array = array ();
	$entry_array ['port_range_from'] = '';
	$entry_array ['port_range_to'] = '';
	$entry_array ['protocol'] = '-1';
	$entry_array ['rule_action'] = 'deny';
	$entry_array ['cidr_block'] = $ip . '/32';
	$entry_array ['rule_number'] = $rule_number;
	$entry_array ['egress'] = 'false';
	$micro_service_vars_array ['entries'] = array($entry_array);
	
	$network_acl_entry = array (
			'network_acl_entry' => array (
					'0' => $micro_service_vars_array 
			) 
	);
	
	$response = execute_command_and_verify_response ( $device_id, CMD_CREATE, $network_acl_entry, "CREATE Network ACL Entry" );
	
	$response = json_decode ( $response, true );
	
	if ($response ['wo_status'] != ENDED) {
		
		// TODO: failure case
	}
}

function delete_acl($device_id, $acl_id, $context) {

  logToFile('delete ACL '.$acl_id);
  $network_acl = array('network_acl' => $acl_id);
 
  $response = execute_command_and_verify_response($device_id, CMD_DELETE, $network_acl, "DELETE Network ACL");

  $response = json_decode($response, true);
  
  if ($response['wo_status'] != ENDED) {
        logToFile('deletion of ACL: '.$acl_id.' failed');  
        logToFile(debug_dump($response, 'RESPONSE'));
  } 
}

function create_acl($device_id, $vpc_id, $context) {

  logToFile('create Network ACL for VPC '.$vpc_id);
  $micro_service_vars_array = array();
  $micro_service_vars_array['vpc_id'] = $vpc_id;
  $network_acl = array('network_acl' => array('0' => $micro_service_vars_array));
 
  $response = execute_command_and_verify_response($device_id, CMD_CREATE, $network_acl, "CREATE Network ACL");

  $response = json_decode($response, true);
  
  if ($response['wo_status'] == ENDED) {

	$wo_comment=$response['wo_comment'];
	$resp=json_decode($wo_comment,true);
	$id=$resp['NetworkAcl']['NetworkAclId']; 
        logToFile('Network ACL created, ID : '.$id);
	return $id;
  } 
}

?>
