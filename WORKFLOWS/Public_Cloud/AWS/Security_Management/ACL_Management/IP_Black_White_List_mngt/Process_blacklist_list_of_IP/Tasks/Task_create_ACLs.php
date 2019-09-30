<?php

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';
require_once __DIR__.'/../../Common/common.php';

function list_args()
{
 
}

$device_id = substr($context['aws_region'], 3);

$ip_list_array = explode(' ', $context['ip_list_string']);

$ip_count = count($ip_list_array);
logToFile($ip_count. " IP address to blacklist");

$acl_to_create_count = $ip_count / 18;
logToFile($acl_to_create_count. " Network ACL to create");

$vpc_id = $context['vpc_id'];

$acl_list_string='';
$acl_count = 0;
for ($acl_count = 0; $acl_count <= $acl_to_create_count; $acl_count++) {
  $acl_id = create_acl($device_id, $vpc_id, $context);
  $acl_list_string = $acl_list_string.' '.$acl_id;
} 
$acl_list_string = trim($acl_list_string);
$acl_array = explode(' ', $acl_list_string);
logTofile(debug_dump($acl_array, "ACL CREATED"));
$context['acls'] = $acl_list_string;



task_success($acl_count.' Network ACL created in VPC '.$vpc_id );

?>