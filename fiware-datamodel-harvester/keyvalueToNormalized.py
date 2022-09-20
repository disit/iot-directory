 #Snap4City: IoT-Directory
 # Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence

  #This program is free software; you can redistribute it and/or
  #modify it under the terms of the GNU General Public License
  #as published by the Free Software Foundation; either version 2
  #of the License, or (at your option) any later version.
  #This program is distributed in the hope that it will be useful,
  #but WITHOUT ANY WARRANTY; without even the implied warranty of
  #MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  #GNU General Public License for more details.
  #You should have received a copy of the GNU General Public License
  #along with this program; if not, write to the Free Software
  #Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
  
"""
Converts an NGSI v2 Simplified Representation (a.k.a. keyValues)
into a Normalized Representation
Copyright (c) 2018 FIWARE Foundation e.V.
Author: José Manuel Cantera
"""


def keyValues_2_normalized(entity):
    out = {}
    a = entity.keys()
    for key in entity:
        if key == 'id' or key == 'type':
            out[key] = entity[key]
            continue

        out[key] = {
            'value': entity[key]
        }

        if key == 'location':
            out[key]['type'] = 'geo:json'

        if key.startswith('date'):
            out[key]['type'] = 'DateTime'

        if key == 'address':
            out[key]['type'] = 'PostalAddress'

        if key.startswith('ref'):
            out[key]['type'] = 'Relationship'

        if key.startswith('has'):
            out[key]['type'] = 'Relationship'

    return out
