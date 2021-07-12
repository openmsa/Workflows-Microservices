from msa_sdk.variables import Variables
from msa_sdk.msa_api import MSA_API
from msa_sdk.order import Order
from msa_sdk.orchestration import Orchestration
from datetime import datetime
from msa_sdk import constants
from msa_sdk import util
import json
import sys
import time


"""
The proposals of the tasks are:
  - Check whether is the server turned on
  - If yes - shutdown the server
"""


#Create Variables() object and retrieve useful variables
TaskVariables = Variables()
context = Variables.task_call(TaskVariables)

#Variables to finish the task properlly
fail_comment = str()
success_comment = str()
fail_string = f'{{"wo_status": "FAIL", "wo_comment": "{fail_comment}"}}'
success_string = f'{{"wo_status": "ENDED", "wo_comment": "{success_comment}"}}'

#Create Orchestration object to update GUI dynamically
Orchestration = Orchestration(context['UBIQUBEID'])
async_update_list = (context['PROCESSINSTANCEID'], context['TASKID'], context['EXECNUMBER'])
process_id = context['SERVICEINSTANCEID']

#Create order object
ServerOrderObject = Order(context['device_id'])
ServerOrderObject.command_synchronize(300)

#Retrieve info about tenants
Orchestration.update_asynchronous_task_details(*async_update_list, 'Checking server power state... ')
objects_list = ServerOrderObject.command_objects_instances(context['ms_alias']['ms_server_general'])
server_object = ServerOrderObject.command_objects_instances_by_id(context['ms_alias']['ms_server_general'], 
                                                                  objects_list[0])[context['ms_alias']['ms_server_general']][objects_list[0]]
server_object_id   = server_object['object_id']
server_power_state = server_object['power_state']
Orchestration.update_asynchronous_task_details(*async_update_list, 'Checking server power state... {}'.format(server_power_state))

if server_power_state.lower() != 'off':
    #Turn off server
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Turn off the server... ')
    ms_dict = {context['ms_alias']['ms_power_actions']: 
                           				   {'ForceOff': {'object_id': 'ForceOff',
                                       					 'action': 'ForceOff'
                                                        }
                                           }
              }
    ServerOrderObject.command_execute('CREATE', ms_dict)
    is_turned_off = False
    counter = 10
    while not is_turned_off and counter > 0:
        time.sleep(10)
        ServerOrderObject.command_synchronize(300)
        objects_list =  ServerOrderObject.command_objects_instances(context['ms_alias']['ms_server_general'])
        server_object = ServerOrderObject.command_objects_instances_by_id(context['ms_alias']['ms_server_general'], 
                                                        objects_list[0])[context['ms_alias']['ms_server_general']][objects_list[0]]
        server_object_id   = server_object['object_id']
        server_power_state = server_object['power_state']

        if server_power_state.lower() == 'off':
            is_turned_off = True
        
        counter -= 1
    if is_turned_off:
        Orchestration.update_asynchronous_task_details(*async_update_list, 'Turn off the server... OK')
        success_comment = 'Server has been turned off successfully.'
        print(MSA_API.process_content('ENDED', success_comment , context, True))
    else:
        Orchestration.update_asynchronous_task_details(*async_update_list, 'Turn off the server... FAILED')
        fail_comment = 'Server has not been turned off by some reason. Stop the automation'
        print(MSA_API.process_content('FAIL', success_comment , context, True))
else:
    success_comment = 'Server is already in power off state'
    print(MSA_API.process_content('ENDED', success_comment , context, True))