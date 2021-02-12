from msa_sdk.variables import Variables
from msa_sdk.msa_api import MSA_API
from msa_sdk.order import Order
from msa_sdk.device import Device
from msa_sdk.orchestration import Orchestration
from datetime import datetime
from msa_sdk import constants
from msa_sdk import lookup
from msa_sdk import util
import ipaddress
import time
import re
import json
import xmltodict
import sys
import os


"""
The proposals of the task are:
   - Identify site ASN
   - Identify site prefixes and IP addresses
   - Identify PE and CE devices

"""


#New Variables object
TaskVariables = Variables()

#Add new variables
TaskVariables.add('site', var_type = 'String')
TaskVariables.add('status', var_type = 'String')

#Add vars to context
context = Variables.task_call(TaskVariables)

process_id                   = context['SERVICEINSTANCEID']
ipam_device_id               = context['ipam_device_id']
customer_vrf                 = context['customer_details']['vrf']
customer_name                = context['customer_name']
site                         = context['site']

ms_ipam_tenant           = context['ipam_ms_aliases']['IPAM Tenants']
ms_ipam_site             = context['ipam_ms_aliases']['IPAM Sites']
ms_ipam_device           = context['ipam_ms_aliases']['IPAM Devices']
ms_interface_connection  = context['ipam_ms_aliases']['IPAM Interface Connections']
ms_ipam_prefix           = context['ipam_ms_aliases']['IPAM IPv4 prefixes']
ms_ipam_avaliable_prefix = context['ipam_ms_aliases']['IPAM Available Prefixes']
ms_ipam_address          = context['ipam_ms_aliases']['IPAM IPv4 addresses']
ms_ipam_interface        = context['ipam_ms_aliases']['IPAM Interfaces']

#Variables to finish the task properlly
fail_comment = str()
success_comment = str()
fail_string = f'{{"wo_status": "FAIL", "wo_comment": "{fail_comment}"}}'
success_string = f'{{"wo_status": "ENDED", "wo_comment": "{success_comment}"}}'

#Create Orchestration object to update GUI dynamically
Orchestration = Orchestration(context['UBIQUBEID'])
async_update_list = (context['PROCESSINSTANCEID'], context['TASKID'], context['EXECNUMBER'])

#Create IPAM order object
IpamOrderObject = Order(ipam_device_id)
IpamOrderObject.command_synchronize(300)

objects_list = IpamOrderObject.command_objects_instances(ms_ipam_device)

#Retrieve information about CE device what is located on the site
counter = 0
ce_device_id = None
ce_device_name = None

#Find CE device name
Orchestration.update_asynchronous_task_details(*async_update_list, 'Retrieve information about CE device on the site... ')
while ce_device_name is None and counter < len(objects_list):
    util.log_to_process_file(process_id, 'DEBUG: {}'.format(IpamOrderObject.command_objects_instances_by_id(ms_ipam_device, objects_list[counter])))
    device_object = IpamOrderObject.command_objects_instances_by_id(ms_ipam_device, 
                                                                    objects_list[counter])[ms_ipam_device][objects_list[counter]]
    if device_object['role'] == 'CE' and device_object['site'] == site:
        ce_device_name = device_object['object_id']
    counter += 1

#Find device ID value
MsaLookup = lookup.Lookup()
MsaLookup.look_list_device_ids()

devices = json.loads(MsaLookup.content)

counter = 0
while ce_device_id is None or counter < len(devices):
    if devices[counter]['name'] == ce_device_name:
        ce_device_id = devices[counter]['id']
    counter += 1

Orchestration.update_asynchronous_task_details(*async_update_list, 'Retrieve information about CE device on the site... OK')
time.sleep(3)


Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove CE managed entity... ')
#Create CE device object
CeDeviceObject = Device(device_id = ce_device_id)
CeDeviceObject.delete()
Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove CE managed entity... OK')
time.sleep(3)

#Update IPAM
Orchestration.update_asynchronous_task_details(*async_update_list, 'Update IPAM... ')
ms_dict = {ms_ipam_device: 
                       {ce_device_name: {'object_id': ce_device_name,
                                    	 'status':   context['status'],
                                    	 'site': device_object['site'],
                                    	 'role': device_object['role'],
                                    	 'model': device_object['model']
                                    }
                        }
        }
IpamOrderObject.command_execute('UPDATE', ms_dict)
Orchestration.update_asynchronous_task_details(*async_update_list, 'Update IPAM... OK')
time.sleep(3)

success_comment = 'CE device has been moved to {} on site {}'.format(context['status'], context['site'])
del context['status']
del context['site']
print(IpamOrderObject.process_content('ENDED', success_comment, context, True))