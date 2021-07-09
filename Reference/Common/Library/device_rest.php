<?php

require_once COMMON_DIR . 'curl_performer.php';

/**
 * Create Template Managed Device
 *
 * @param unknown $customer_id
 * @param unknown $device_name
 * @param unknown $manufacturer_id
 * @param unknown $model_id
 * @param unknown $login
 * @param unknown $password
 * @param unknown $password_admin
 * @param unknown $management_address
 * @param string $log_enabled
 * @param string $log_more_enabled
 * @param string $mail_alerting
 * @param string $reporting
 * @return unknown
 */
 //********************** LED add $managementInterface *********************/
function _device_create ($customer_id, $device_name, $manufacturer_id,
						$model_id, $login, $password, $password_admin,
						$management_address, $device_external_reference = "",
						$log_enabled = "true", $log_more_enabled = "true", 
						$mail_alerting = "true", $reporting = "false", 
						$snmp_community = SNMP_COMMUNITY_DEFAULT, 
						$managementInterface = "", $hostname = "",  $management_port = 22) {

	$array = array('name' => $device_name,
			'manufacturerId' => $manufacturer_id,
			'modelId' => $model_id,
			'login' => $login,
			'password' => $password,
			'passwordAdmin' => $password_admin,
			'logEnabled' => $log_enabled,
			'logMoreEnabled' => $log_more_enabled,
			'mailAlerting' => $mail_alerting,
			'reporting' => $reporting,
			'managementAddress' => $management_address,
			'managementPort' => $management_port,
			'hostname' => $hostname,
			'externalReference' => $device_external_reference,
			'snmpCommunity' => $snmp_community
			);
  if (isset($managementInterface) && $managementInterface){
    $array['managementInterface'] = $managementInterface;
  }  
  
	$json = json_encode($array);
	$msa_rest_api = "device/{$customer_id}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api, $json);
	$response = perform_curl_operation($curl_cmd, "CREATE TEMPLATE MANAGED DEVICE");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * curl -u ncroot:Ub1qub3  -XGET http://localhost:80/ubi-api-rest/device/id/104
 *
 */
function _device_read_by_id ($device_id) {

        $msa_rest_api = "device/id/{$device_id}";
        $curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
        $response = perform_curl_operation($curl_cmd, "READ DEVICE BY ID");
        $response = json_decode($response, true);
        if ($response['wo_status'] !== ENDED) {
                $response = json_encode($response);
                return $response;
        }
        $response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
        return $response;
}

/**
 * <pre>
 *     curl -u ncroot:ubiqube  -XDELETE http://localhost:10080/ubi-api-rest/device/id/1671
 * </pre>
 *
 */
function _device_delete ($device_id) {

	$msa_rest_api = "device/id/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_DELETE, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "DELETE DEVICE BY ID");
	return $response;
}

/**
 * <pre>
 *     curl -u ncroot:ubiqube  -XDELETE http://localhost:10080/ubi-api-rest/device/reference/1671
 * </pre>
 *
 */
function _device_delete_by_reference ($device_reference) {

	$msa_rest_api = "device/reference/{$device_reference}";
	$curl_cmd = create_msa_operation_request(OP_DELETE, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "DELETE DEVICE BY REFERENCE");
	return $response;
}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -XPOST http://localhost:10080/ubi-api-rest/device/activate/657
 */
function _device_mark_as_provisioned ($device_id) {

	$msa_rest_api = "device/activate/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_POST, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "MARK AS PROVISIONED BY DEVICE ID");
	return $response;
}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -XOP_GET http://localhost:10080/ubi-api-rest/DeviceWS/getDeviceStatus/657
 */
function _device_get_status ($device_id) {

	$msa_rest_api = "device/status/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "GET DEVICE STATUS");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * curl -u ncroot:Ub1qub3  -XGET http://localhost:80/ubi-api-rest/device/id/104
 * 
 */
function _device_read_by_id ($device_id) {
	
	$msa_rest_api = "device/id/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "READ DEVICE BY ID");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * curl -u ncroot:ubiqube  -X GET http://localhost:8080/ubi-api-rest/device/reference/DEVICE_REF1234
 *
 * @param unknown $device_reference
 * @return Ambigous <unknown, mixed>
 */
function _device_read_by_reference ($device_reference) {

	$msa_rest_api = "device/reference/{$device_reference}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "READ BY DEVICE REFERENCE");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * reads, from the database, the hostname set for a device
 * @param  $device_id
 */
function _device_get_hostname_by_id ($device_id) {
	$msa_rest_api = "device/v1/hostname/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "READ DEVICE HOSTNAME BY ID");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}


/**
 * this function sets the hostname of a device in the database
 * it uses the REST API: PUT device/v1/{$device_id}/hostname/{$hostname}
 * @param device_id: the database identifier of the device, It should be a type long
 * @param hostname: a valid hostname
 */
function _device_set_hostname_by_id ($device_id, $hostname) {
	$msa_rest_api = "device/v1/{$device_id}/hostname/{$hostname}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "SET DEVICE HOSTNAME BY ID");
	return $response;
}

/**
 * reads, from the database, the nature set for a device
 * @param  $device_id
 */
function _device_get_nature_by_id ($device_id) {
	$msa_rest_api = "device/v1/nature/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "READ DEVICE NATURE BY ID");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * this function sets the nature of a device in the database
 * it uses the REST API: PUT device/v1/{$device_id}/nature/{$hostname}
 * @param device_id: the database identifier of the device, It should be a type long
 * @param nature: one of VPUB, PHSL, VPRV
 */
function _device_set_nature_by_id ($device_id, $nature = "VPRV") {
	$msa_rest_api = "device/v1/{$device_id}/nature/{$nature}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "SET DEVICE NATURE BY ID");
	return $response;
}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -XPOST http://localhost:10080/ubi-api-rest/device/extended_attribute/set/2927?attributeName=MAINTENANCE_MODE&attributeValue=TRUE
 */
  
function _device_set_maintenance_mode ($device_id, $enable = "true") {
    if (isset($enable) && ($enable === true|| $enable ==='true' ||  $enable == 1 )){
      $enable = 'true';  //we should put a string into the DB (0 and 1 don't work, we get an error and we can not see the device)
    }else{
      $enable = 'false';
    }

	

	$msa_rest_api = "device/extended_attribute/set/{$device_id}?attributeName=MAINTENANCE_MODE&attributeValue={$enable}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "SET DEVICE MAINTENANCE MODE to $enable");
	// $response = perform_curl_operation($curl_cmd, "SET DEVICE MAINTENANCE MODE");
	return $response;
}

/**
 * REST endpoint available in MSA NB on REMOTE MSA in V15.3
 * ex:
 * curl -u ncroot:ubiqube  -XPOST http://localhost:10080/ubi-api-rest/device/extended_attribute/set/2927?attributeName=MAINTENANCE_MODE&attributeValue=TRUE
 */
  
function _device_set_maintenance_mode_v15_3 ($device_id, $enable = "true") {
  $msa_rest_api = "device/maintenance/{$device_id}?enable={$enable}";
  $curl_cmd = create_msa_operation_request(OP_POST, $msa_rest_api);
  $response = perform_curl_operation($curl_cmd, "SET DEVICE MAINTENANCE MODE on MSA v15.3");
  return $response;
 }


/**
 * Set device extended attribute variable in the MSA DB
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -XPOST http://localhost:10080/ubi-api-rest/device/extended_attribute/set/2927?attributeName=Resource_group&attributeValue=IRELAND
 */

function _device_set_extended_attribute ($device_id, $attribute_name, $attribute_value) {

        $msa_rest_api = "device/extended_attribute/set/{$device_id}?attributeName={$attribute_name}&attributeValue={$attribute_value}";
        $curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
        $response = perform_curl_operation($curl_cmd, "Set device extended attribute $attribute_name to $attribute_value");
        return $response;

}

/**
 * Get device extended attribute variable from the MSA DB
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -XPOST http://localhost:10080/ubi-api-rest/device/extended_attribute/get/2927?attributeName=RESOURCE_GROUP
 */

function _device_get_extended_attribute ($device_id, $attribute_name) {

        $msa_rest_api = "device/extended_attribute/get/{$device_id}?attributeName={$attribute_name}";
        $curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
        $response = perform_curl_operation($curl_cmd, "Get device $device_id - extended attribute $attribute_name");
        return $response;

}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -XOP_GET http://localhost:10080/ubi-api-rest/configuration/status/id/{deviceId}
 */
function _device_get_update_config_status ($device_id) {

	$msa_rest_api = "device/configuration/status/id/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "GET DEVICE UPDATE CONFIG STATUS");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -XPOST http://localhost:10080/ubi-api-rest/device/configuration/update/657
 */
function _device_do_update_config ($device_id) {

	$msa_rest_api = "device/configuration/update/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_POST, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "DO UPDATE CONFIGURATION");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -X GET http://localhost:8080/ubi-api-rest/device/backup/status/id/2932
 */
function _device_get_backup_status_by_device_id ($device_id) {

	$msa_rest_api = "device/backup/status/id/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "GET DEVICE BACKUP STATUS");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube -XPUT http://localhost:10080/ubi-api-rest/device/backup/657
 */
function _device_do_backup ($device_id) {

	$msa_rest_api = "device/backup/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "DO BACKUP");
	return $response;
}

/**
 * REST endpoint available in MSA NB
 * ex:
 * curl -u ncroot:ubiqube  -X "POST" http://localhost:10080/ubi-api-rest/device/ping/127.0.0.1
 */
function _device_do_ping ($address) {

	$msa_rest_api = "device/ping/{$address}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "DO PING");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 <pre>
 curl -u ncroot:ubiqube  -X GET http://localhost:10080/ubi-api-rest/device/provisioning/status/657
 </pre>
 * @return ProvisioningStatus
 */
function _device_get_provisioning_status_by_id ($device_id) {

	$msa_rest_api = "device/provisioning/status/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "GET PROVISIONING STATUS BY DEVICE ID");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 <pre>
 curl -u ncroot:ubiqube  -XPOST http://localhost:10080/ubi-api-rest/device/provisioning/657
 </pre>
 */
function _device_do_initial_provisioning_by_id ($device_id) {

	$msa_rest_api = "device/provisioning/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_POST, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "DO INITIAL PROVISIONING BY DEVICE ID");
	return $response;
}

/**
 * <pre>
 * curl -u ncroot:ubiqube  -X PUT http://localhost:8080/ubi-api-rest/device/push_configuration/2932
 * -d '{"configuration": "config system interface\nedit port1\nset ip 192.168.1.10 255.255.255.0\nend"}'
 * </pre>
 *
 **/
function _device_do_push_configuration_by_id ($device_id, $configuration) {

	$msa_rest_api = "device/push_configuration/{$device_id}";
	$array = array('configuration' => $configuration);
	$json = json_encode($array);
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api, $json);
	$response = perform_curl_operation($curl_cmd, "DO PUSH CONFIGURATION BY DEVICE ID");
	return $response;
}

/**
	<pre>
	curl -u ncroot:ubiqube  -X GET http://localhost:8080/ubi-api-rest/device/push_configuration/status/2932
	</pre>
 *
 * @param unknown $device_id
 * @return Ambigous <unknown, mixed>
 */

function _device_get_pushconfig_status_by_id ($device_id) {

	$msa_rest_api = "device/push_configuration/status/{$device_id}";
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "GET PUSH CONFIGURATION STATUS BY DEVICE ID");
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		return $response;
	}
	$response = prepare_json_response(ENDED, ENDED_SUCCESSFULLY, $response['wo_newparams']['response_body']);
	return $response;
}

/**
 * curl -u ncroot:ubiqube  -XPUT 'http://MSA
	IP/ubi-api-rest/device/management_ip/update/{device_id}?ip=10.10.10.10&mask=255.255.255.255'
 */
function _device_update_management_ip_address ($device_id, $ip_address, $netmask = "255.255.255.255") {

	$msa_rest_api = "device/management_ip/update/{$device_id}?ip={$ip_address}&mask={$netmask}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "UPDATE DEVICE MANAGEMENT IP ADDRESS");
	return $response;
}

/**
 * curl -u ncroot:ubiqube  -H "Content-Type: application/json" -XPUT
 * 'http://localhost:10080/ubi-api-rest/device/conf_profile/switch/3768?old_profile_ref=A&new_profile_ref=C'
 */
function _device_configuration_profile_switch ($device_id, $old_profile_ref, $new_profile_ref) {

	$msa_rest_api = "device/conf_profile/switch/{$device_id}?old_profile_ref={$old_profile_ref}&new_profile_ref={$new_profile_ref}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "SWITCH DEVICE CONFIGURATION PROFILE");
	return $response;
}

/**
 * curl -u ncroot:ubiqube  -XPUT 'http://localhost:10080/ubi-api-rest/device/credentials/update/14710?login=myLogin&password=myPassword'
 * 
 */
function _device_update_credentials ($device_id, $login, $password) {
	
	$msa_rest_api = "device/credentials/update/{$device_id}?login={$login}&password={$password}";
	$curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "UPDATE DEVICE CREDENTIALS IN MSA DATABASE");
	return $response;
}


function _device_do_update_firmware ($device_id) {
	logToFile('[device_rest][do_update_firmware] update firmware on '.$device_id);
	$api_cmd='/opt/ubi-jentreprise/bin/api/device/doFirmwareUpdateByDeviceId.sh '.$device_id;
	logToFile("[device_rest][do_update_firmware]  -> ".$api_cmd);
	$res=shell_exec($api_cmd);
	logToFile("[device_rest][do_update_firmware] ".$res);
	return $res;
}


/**
 * REST endpoint available in MSA NB : Update the firmware by rest
 * Put $option='REST-API' to run the firmware update on the device via rest API. CLI by default 
 * ex:
 * with CLI : /usr/bin/curl -isw 'HTTP_CODE=%{http_code}' -u ncroot:ubiqube --connect-timeout 60 --max-time 60 -H "Content-Type: application/json" -X PUT 'http://127.0.0.1:80/ubi-api-rest/device/v1/doFirmwareUpdateByDeviceId?deviceId=2935&doFirmwareUpdateByDeviceIdOption='
 * with REST-API : /usr/bin/curl -isw 'HTTP_CODE=%{http_code}' -u ncroot:ubiqube --connect-timeout 60 --max-time 60 -H "Content-Type: application/json" -X PUT 'http://127.0.0.1:80/ubi-api-rest/device/v1/doFirmwareUpdateByDeviceId?deviceId=2935&doFirmwareUpdateByDeviceIdOption=REST-API'
 */
function _device_do_update_firmware_rest ($device_id, $option='') {
  logToFile('[device_rest][_device_do_update_firmware_rest] update firmware on '.$device_id);
  $msa_rest_api = "device/v1/doFirmwareUpdateByDeviceId?deviceId={$device_id}&doFirmwareUpdateByDeviceIdOption=$option";
  $curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
  $response = perform_curl_operation($curl_cmd, "Run Device FIRMWARE UPDATE");
  return $response;
} 


function _device_check_update_firmware ($device_id) {
	logToFile('[device_rest][check_update_firmware] update firmware on '.$device_id);
	$api_cmd='/opt/ubi-jentreprise/bin/api/device/getFirmwareUpdateStatusByDeviceId.sh '.$device_id;
	logToFile("[device_rest][check_update_firmware]  -> ".$api_cmd);
	$res=shell_exec($api_cmd);
	logToFile("[device_rest][check_update_firmware] ".$res);
	return $res;
}

/**
 * REST endpoint available in MSA NB : Get the update firmware status by rest
 * ex:
 * /usr/bin/curl -isw 'HTTP_CODE=%{http_code}' -u ncroot:ubiqube --connect-timeout 60 --max-time 60 -H "Content-Type: application/json" -X GET 'http://127.0.0.1:80/ubi-api-rest/device/v1/getFirmwareUpdateStatusByDeviceId/2935'
 // output : { "message" : "New firmware version is build0398\r\n\r\n", "date" : "10-10-2019 15:22:22", "status" : "ENDED", "finalStep" : false
 */
function _device_check_update_firmware_rest ($device_id) {
  // 
  logToFile('[device_rest][_device_check_update_firmware_rest] update firmware on '.$device_id);
  $msa_rest_api = "device/v1/getFirmwareUpdateStatusByDeviceId/{$device_id}";
  $curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
  $response = perform_curl_operation($curl_cmd, "GET FIRMWARE UPDATE STATUS");
  return $response;
}


function _device_create_configure_variable ($device_id, $variablename, $variablevalue) {
	logToFile('[device_rest][create_configure_variable] Add variable '.$variablename.' ('.$variablevalue.') to '.$device_id);
	$api_cmd='/opt/ubi-jentreprise/bin/api/deviceconfigurationvariable/createConfigurationVariable.sh '.$device_id.' '.$variablename.' '.$variablevalue;
	logToFile("[device_rest][create_configure_variable]  -> ".$api_cmd);
	$res=shell_exec($api_cmd);
	logToFile("[device_rest][create_configure_variable] ".$res);
	return $res;
}

function _device_rollback_do_backup ($device_id) {
	logToFile('[device_rest][rollback_do_backup] Backup device '.$device_id);
	$api_cmd='/opt/ubi-jentreprise/bin/api/rollback/doBackup.sh '.$device_id;
	logToFile("[device_rest][rollback_do_backup]  -> ".$api_cmd);
	$res=shell_exec($api_cmd);
	logToFile("[device_rest][rollback_do_backup] ".$res);
	return $res;
}

function _device_rollback_list_revision ($device_id) {
	logToFile('[device_rest][rollback_list_revision] List revision for device '.$device_id);
	$api_cmd='/opt/ubi-jentreprise/bin/api/rollback/doListRevisions.sh '.$device_id;
	logToFile("[device_rest][rollback_list_revision]  -> ".$api_cmd);
	$res=shell_exec($api_cmd);
	logToFile("[device_rest][rollback_list_revision] ".$res);
	return $res;
}

function _device_rollback_do_restore ($device_id, $revision_id) {
	logToFile('[device_rest][rollback_do_restore] Restore device '.$device_id.' using revision '.$revision_id);
	$api_cmd='/opt/ubi-jentreprise/bin/api/rollback/doRestore.sh '.$device_id.' '.$revision_id;
	logToFile("[device_rest][rollback_do_restore]  -> ".$api_cmd);
	$res=shell_exec($api_cmd);
	logToFile("[device_rest][rollback_do_restore] ".$res);
	return $res;
}

/**
 * Get devices list  by customer  (customerId integer)
 *
 * [root@ACS2 Process]#  /usr/bin/curl -isw 'HTTP_CODE=%{http_code}' -u ncroot:ubiqube --connect-timeout 60 --max-time 60 -H "Content-Type: application/json" -X GET 'http://127.0.0.1:80/ubi-api-rest/lookup/v1/customers/12/devices' -d '{ }'
 * [ { "id" : 2977, "prefix" : "ACS",  "ubiId" : "ACS2977", "name" : "FAERA_generic_3.130.148.141", "externalReference" : "ACS2977", "operatorId" : 0, "displayName" : "FAERA_generic_3.130.148.141 - ACS2977", "displayNameForJsps" : "FAERA_generic_3.130.148.141 - ACS2977" }, {.....
 *
 * @param customerId Integer
 * @return
 *     $response
 */
function _device_get_by_customer ($customerId) {
    $msa_rest_api = "lookup/v1/customers/{$customerId}/devices";
    $curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
    $response = perform_curl_operation($curl_cmd, "GET DEVICES BY CUSTOMER");
    return $response;
}


/**
 * Get device groups by customer
 *
 * curl -u ncroot:ubiqube  -XPUT 'http://localhost:10080/ubi-api-rest/device-group/v1/customer/{customerId}'
 *
 * @param customerId
 * @return
 *     $response
 */
function _device_get_groups_by_customer ($customerId) {

    $msa_rest_api = "device-group/v1/customer/$customerId";
    $curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
    $response = perform_curl_operation($curl_cmd, "GET DEVICE GROUPS BY CUSTOMER");
    return $response;
}

/**
 * Get devices by device group
 *
 * curl -u ncroot:ubiqube  -XPUT 'http://localhost:10080/ubi-api-rest/device-group/v1/devices/id/{deviceGroupId}'
 *
 * @param deviceGroupId
 * @return
 *      $response
 */
function _device_get_devices_by_device_group ($deviceGroupId) {

    $msa_rest_api = "device-group/v1/devices/id/$deviceGroupId";
    $curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
    $response = perform_curl_operation($curl_cmd, "GET DEVICES BY DEVICE GROUP");
    return $response;
}

/**
 * Set device group
 *
 * curl -u ncroot:ubiqube  -XPUT 'http://localhost:10080/ubi-api-rest/device-group/v1/add -d {
"id", "name", "address", "remarks", "externalRefs", "longitude", "latitude", "customerId", "deviceIds"
}'
 *
 * @param DeviceGroup $json_device_grou
 * @return
 *      $response
 */
function _device_create_device_group ($customerId, $device_group_id, $name, $address, $sites, $actorId, $remarks, $externalRefs, $longitude = 0,
							$latitude = 0, $newGroup = false, $toDelete = false) {

    $msa_rest_api = "device-group/v1/add";
	
    $array = array();
    $array['id'] = $device_group_id;
    $array['name'] = $name;

    if (empty($address)) {
       $array['address'] = "";
    } else {
       $array['address'] = $address; 
    }

    if (empty($sites) || $sites == "") {
       $array['sites'] = array();
    } else {
       $array['sites'] = $sites;
    }

    $array['sites'] = $sites;
    $array['remarks'] = $remarks;
    $array['externalRefs'] = $externalRefs;
    $array['longitude'] = $longitude;
    $array['latitude'] = $latitude;
    $array['customerId'] = $customerId;
    $array['actorId'] = $actorId;
    $array['newGroup'] = $newGroup;
    $array['toDelete'] = $toDelete;

    $json = json_encode($array);

    $curl_cmd = create_msa_operation_request(OP_POST, $msa_rest_api, $json);
    $response = perform_curl_operation($curl_cmd, "CREATE DEVICE GROUP");
    return $response;
}

/**
 * Attach device to device group by device ID
 *
 * curl -u ncroot:ubiqube  -XPUT 'http://localhost:10080/ubi-api-rest/device-group/v1/{deviceGroupId}/attach-device/{deviceId}'
 *
 * @param deviceGroupId
 * @param deviceId
 * @return
 *      $response
 */
function _device_attach_to_device_group_by_device_id ($deviceGroupId, $deviceId) {

    $msa_rest_api = "device-group/v1/$deviceGroupId/attach-device/$deviceId";
    $curl_cmd = create_msa_operation_request(OP_POST, $msa_rest_api);
    $response = perform_curl_operation($curl_cmd, "ATTACH DEVICE TO DEVICE GROUP BY DEVICE ID");
    return $response;
}

/**
 * Attach device to device group by device reference
 *
 * curl -u ncroot:ubiqube  -XPUT 'http://localhost:10080/ubi-api-rest/device-group/v1/{deviceGroupId}/attach-device/{deviceReference}'
 *
 * @param deviceGroupId
 * @param deviceReference
 * @return
 *      $response
 */
function _device_attach_to_device_group_by_device_reference ($deviceGroupId, $deviceReference) {

    $msa_rest_api = "device-group/v1/$deviceGroupId/attach-device/reference/$deviceReference";
    $curl_cmd = create_msa_operation_request(OP_POST, $msa_rest_api);
    $response = perform_curl_operation($curl_cmd, "ATTACH DEVICE TO DEVICE GROUP BY DEVICE REFERENCE");
    return $response;
}


/**
 * Updates device external reference";
 *
 * curl -u ncroot:ubiqube  -XPUT 'http://localhost:10080/ubi-api-rest/device/reference/update/{deviceId}'
 *
 * @param deviceId
 * @param device_external_reference
 * @return
 *      $response
 */
function _device_update_device_external_reference($deviceId, $device_external_reference) {
    $msa_rest_api = "device/reference/update/{$deviceId}?reference={$device_external_reference}";
    $curl_cmd = create_msa_operation_request(OP_PUT, $msa_rest_api);
    $response = perform_curl_operation($curl_cmd, "SET NEW DEVICE EXTERNAL REFERENCE");
    return $response;
} 


/**
 * curl -u ncroot:ubiqube  http://localhost/ubi-api-rest/device/v1/getManagementIpAddress/125
 */
function _device_get_management_ip_address($device_id) {
	$msa_rest_api = 'device/v1/getManagementIpAddress/' . $device_id;
	$curl_cmd = create_msa_operation_request(OP_GET, $msa_rest_api);
	$response = perform_curl_operation($curl_cmd, "GET MANAGEMENT IP ADDRESS");
	$res = json_decode($response, true);
	if ($res['wo_status'] !== ENDED) {
		echo $response;
		exit;
	}
	return json_decode($res['wo_newparams']['response_body'], 1);
}

