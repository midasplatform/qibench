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





def addCondorDag(communicator, token, batchmaketaskid, dagname):
    """
    Adds a condor dag linked to the batchmaketask
    """
    parameters = dict()
    parameters['token'] = token
    parameters['batchmaketaskid'] = batchmaketaskid
    parameters['dagfilename'] = dagname
    parameters['outfilename'] = dagname + ".dagjob.dagman.out"
    #print parameters
    parameters['XDEBUG_SESSION_START'] = 'netbeans-xdebug'
    response = communicator.makeRequest('midas.batchmake.add.condor.dag', parameters)
    return response



if __name__ == "__main__":
  (scriptName, outputDir, taskId, dagName, jobId, jobName, returnCode) = sys.argv

  jobidNum = jobName[3:]
  cfgParams = loadConfig('config.cfg')

  log = open(os.path.join(outputDir,'postscript'+jobidNum+'.log'),'w')
  log.write('Condor Post Script log\n\nsys.argv:\n\n')
  log.write('\t'.join(sys.argv))

  log.write('\n\nConfig Params:\n\n')
  log.write('\n'.join(['\t'.join((k,v)) for (k,v) in cfgParams.iteritems()])) 


  interfaceMidas = apiMidas.Communicator (cfgParams['url'])
  token = interfaceMidas.loginWithApiKey(cfgParams['email'], cfgParams['apikey'], application='Default')
  log.write("\n\nLogged into midas, got token: "+token+"\n\n")

  dagResponse = addCondorDag(interfaceMidas, token, taskId, dagName)
  log.write("\n\nAdded a Condor Dag with response:"+str(dagResponse)+"\n\n")

  log.close()
  exit()#print cfgParamsOut
