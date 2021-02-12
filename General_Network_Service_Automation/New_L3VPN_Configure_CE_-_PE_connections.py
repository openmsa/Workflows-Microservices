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
   - For each CE - PE link configure IP addresses
   - For each CE - PE link BGP peers
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

ipam_device_id               = context['ipam_device_id']
ce_device_id                 = context['ce_device_details']['device_id']
ce_device_name               = context['ce_device_details']['name']
customer_vrf                 = context['customer_details']['vrf']
customer_name                = context['customer_name']
site                         = context['site']
site_asn                     = context['site_asn']
asn                          = '65042'

ms_ipam_tenant           = context['ipam_ms_aliases']['IPAM Tenants']
ms_ipam_site             = context['ipam_ms_aliases']['IPAM Sites']
ms_ipam_device           = context['ipam_ms_aliases']['IPAM Devices']
ms_interface_connection  = context['ipam_ms_aliases']['IPAM Interface Connections']
ms_ipam_prefix           = context['ipam_ms_aliases']['IPAM IPv4 prefixes']
ms_ipam_avaliable_prefix = context['ipam_ms_aliases']['IPAM Available Prefixes']
ms_ipam_address          = context['ipam_ms_aliases']['IPAM IPv4 addresses']
ms_ipam_vrf              = context['ipam_ms_aliases']['IPAM VRFs']
ms_router_bgp_neighbor   = context['ipam_ms_aliases']['Router BGP neighbours']
ms_router_bgp_speaker    = context['ipam_ms_aliases']['Router BGP speaker']
ms_router_vrf            = context['ipam_ms_aliases']['Router VRF']
ms_router_interface      = context['ipam_ms_aliases']['Router interfaces']

#Variables to finish the task properlly
fail_comment = str()
success_comment = str()
fail_string = f'{{"wo_status": "FAIL", "wo_comment": "{fail_comment}"}}'
success_string = f'{{"wo_status": "ENDED", "wo_comment": "{success_comment}"}}'

#Create Orchestration object to update GUI dynamically
Orchestration = Orchestration(context['UBIQUBEID'])
async_update_list = (context['PROCESSINSTANCEID'], context['TASKID'], context['EXECNUMBER'])


IpamOrderObject = Order(ipam_device_id)

