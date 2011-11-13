#! /usr/bin/python
import os
import sys
import pydas.communicator as apiMidas
import pydas.exceptions as pydasException

# Load configuration file
def loadConfig(filename):
   try:
     configfile = open(filename, "r")
     ret = dict()
     for x in configfile:
       x = x.strip()
       if not x: continue
       cols = x.split()
       ret[cols[0]] = cols[1]
     return ret
   except Exception, e: raise


  
class DicomUploader(object):
    """
    Class for creating a Dicom Item from an input directory of dicom files.
    """

    def __init__(self, configFile):
        """
        Constructor
        """
        self.cfgParams = loadConfig('config.cfg')
        self.interfaceMidas = apiMidas.Communicator (self.cfgParams['url'])
        self.token = self.interfaceMidas.loginWithApiKey(self.cfgParams['email'], self.cfgParams['apikey'], application='Default')

    def createDicomItem(self, itemName, itemDescription, inputDir, parentFolderId):
        # create the item
        item = self.interfaceMidas.createItem(self.token, itemName, parentFolderId, itemDescription)
        itemId = item['item_id']
        # find all the files in the inputDir
        print inputDir
        files = os.listdir(inputDir)
        numfiles = len(files)
        for (ind, filename) in enumerate(files):
          filepath = os.path.join(inputDir, filename)
          print "processing: ",filepath,ind,"out of",numfiles
          # get an upload token for this file 
          uploadToken = self.interfaceMidas.generateUploadToken(self.token, itemId, filepath)
          length = os.path.getsize(filepath)
          uploadResponse = self.interfaceMidas.performUpload(uploadToken['token'], filename, length, filepath, None, None, itemId, 'head')

if __name__ == "__main__":
    #python create_dicom_item.py config.cfg 39 case39 /home/mgrauer/dev/buckler_nist/39/ 230
    (scriptName, configFile, itemName, itemDescription, inputDir, parentFolderId) = sys.argv
    os.chdir(sys.path[0])
    dicomUploader = DicomUploader(configFile)
    dicomUploader.createDicomItem(itemName, itemDescription, inputDir, parentFolderId)
