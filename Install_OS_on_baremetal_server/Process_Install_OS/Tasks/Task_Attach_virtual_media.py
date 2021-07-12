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
  - Attach virtual media
"""


#Create Variables() object and retrieve useful variables
TaskVariables = Variables()
context = Variables.task_call(TaskVariables)

#Variables to finish the task properlly
fail_comment = str()
success_comment = str()
fail_string = f'{{"wo_status": "FAIL", "wo_comment": "{fail_comment}"}}'
success_string = f'{{"wo_status": "ENDED", "wo_comment": "{success_comment}"}}'
process_id = context['SERVICEINSTANCEID']

#Create Orchestration object to update GUI dynamically
Orchestration = Orchestration(context['UBIQUBEID'])
async_update_list = (context['PROCESSINSTANCEID'], context['TASKID'], context['EXECNUMBER'])


#Create order object
ServerOrderObject = Order(context['device_id'])

#Attach virtual media
Orchestration.update_asynchronous_task_details(*async_update_list, 'Attach ISO to virtual CD... ')
objects_list = ServerOrderObject.command_objects_instances(context['ms_alias']['ms_virtual_media'])

virtual_media_object = None
for virtual_media in objects_list:
    if virtual_media == 'CD':
        virtual_media_object = ServerOrderObject.command_objects_instances_by_id(context['ms_alias']['ms_virtual_media'], 
                                                                                 virtual_media)[context['ms_alias']['ms_virtual_media']][virtual_media]
if virtual_media_object is not None:
    #Check if virtual media already attached
    if virtual_media_object['image_source'] != 'NotConnected':
        ms_dict = {context['ms_alias']['ms_virtual_media']: {virtual_media_object['object_id']: {}}}
        ServerOrderObject.command_execute('DELETE', ms_dict)
        time.sleep(10)
        ServerOrderObject.command_synchronize(300)

    ms_dict = {context['ms_alias']['ms_virtual_media']: 
                           				   {virtual_media_object['object_id']: {'object_id': virtual_media_object['object_id'],
                                       					                        'image_name': '',
                                                                                'image_source': context['iso_uri'],
                                                                                'image': ''
                                                        }
                                           }
              }
    ServerOrderObject.command_execute('UPDATE', ms_dict)

    #Check if virtual media is attached
    is_attached = False
    counter = 10
    while not is_attached and counter > 0:
        time.sleep(10)
        ServerOrderObject.command_synchronize(300)
        objects_list = ServerOrderObject.command_objects_instances(context['ms_alias']['ms_virtual_media'])
        virtual_media_object = None
        for virtual_media in objects_list:
            if virtual_media == 'CD':
                virtual_media_object = ServerOrderObject.command_objects_instances_by_id(context['ms_alias']['ms_virtual_media'], 
                                                                                         virtual_media)[context['ms_alias']['ms_virtual_media']][virtual_media]
        if virtual_media_object['image'] == context['iso_uri']:
            is_attached = True

        counter -= 1
if is_attached:
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Attach ISO to virtual CD... OK')
    success_comment = 'Virtual media has been attached sucessfully.'
    print(MSA_API.process_content('ENDED', success_comment , context, True))
else:
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Attach ISO to virtual CD... FAILED')
    fail_comment = 'Virtual media has not been attached by some reason. Stop the automation'
    print(MSA_API.process_content('FAIL', success_comment , context, True))