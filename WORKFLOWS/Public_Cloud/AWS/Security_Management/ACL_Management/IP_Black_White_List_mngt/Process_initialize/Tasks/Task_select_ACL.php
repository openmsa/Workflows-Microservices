<?php

/**
 * This file is necessary to include to use all the in-built libraries of /opt/fmc_repository/Reference/Common
 */
require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

/**
 * List all the parameters required by the task
 */
function list_args()
{
  create_var_def('aws_region', 'String');
  create_var_def('vpc_id', 'String');
  create_var_def('url_IP_provider', 'String');
}

task_success('Task OK');

?>