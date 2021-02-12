<?php


function topology_create_service_view($ipam_device_id) {
	global $context;

        logToFile("*** topology_create_view");

        
        $context ['Nodes'] = array ();
        $context ['Nodes_MAJ'] = array ();


        $response = json_decode(import_objects($ipam_device_id, array('vrf')), True);
	$object_ids_array = $response['wo_newparams']['vrf'];
	
	foreach ($object_ids_array as $vrf => $details) {
		createTopologyNetwork($details['rd'], $details['object_id'], "network", "");
        }	


        $customer_ref = get_customer_ref();
        $list = json_decode(_lookup_list_devices_by_customer_reference($customer_ref), false);

        foreach ($list->wo_newparams as $value) {
		if (strpos($value->name, 'PE') !== false) {
			$deviceId = $value->id;
			$name = $value->name;
			$response = _device_read_by_id ($deviceId);
			$device_info = json_decode($response);
			logToFile(debug_dump($device_info, "DEBUG: DEVICE INFO"));
 			$device_nature = $device_info->wo_newparams->sdNature;
			$status = getStatus($deviceId);

			$error = processDevice($deviceId, $name, $device_nature, $status);

			if ($error != "") {
				logTofile(debug_dump($error, "*** topology_create_view ERROR***"));
			}
		}
        }

        foreach ($list->wo_newparams as $value) {
                if (strpos($value->name, 'CE') !== false) {
                        $deviceId = $value->id;
                        $name = $value->name;
                        $response = _device_read_by_id ($deviceId);
                        $device_info = json_decode($response);
                        logToFile(debug_dump($device_info, "DEBUG: DEVICE INFO"));
                        $device_nature = $device_info->wo_newparams->sdNature;
                        $status = getStatus($deviceId);

                        $error = processDevice($deviceId, $name, $device_nature, $status);

                        if ($error != "") {
                                logTofile(debug_dump($error, "*** topology_create_view ERROR***"));
                        }
                }
        }


        return prepare_json_response(ENDED, "The topology has fully loaded", $context, false);

}




function topology_create_view() {
	global $context;

	logToFile("*** topology_create_view");

	$context ['Nodes'] = array ();
	$context ['Nodes_MAJ'] = array ();
		  
	$customer_ref = get_customer_ref();
	$list = json_decode(_lookup_list_devices_by_customer_reference($customer_ref), false);

	foreach ($list->wo_newparams as $value) {
		$deviceId = $value->id;
		$name = $value->name;
		$response = _device_read_by_id ($deviceId);
        $device_info = json_decode($response);
		$device_nature = $device_info->wo_newparams->sdNature;
		$status = getStatus($deviceId);

		$error = processDevice($deviceId, $name, $device_nature, $status);
		
		if ($error != "") {
			logTofile(debug_dump($error, "*** topology_create_view ERROR***"));
		}
	}
	
	return prepare_json_response(ENDED, "The topology has fully loaded", $context, false);
}


function topology_update_service_view($ipam_device_id) {
        global $context;


        logToFile("*** topology_update_view");

        $context ['Nodes'] = array ();
        $context ['Nodes_MAJ'] = array ();

        $response = json_decode(import_objects($ipam_device_id, array('vrf')), True);
        $object_ids_array = $response['wo_newparams']['vrf'];

        foreach ($object_ids_array as $vrf => $details) {
                createTopologyNetwork($details['rd'], $details['object_id'], "network", "");
        }


        $customer_ref = get_customer_ref();
        $list = json_decode(_lookup_list_devices_by_customer_reference($customer_ref), false);

        foreach ($list->wo_newparams as $value) {
                if (strpos($value->name, 'PE') !== false) {
                        $deviceId = $value->id;
                        $name = $value->name;
                        $response = _device_read_by_id ($deviceId);
                        $device_info = json_decode($response);
                        $device_nature = $device_info->wo_newparams->sdNature;
                        $status = getStatus($deviceId);

                        $error = processDevice($deviceId, $name, $device_nature, $status);

                        if ($error != "") {
                                logTofile(debug_dump($error, "*** topology_update_view ERROR***"));
                        }
                }
        }


        foreach ($list->wo_newparams as $value) {
                if (strpos($value->name, 'CE') !== false) {
                        $deviceId = $value->id;
                        $name = $value->name;
                        $response = _device_read_by_id ($deviceId);
                        $device_info = json_decode($response);
                        logToFile(debug_dump($device_info, "DEBUG: DEVICE INFO"));
                        $device_nature = $device_info->wo_newparams->sdNature;
                        $status = getStatus($deviceId);

                        $error = processDevice($deviceId, $name, $device_nature, $status);

                        if ($error != "") {
                                logTofile(debug_dump($error, "*** topology_create_view ERROR***"));
                        }
                }
        }

        return prepare_json_response(ENDED, "The topology has fully loaded", $context, false);

}

