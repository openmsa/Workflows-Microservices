<?php
require_once '/opt/fmc_repository/Process/Topology/Common/Topology_populate.php';
require_once '/opt/fmc_repository/Process/Topology/Common/Topology_common.php';
require_once '/opt/fmc_repository/Process/Reference/Common/Library/topology_rest.php';
require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

function calculateDeviceTopology($deviceId, $name, $device_nature) {
	global $context;

    logTofile("*** calculateDeviceTopology  deviceId: ".$deviceId." name: ".$name."\n");
    $customer_ref = get_customer_ref();
    $nodePlace = createTopology($deviceId, $name, $device_nature, "router", "style/topology/img/router_OK.svg");
    if (strpos($name, 'PE') !== false) {	
	$instances_objname = "vrf";
	$array = array (
			$instances_objname
	);

	$response = json_decode(import_objects($deviceId, array('vrf')), True);
	if (array_key_exists('vrf', $response['wo_newparams'])) {
		$object_ids_array = $response['wo_newparams']['vrf'];
		foreach ($object_ids_array as $vrf => $details) {
                        if (array_key_exists('rd', $details)) {
				$vrf_rd = $details['rd'];
				logTofile("*** calculateDeviceTopology  vrf_rd: ".$vrf_rd."\n");
				//createTopologyNetwork($vrf_rd, $vrf_name, "network", "");
				$vrf_node_position = _topology_exist_object_this_instance($vrf_rd);
				$vrf_name = $context ['Nodes'] [$vrf_node_position] ["name"];
				$context ['Nodes'] [$nodePlace] ["links"] [] = $vrf_name;
			}
		}
		
		return false;
	} else {
		logTofile("WARNING: calculateDeviceTopology: managed entity ".$deviceId." has no vrf microservice attached");
	}

    }

    if (strpos($name, 'CE') !== false) {
	$pe_array = array();
	$list = json_decode(_lookup_list_devices_by_customer_reference($customer_ref), True);
        foreach ($list['wo_newparams'] as $device => $details) {
		if (strpos($details['name'], 'PE') !== false) {
			$pe_array[$details['id']] = array('name' => $details['name'], 'ip' => array());	
		}

	}
	foreach ($pe_array as $pe_id =>&$ip_list) {
		$response = json_decode(import_objects($pe_id, array('interfaces')), True);
		if (array_key_exists('interfaces', $response['wo_newparams'])) {
			$object_ids_array = $response['wo_newparams']['interfaces'];
			foreach ($object_ids_array as $interface => $details) {
				if (array_key_exists('ip_addr', $details)) {
					$ip_list['ip'][] = $details['ip_addr'];
				}
			}
		}
	}
	unset($pe_id, $ip_list);
        $response = json_decode(import_objects($deviceId, array('bgp_neighbour')), True);
        if (array_key_exists('bgp_neighbour', $response['wo_newparams'])) {
		$object_ids_array = $response['wo_newparams']['bgp_neighbour'];
		foreach ($object_ids_array as $neighbour => $details) {
			$neighbour_ip = $details['object_id'];
			foreach ($pe_array as $pe_id =>$ip_list) {
				if (in_array($neighbour_ip, $ip_list['ip'])) {
					$context ['Nodes'] [$nodePlace] ["links"] [] = $ip_list['name'];
				}
			} 	
		}
	}
    }
}
?>
