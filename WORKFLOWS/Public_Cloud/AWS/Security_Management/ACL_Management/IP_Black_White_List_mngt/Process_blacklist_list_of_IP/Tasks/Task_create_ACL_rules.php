<?php
require_once '/opt/fmc_repository/Process/Reference/Common/common.php';
require_once __DIR__ . '/../../Common/common.php';

function list_args() {
}

$device_id = substr ( $context ['aws_region'], 3 );

$ip_list_string = $context ['ip_list_string'];
$ip_list_array = explode ( ' ', $ip_list_string );

$acl_list_string = $context ['acls'];
$acl_list_array = explode ( ' ', $acl_list_string );

logToFile ( debug_dump ( $acl_list_array, "CREATE RULE: ACL ARRAY" ) );
logToFile ( debug_dump ( $ip_list_array, "CREATE RULE: IP ARRAY" ) );

$ip_index=0;
$ip_count=count($ip_list_array);
$acl_index=0;

for ($ip_index = 0; $ip_index < $ip_count; $ip_index++) { 

  $ip=$ip_list_array[$ip_index];
  $rule_index = ($ip_index % 18)+1;

  logToFile("current IP: " . $ip .  " at index: " . $ip_index);
  logToFile("acl_index: " . $acl_index);
  logToFile("rule_index: " . $rule_index);

  create_acl_entry ( $device_id, $acl_list_array[$acl_index], $rule_index, $ip );

  if ($ip_index > 0 && $rule_index % 18 ==0) {
    $acl_index++;
  }
}

task_success ( $ip_index . ' rules created in ' . ($acl_index+1) . ' Network ACL' );
exit;

?>