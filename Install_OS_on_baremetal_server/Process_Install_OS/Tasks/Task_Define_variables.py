from msa_sdk.variables import Variables
from msa_sdk.msa_api import MSA_API
import json
import re

"""
The proposals of the task are:
  - Get server ME and OS image HTTP URI as inputs
  - Define MS aliases
"""


#New Variables object
TaskVariables = Variables()

#Add new variables
TaskVariables.add('iso_uri', var_type = 'String')
TaskVariables.add('device_id', var_type = 'Device')

#Add vars to context
context = Variables.task_call(TaskVariables)
context['device_id'] = re.match('^\D+?(\d+?)$', context['device_id']).group(1)


#Import microservice alias list
context['ms_alias'] = {"ms_virtual_media" : "redfish_virtual_media",
                       "ms_one_time_boot" : "redfish_one_time_boot_virtual_cd",
                       "ms_power_actions" : "redfish_server_actions",
                       "ms_server_general": "redfish_server_general"
					  }


#Finish the task correctlly
result = MSA_API.process_content('ENDED', 'All variables have been defined successfully', context, True)
print(result)
