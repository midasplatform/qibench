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
       #print cols
       ret[cols[0]] = cols[1]
     return ret
   except Exception, e: raise




def parseVolumeMeasurement(filepath):
  if not os.path.exists(filepath):
    return None
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
    Adds a scalar value to the runitem
    """
    parameters = dict()
    parameters['token'] = token
    parameters['qibenchrunitemid'] = qibenchrunitemid
    parameters['name'] = name
    parameters['value'] = value
    #print parameters
    response = communicator.makeRequest('midas.qibench.runitemscalarvalue.add', parameters)
    return response



def setRunItemOutputItemId(communicator, token, qibenchrunitemid, outputItemId):
    """
    Sets the outputItemId on the runitem
    """
    parameters = dict()
    parameters['token'] = token
    parameters['qibenchrunitemid'] = qibenchrunitemid
    parameters['outputitemid'] = outputItemId
    print parameters
    response = communicator.makeRequest('midas.qibench.runitem.outputitemid.set', parameters)
    return response









if __name__ == "__main__":
  (scriptName, outputDir, runId, outputFolderId, runItemId, itemName, outputAim, outputImage, outputMesh, jobname, jobid, returncode) = sys.argv
  jobidNum = jobname[3:]
  cfgParams = loadConfig('config.cfg')

  log = open(os.path.join(outputDir,'postscript'+jobidNum+'.log'),'w')
  log.write('Condor Post Script log\n\nsys.argv:\n\n')
  log.write('\t'.join(sys.argv))

  log.write('\n\nConfig Params:\n\n')
  log.write('\n'.join(['\t'.join((k,v)) for (k,v) in cfgParams.iteritems()])) 


  interfaceMidas = apiMidas.Communicator (cfgParams['url'])
  token = interfaceMidas.loginWithApiKey(cfgParams['email'], cfgParams['apikey'], application='Default')
  log.write("\n\nLogged into midas, got token: "+token+"\n\n")

  exeOutput = 'bmGrid.' + jobidNum + '.out.txt' 
  exeOutputPath = os.path.join(outputDir, exeOutput)
  log.write("\n\nParsing output file: "+exeOutputPath+"\n\n")
  volume = parseVolumeMeasurement(exeOutputPath)
  log.write("\n\nvolume from output file:"+volume+"\n\n")
  

  response = addRunItemScalarvalue(interfaceMidas, token, runItemId, 'CaseReading', volume)
  log.write("\n\nCalled addRunItemScalarvalue("+runItemId+", "+"CaseReading"+", "+volume+") with response:"+str(response)+"\n\n")

  # create the item
  item = interfaceMidas.createItem(token, itemName, outputFolderId, 'pydas created')
  itemId = item['item_id']
  log.write("\n\nCalled createItem, got itemId:"+str(itemId)+"\n\n")
  


  #itemId = 190 # hardcode for now
  # set the outputitemid in the runitem
  response = setRunItemOutputItemId(interfaceMidas, token, runItemId, itemId)
  log.write("\n\nCalled setRunItemOutputItemId with response:"+str(response)+"\n\n")


 
  #print cfgParams
  

  #exit()
  #bmGrid.1.out.txt
  # HACK need some error handling if no file
  # also look at returncode value
 

  #exit()
  #exit()
  #qibench_run_item_id 


 
  # also get the revision and set to head

  # upload the bitstreams to the item
  for filename in [outputAim, outputImage, outputMesh]:
    filePath = outputDir + '/' + filename
    uploadToken = interfaceMidas.generateUploadToken(token, itemId, filename)
    log.write("\n\nGot uploadToken:"+str(uploadToken)+" for filename "+filename+"\n\n")
    length = os.path.getsize(filePath)
    #print uploadToken
    uploadResponse = interfaceMidas.performUpload(uploadToken['token'], filename, length, filePath, None, None, itemId, 'head')
    log.write("\n\nGot uploadResponse:"+str(uploadResponse)+" for filename "+filename+"\n\n")
  
  log.close()
  exit()#print cfgParamsOut