#Create CE device order object
CeOrderObject = Order(ce_device_id)
response = CeOrderObject.command_synchronize(300)
pe_order_list = list()

Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring CE device... ') 
for interface, connection in ce_connections.items():
    
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring CE device... IP address on link toward {}... '.format(connection['neighbour']['device'])) 
    #Create oreder object for PE
    pe_order_list.append(Order(connection['neighbour']['device_id']))
    response = pe_order_list[-1].command_synchronize(300)

    #Configure IP address on CE link
    ms_dict = {ms_router_interface: 
                           {interface: {'object_id': interface,
                                        'ip_addr':   connection['ip_address'].split('/')[0],
                                        'ip_prefix': connection['ip_address'].split('/')[1]
                                        }
                            }
            }
    CeOrderObject.command_execute('UPDATE', ms_dict)
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring CE device... IP address on link toward {}... OK'.format(connection['neighbour']['device']))
    CeOrderObject.command_synchronize(300)

    #Create BGP process on CE
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring CE device... BGP process on {}... '.format(ce_device_name))
    ms_dict = {ms_router_bgp_speaker: 
                           {site_asn: {'object_id': site_asn,
                                       'router_id': connection['ip_address'].split('/')[0]
                                        }
                            }
              }
    CeOrderObject.command_execute('CREATE', ms_dict)
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring CE device... BGP process on {}... OK'.format(ce_device_name))
    CeOrderObject.command_synchronize(300)
    
    #Create BGP neighbor on CE
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring CE device... BGP peer for {}... '.format(connection['neighbour']['device']))
    ms_dict = {ms_router_bgp_neighbor : 
                           {connection['neighbour']['ip_address'].split('/')[0].replace('.', '_'): {'object_id': connection['neighbour']['ip_address'].split('/')[0],
                                                                                                    'peer_group': customer_name.replace(' ', '_'),
                                                                                                    'asn': asn,
                                                                                                    'local_asn': site_asn,
                                                                                                    'address_family': {'0': {'afi': 'IPv4', 'safi': 'Unicast'}},
                                                                                                    'vrf': 'master'
                                                                                                     }
                            }
              }
    CeOrderObject.command_execute('CREATE', ms_dict)
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring CE device... BGP peer for {}... OK'.format(connection['neighbour']['device']))
    CeOrderObject.command_synchronize(300)

    #Check if customer VRF exists on PE
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring PE device... Configuring VRF... ')
    does_customer_vrf_exist = False
    counter = 0
    objects_list = pe_order_list[-1].command_objects_instances(ms_router_vrf)
    
    if customer_vrf not in objects_list:
      #Create custromer VRF on PE
      #Grab RD from IPAM
      vrf_object = IpamOrderObject.command_objects_instances_by_id(ms_ipam_vrf, 
                                                                  customer_vrf)[ms_ipam_vrf][customer_vrf]


      ms_dict = {ms_router_vrf: 
                       {customer_vrf: {'object_id': customer_vrf,
                                      'interfaces': {'0': {'iface_name': connection['neighbour']['interface']}},
                                      'rd': vrf_object['rd'],
                                      'route_target_export': vrf_object['rd'],
                                      'route_target_import': vrf_object['rd']
                                       }
                        }
                }
      pe_order_list[-1].command_execute('CREATE', ms_dict)
      Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring PE device... Configuring VRF... OK')
      pe_order_list[-1].command_synchronize(300)

    
    #Configure IP address on PE link
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring PE device... IP address on link toward {}... '.format(ce_device_name))
    ms_dict = {ms_router_interface: 
                       {connection['neighbour']['interface']: {'object_id': connection['neighbour']['interface'],
                                                               'ip_addr':   connection['neighbour']['ip_address'].split('/')[0],
                                                               'ip_prefix': connection['neighbour']['ip_address'].split('/')[1]
                                                               }
                        }
        }
    pe_order_list[-1].command_execute('UPDATE', ms_dict)
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring PE device... IP address on link toward {}... OK'.format(ce_device_name))
    pe_order_list[-1].command_synchronize(300)

    #Create BGP neighbor on PE
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring PE device... BGP peer for {}... '.format(ce_device_name))
    ms_dict = {ms_router_bgp_neighbor : 
                           {connection['ip_address'].split('/')[0].replace('.', '_'): {'object_id': connection['ip_address'].split('/')[0],
                                                                                       'peer_group': customer_name.replace(' ', '_'),
                                                                                       'asn': site_asn,
                                                                                       'local_asn': asn,
                                                                                       'address_family': {'0': {'afi': 'IPv4', 'safi': 'Unicast'}},
                                                                                       'vrf': customer_vrf
                                                                                      }
                            }
              }
    pe_order_list[-1].command_execute('CREATE', ms_dict)
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Configuring PE device... BGP peer for {}... OK'.format(ce_device_name))
    pe_order_list[-1].command_synchronize(300)

pretty_formatted_bar = list(12*'-')
for counter in range(0, 12):
    pretty_formatted_bar.insert(0,'*')
    pretty_formatted_bar.pop()
    Orchestration.update_asynchronous_task_details(*async_update_list, 'Verifying BGP session status... [{}]'.format(''.join(pretty_formatted_bar)))
    time.sleep(3)

#Verify connections
peering_list = list()
CeOrderObject.command_synchronize(300)
objects_list = CeOrderObject.command_objects_instances(ms_router_bgp_neighbor)
for neighbor in objects_list:
    neighbor_object = CeOrderObject.command_objects_instances_by_id(ms_router_bgp_neighbor, 
                                                                      neighbor)[ms_router_bgp_neighbor][neighbor]
    if neighbor_object['state'] == 'Established':
      peering_list.append(True)
    else:
      peering_list.append(False)

Orchestration.update_asynchronous_task_details(*async_update_list, 'Verifying BGP session status... [{}] OK'.format(str(pretty_formatted_bar)))


if len(peering_list) > 0:
  if all(peering_list):
    print(CeOrderObject.process_content('ENDED', 'All BGP sessions have been established', context, True))
  else:
    print(CeOrderObject.process_content('FAILED', 'One or more BGP sessions are not in Establshed stage', context, True))
else:
  print(CeOrderObject.process_content('FAILED', 'No BGP peer has been created', context, True))