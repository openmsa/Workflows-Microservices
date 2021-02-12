from msa_sdk.variables import Variables
from msa_sdk.msa_api import MSA_API
from msa_sdk.order import Order
from msa_sdk.device import Device
from msa_sdk.orchestration import Orchestration
from datetime import datetime
from msa_sdk import constants
from msa_sdk import util
import ipaddress
import time
import re
import json
import xmltodict
import sys
import os

"""
The proposals of the tasks are:
   - Remove site prefixes from IPAM
   - Remove site addresses from IPAM
   - Remove ASN from IPAM
"""


#New Variables object
TaskVariables = Variables()

#Add new variables
TaskVariables.add('site', var_type = 'String')

#Add vars to context
context = Variables.task_call(TaskVariables)
process_id                   = context['SERVICEINSTANCEID']
_ce_connections              = context['ce_connections']

#Because bug
ce_connections = dict()
for interface, connection in _ce_connections.items():
    ce_connections[interface.replace('DOT', '.')] = connection

site                         = context['site']
ipam_device_id               = context['ipam_device_id']
ce_device_id                 = context['ce_device_details']['device_id']
ce_device_name               = context['ce_device_details']['name']
ce_internal_interface_name   = context['ce_device_details']['internal_interface_name']
site_asn                     = context['site_asn']
customer_vrf                 = context['customer_details']['vrf']



ms_ipam_tenant           = context['ipam_ms_aliases']['IPAM Tenants']
ms_ipam_site             = context['ipam_ms_aliases']['IPAM Sites']
ms_ipam_device           = context['ipam_ms_aliases']['IPAM Devices']
ms_interface_connection  = context['ipam_ms_aliases']['IPAM Interface Connections']
ms_ipam_prefix           = context['ipam_ms_aliases']['IPAM IPv4 prefixes']
ms_ipam_avaliable_prefix = context['ipam_ms_aliases']['IPAM Available Prefixes']
ms_ipam_address          = context['ipam_ms_aliases']['IPAM IPv4 addresses']
ms_ipam_vrf              = context['ipam_ms_aliases']['IPAM VRFs']

#Variables to finish the task properlly
fail_comment = str()
success_comment = str()
fail_string = f'{{"wo_status": "FAIL", "wo_comment": "{fail_comment}"}}'
success_string = f'{{"wo_status": "ENDED", "wo_comment": "{success_comment}"}}'

#Create Orchestration object to update GUI dynamically
Orchestration = Orchestration(context['UBIQUBEID'])
async_update_list = (context['PROCESSINSTANCEID'], context['TASKID'], context['EXECNUMBER'])


IpamOrderObject = Order(ipam_device_id)
IpamOrderObject.command_synchronize(300)


#Remove site prefixes
for prefix in context['site_prefixes_list']:
  Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove site {} prefix {} from IPAM... '.format(site, prefix['object_id']))
  ms_dict = {ms_ipam_prefix: 
                           {prefix['object_id'].replace('.', '_'): {'object_id': prefix['object_id']}
                           }
            }
  IpamOrderObject.command_execute('DELETE', ms_dict)
  Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove site {} prefix {} from IPAM... OK'.format(site, prefix['object_id']))
  time.sleep(3)
IpamOrderObject.command_synchronize(300)


#Remove site IP addresses
for ip_address in context['site_address_list']:
  Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove site {} address {} from IPAM... '.format(site, ip_address['object_id']))
  ms_dict = {ms_ipam_address: 
                           {ip_address['object_id'].replace('.', '_'): {'object_id': ip_address['object_id']}
                           }
            }
  IpamOrderObject.command_execute('DELETE', ms_dict)
  Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove site {} address {} from IPAM... OK'.format(site, ip_address['object_id']))
  time.sleep(3)
IpamOrderObject.command_synchronize(300)

Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove ASN for the site from IPAM... ')  
site_object = IpamOrderObject.command_objects_instances_by_id(ms_ipam_site, site)[ms_ipam_site][site]
ms_dict = {ms_ipam_site: {site_object['object_id']: {'object_id': site_object['object_id'],
                                                     'id': site_object['id'],
                                                     'slug': site_object['slug'],
                                                     'tenant': site_object['tenant'],
                                                     'status': site_object['status']
                                                     }
                          }
          }
IpamOrderObject.command_execute('UPDATE', ms_dict)
time.sleep(3)
Orchestration.update_asynchronous_task_details(*async_update_list, 'Remove ASN for the site from IPAM... ') 

IpamOrderObject.command_synchronize(300)

#Clean up context
del context['ce_connections']
del context['ce_device_details']
del context['site']
del context['site_asn']

success_comment = f'L3VPN service has been removed successfully from site {site}'
print(IpamOrderObject.process_content('ENDED', success_comment, context, True))