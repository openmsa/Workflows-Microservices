<?php


require_once '/opt/fmc_repository/Process/Reference/Common/common.php';
require_once __DIR__.'/../../Common/common.php';

function list_args()
{
}

$device_id = substr($context['aws_region'], 3);
$vpc_id = $context['vpc_id'];

if (isset($context['acls'])) {

  $response = synchronize_objects_and_verify_response($device_id);
  $response = _device_read_by_id($device_id);
  $response = json_decode($response, true);
  if ($response['wo_status'] !== ENDED) {
    $response = json_encode($response);
    echo $response;
    exit;
  }

  $acl_list_string = $context['acls'];
  $acl_list = explode(" ", $acl_list_string);
  $acl_list_size = count($acl_list);
  foreach ($acl_list as $acl_id) {
    delete_acl($device_id, $acl_id, $context);
  }
  task_success($acl_list_size . ' Network ACL deleted from VPC '.$vpc_id);

} else {
  //logToFile("no Network ACL to delete in VPC ".$vpc_id);
  task_success("no Network ACL to delete in VPC ".$vpc_id);
}



?>