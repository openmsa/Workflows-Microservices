<?php
require_once '/opt/fmc_repository/Process/Topology/Common/Topology_populate.php';
require_once '/opt/fmc_repository/Process/Topology/Common/Topology_common.php';
require_once '/opt/fmc_repository/Process/Reference/Common/Library/topology_rest.php';

function calculateDeviceTopology($deviceId, $name, $device_nature) {
	global $context;

    logTofile("*** calculateDeviceTopology  deviceId: ".$deviceId." name: ".$name."\n");

    $nodePlace = createTopology($deviceId, $name, $device_nature, "router", "style/topology/img/router_OK.svg");
	
	$instances_objname = "vlan";
	$array = array (
			$instances_objname
	);

	$import_result = json_decode(import_objects($deviceId, $array));
	if (isset($import_result->wo_newparams->vlan)) {
		$vlans = $import_result->wo_newparams->vlan;
		foreach ($vlans as $vlan) {

			if (!isset($vlan->name)) {
				$vlan->name = "unknown VLAN name. The Microservice VLAN requires a variable \'name\'";
			}
			$vlan_id = $vlan->object_id;
			logTofile("*** calculateDeviceTopology  vlan_id: ".$vlan_id."\n");

			if ($vlan_id == 1) {
				createTopologyNetwork($vlan_id, $vlan_id, "network", "");
			} else {
				createTopologyNetwork($vlan_id, $vlan_id, "network", "");
			}
			$context ['Nodes'] [$nodePlace] ["link"] [] ["id"] = $vlan_id;
		}
		
		logTofile(debug_dump($context ['Nodes'], "*** calculateDeviceTopology Nodes ***\n"));
		return false;
	} else {
		logTofile("WARNING: calculateDeviceTopology: managed entity ".$deviceId." has no vlan microservice attached");
	}
}
?>
