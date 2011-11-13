#! /usr/bin/python
import re
import os
import sys
import time
import pydas.communicator as apiMidas
import pydas.exceptions as pydasException
import uuid
import json
import shutil
from zipfile import ZipFile, ZIP_DEFLATED
from subprocess import Popen, PIPE, STDOUT
from contextlib import closing

# Load configuration file
def loadConfig(filename):
   try:
     configfile = open(filename, "r")
     ret = dict()
     for x in configfile:
       x = x.strip()
       if not x: continue
       cols = x.split()
       print cols
       ret[cols[0]] = cols[1]
     return ret
   except Exception, e: raise




def parseVolumeMeasurement(filepath):
  lines = open(filepath, 'r')
  volume = "n/a"
  #  pattern = re.compile("Volume of segmentation (mm^3) = 465.686
  for line in lines:
    line = line.strip()
    if line.find("Volume of segmentation (mm^3) = ") > -1:
      cols = line.split()
      volume = cols[-1]
  lines.close()
  return volume


def addRunItemScalarvalue(communicator, token, qibenchrunitemid, name, value):
    """
    Gets the default api key given an email and password
    """
    parameters = dict()
    parameters['token'] = token
    parameters['qibenchrunitemid'] = qibenchrunitemid
    parameters['name'] = name
    parameters['value'] = value
    print parameters
    response = communicator.makeRequest('midas.qibench.runitemscalarvalue.add', parameters)
    return response

if __name__ == "__main__":
  (scriptName, outputDir, outputFolderId, itemName, outputAim, outputImage, outputMesh, jobname, jobid, returncode) = sys.argv
  #python qibench_condor_postscript.py /home/mgrauer/dev/buckler_nist/39out 229 39_4 lstk_39_4_V_lstk.xml lstk_39_4_V_lstk.mha lstk_39_4_V_lstk.stl bmGrid.1.out.txt
  #Set directory location
#itemname, outputaim, outputimage, outputmesh, jobname, condorjobid

#jfrom jobname, can parse jobX where x is id, then from that can parse out image volume


  #os.chdir(sys.path[0])
  #outfile = open('myout.txt','w')
  #outfile.write('\n'.join(sys.argv))
  #outfile.close()
  #exit()

 
  cfgParams = loadConfig('config.cfg')
  #print cfgParams
  
  interfaceMidas = apiMidas.Communicator (cfgParams['url'])
  token = interfaceMidas.loginWithApiKey(cfgParams['email'], cfgParams['apikey'], application='Default')

  jobidNum = jobname[3:]
  exeOutput = 'bmGrid.' + jobidNum + '.out.txt' 
  print exeOutput
  #exit()
  #bmGrid.1.out.txt
  volume = parseVolumeMeasurement(outputDir + '/' + exeOutput)
  print volume
  # HACK need some error handling if no file
  # also look at returncode value
 

  #exit()
  #addRunItemScalarvalue(interfaceMidas, token, 1, 'CaseReading', volume)
  #exit()
  #qibench_run_item_id 


 
  # also get the revision and set to head

  # create the item
  item = interfaceMidas.createItem(token, itemName, outputFolderId, 'pydas created')
  itemId = item['item_id']
  #itemId = 190 # hardcode for now
  # upload the bitstreams to the item
  for filename in [outputAim, outputImage, outputMesh]:
    filePath = outputDir + '/' + filename
    uploadToken = interfaceMidas.generateUploadToken(token, itemId, filename)
    length = os.path.getsize(filePath)
    print uploadToken
    uploadResponse = interfaceMidas.performUpload(uploadToken['token'], filename, length, filePath, None, None, itemId, 'head')
