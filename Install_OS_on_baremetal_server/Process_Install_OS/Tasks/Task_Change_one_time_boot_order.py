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
  - Change one-time boot order
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


#Create order object
ServerOrderObject = Order(context['device_id'])

#Retrieve info about tenants
Orchestration.update_asynchronous_task_details(*async_update_list, 'Change one-time boot order... ')
ms_dict = {context['ms_alias']['ms_one_time_boot']: 
                           			  {'1': {'object_id': '1'
                                            }
                                      }
          }
ServerOrderObject.command_execute('CREATE', ms_dict)
Orchestration.update_asynchronous_task_details(*async_update_list, 'Change one-time boot order...  OK')
success_comment = 'Virtual media has been attached sucessfully.'
print(MSA_API.process_content('ENDED', success_comment , context, True))