function topology_update_view() {
	global $context;

	logToFile("*** topology_update_view");
	
	if (!isset($context ["Nodes"])) {
		$context ['Nodes'] = array ();
	}
	
	if (!isset($context ["Nodes_MAJ"])) {
		$context ['Nodes_MAJ'] = array ();
	}

	$customer_ref = get_customer_ref();
	$list = json_decode(_lookup_list_devices_by_customer_reference($customer_ref), false);
	
	foreach ($list->wo_newparams as $value) {
		$deviceId = $value->id;
		$name = $value->name;
		$response = _device_read_by_id ($deviceId);
		logToFile(debug_dump($response, "DEVICE INFO: \n"));
		$device_info = json_decode($response);
        $device_nature = $device_info->wo_newparams->sdNature;
		$status = getStatus($deviceId);
		$error = processDevice($deviceId, $name, $device_nature, $status);
		
		if ($error != "") {
			logTofile(debug_dump($error, "*** topology_update_view  ERROR ***"));
		}
	}
	
	return prepare_json_response(ENDED, "Topology  fully loaded", $context, false);
}

function processDevice($device_id, $name, $device_nature, $status) {
	logToFile("*** processDevice <$name> ID: $device_id STATUS: $status");
	try {
		//$status = getStatus($device_id);
		if($status == "UP") {
			calculateDeviceTopology($device_id, $name, $device_nature);
		} else {
			if($status == "UNREACHABLE") {
				createTopology($device_id, $name, $device_nature, "router", "style/topology/img/router_ERROR.svg");
			} else if($status == "NEVERREACHED") {
				createTopology($device_id, $name, $device_nature, "router", "style/topology/img/router_NEVERREACHED.svg");
			} else if($status == "CRITICAL") {
				createTopology($device_id, $name, $device_nature, "router", "style/topology/img/router_CRITICAL.svg");
			}
		}
	} catch (Exception $e) {
		logTofile(debug_dump($e, "************** processDevice ERROR **************"));
		echo prepare_json_response(FAILED, "FAILED", $context, true);
		exit;
	}
}

function getStatus($device_id) {
	$info = json_decode(_device_get_status($device_id), true);
	$status = $info ["wo_newparams"];
	
	if (empty($status) || $status == "") {
		return "Managed Entity with id " . $device_id . " was not found";
	} else {
		return $status;
	}
}

function get_customer_ref() {
	global $context;

	// read the customer and get the external reference
	$customer_db_id = substr($context ["UBIQUBEID"],4);
	$response = _customer_read_by_id($customer_db_id);
	$response = json_decode($response, true);
	if ($response['wo_status'] !== ENDED) {
		$response = json_encode($response);
		echo $response;
		exit;
	 }
		  
	$customer_ref = $response['wo_newparams']['externalReference'];
	return $customer_ref;
}

function calcMask($maskAdr) {
	$maskPart = explode(".", $maskAdr);
	$mask = "";
	foreach ($maskPart as $val) {
		$mask .= decbin(intval($val));
	}
	$result=0;
	$pos = strpos($mask, "0");
	if ($pos !== false) {
		$result = $pos;
	} else {
		$result = 32;
	}
	logTofile("calcMask ".$maskAdr." -> ".$result);
	return $result;
}

function getNetworkByAddressAndMask($address, $mask) {
	$addressParts = explode(".", $address);
	$networkAddressBinary = "";
	foreach ($addressParts as $val) {
		$morceauBinaire = decbin(intval($val));
		while (strlen($morceauBinaire) < 8) {
			$morceauBinaire = "0" . $morceauBinaire;
		}
		$networkAddressBinary .= $morceauBinaire;
	}
	
	$networkAddressBinary = substr($networkAddressBinary, 0, $mask);
	while (strlen($networkAddressBinary) < 32) {
		$networkAddressBinary .= "0";
	}
	
	$addressMasked = "";
	for($i = 0; $i < 32; $i = $i + 8) {
		$part = intval(substr($networkAddressBinary, $i, 8), 2);
		$addressMasked .= $part . ".";
	}
	
	$addressMasked = substr($addressMasked, 0, strlen($addressMasked) - 1);
	logTofile("getNetworkByAddressAndMask: ".$address." ".$mask." -> ".$addressMasked."\n");
	return $addressMasked;
}

?>
