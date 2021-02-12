from msa_sdk.variables import Variables
from msa_sdk.msa_api import MSA_API
from msa_sdk.order import Order
from msa_sdk.device import Device
from msa_sdk.orchestration import Orchestration
from datetime import datetime
from msa_sdk import constants
from msa_sdk import util
import time
import re
import json
import xmltodict
import sys


"""
The proposal of the task is activate ME and attach deployment settings
When the task has been finished, details about CE device removed from context.
Ops could start to work with another CE

"""

#New Variables object
TaskVariables = Variables()

#Add new variables
TaskVariables.add('site', var_type = 'String')

#Add vars to context
context = Variables.task_call(TaskVariables)

process_id                   = context['SERVICEINSTANCEID']
ce_device_id                 = context['ce_device_details']['device_id']
ce_device_external_reference = context['ce_device_details']['external_reference']
ce_device_name 				 = context['ce_device_details']['ce_device_name']
ce_local_context             = context['ce_device_details']['local_context']
ms_ipam_device 				 = context['ipam_ms_aliases']['IPAM Devices']
customer_name                = context['customer_name']
ipam_device_id               = context['ipam_device_id']

#Variables to finish the task properlly
fail_comment = str()
success_comment = str()
fail_string = f'{{"wo_status": "FAIL", "wo_comment": "{fail_comment}"}}'
success_string = f'{{"wo_status": "ENDED", "wo_comment": "{success_comment}"}}'

#Create Orchestration object to update GUI dynamically
Orchestration = Orchestration(context['UBIQUBEID'])
async_update_list = (context['PROCESSINSTANCEID'], context['TASKID'], context['EXECNUMBER'])


#IPAM order object
IpamOrderObject = Order(ipam_device_id)

#Create CE device object
CeDeviceObject = Device(device_id = ce_device_id,
                        device_external = ce_device_external_reference)
pretty_formatted_bar = list(10*'-')
Orchestration.update_asynchronous_task_details(*async_update_list, 'Provisioning of CE device... [{}]'.format(''.join(pretty_formatted_bar)))

#If the device mgmt interface is REST-based, add required configuration variables
if 'rest' in ce_local_context['interface'].lower():
    for variable, value in ce_local_context['msa_specific']['rest_headers'].items():
        CeDeviceObject.create_configuration_variable(name = variable, value = value)

#Attach configuration profile
Orchestration.update_asynchronous_task_details(*async_update_list, 'Attaching configuration deployment settings profile... ') 
CeDeviceObject.profile_attach(ce_local_context['msa_specific']['deployment_settings_ref'])
Orchestration.update_asynchronous_task_details(*async_update_list, 'Attaching configuration deployment settings profile... OK')
time.sleep(3) 

#Provision device
CeDeviceObject.initial_provisioning()

#Wait until provisioning is done
while CeDeviceObject.provision_status()['status'] != 'OK':
    pretty_formatted_bar.insert(0,'*')
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Provisioning of CE device... [{}]'.format(''.join(pretty_formatted_bar)))
    time.sleep(5)

for counter in range(0, 10):
    pretty_formatted_bar.insert(0,'*')
    pretty_formatted_bar.pop()
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Provisioning of CE device... [{}]'.format(''.join(pretty_formatted_bar)))
    time.sleep(3)

Orchestration.update_asynchronous_task_details(*async_update_list, 'Provisioning of CE device... [{}] OK'.format(''.join(pretty_formatted_bar)))
time.sleep(3) 


device_object = IpamOrderObject.command_objects_instances_by_id(ms_ipam_device, 
                                                                ce_device_name)[ms_ipam_device][ce_device_name.replace('.', '_')]

Orchestration.update_asynchronous_task_details(*async_update_list, 'Marking device {} as "ACTIVE" in IPAM system...'.format(ce_device_name))
#Mark the device as Active in IPAM
ms_dict = {ms_ipam_device: 
                       {ce_device_name: {'object_id': ce_device_name,
                                    	 'status':   'active',
                                    	 'site': device_object['site'],
                                    	 'role': device_object['role'],
                                    	 'model': device_object['model']
                                    }
                        }
        }
IpamOrderObject.command_execute('UPDATE', ms_dict)
Orchestration.update_asynchronous_task_details(*async_update_list, 'Marking device {} as "ACTIVE" in IPAM system... OK'.format(ce_device_name))
time.sleep(3)


#Clean up context
context.pop('ce_device_details')


success_comment = 'New CE device has been proviosioned successfully'
print(CeDeviceObject.process_content('ENDED', success_comment, context, True))
