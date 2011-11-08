<?php
/*=========================================================================
MIDAS Server
Copyright (c) Kitware SAS. 20 rue de la Villette. All rights reserved.
69328 Lyon, FRANCE.

See Copyright.txt for details.
This software is distributed WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the above copyright notices for more information.
=========================================================================*/
?>
<?php
/** Qibench_ExecuteComponent */
class Qibench_ExecuteComponent extends AppComponent
  {

    
/*  protected $kwBatchmakeComponent;
  protected $applicationConfig;

  /* * set up tests* /
  public function setUp()
    {
    $this->setupDatabase(array('default'));
    $this->_models = array('User');
    $this->enabledModules = array('batchmake');
    parent::setUp();
    if(!isset($this->kwBatchmakeComponent))
      {
      require_once BASE_PATH.'/modules/batchmake/controllers/components/KWBatchmakeComponent.php';
      require_once BASE_PATH.'/modules/batchmake/tests/library/ExecutorMock.php';
      $executor = new Batchmake_ExecutorMock();
      $this->kwBatchmakeComponent = new Batchmake_KWBatchmakeComponent($this->setupAndGetConfig(), $executor);
      }
    } */
    
   /** save upload item in the DB */
  public function createUploadedItem($userDao, $name, $path, $parent = null, $license = null, $filemd5 = '')
    {
    $modelLoad = new MIDAS_ModelLoader();
    $itemModel = $modelLoad->loadModel('Item');
    $feedModel = $modelLoad->loadModel('Feed');
    $folderModel = $modelLoad->loadModel('Folder');
    $bitstreamModel = $modelLoad->loadModel('Bitstream');
    $assetstoreModel = $modelLoad->loadModel('Assetstore');
    $feedpolicygroupModel = $modelLoad->loadModel('Feedpolicygroup');
    $itemRevisionModel = $modelLoad->loadModel('ItemRevision');
    $feedpolicyuserModel = $modelLoad->loadModel('Feedpolicyuser');
    $itempolicyuserModel = $modelLoad->loadModel('Itempolicyuser');

    if($userDao == null)
      {
      throw new Zend_Exception('Please log in');
      }

    if($parent == null)
      {
      $parent = $userDao->getPrivateFolder();
      }
    if(is_numeric($parent))
      {
      $parent = $folderModel->load($parent);
      }

    if($parent == false || !$folderModel->policyCheck($parent, $userDao, MIDAS_POLICY_WRITE))
      {
      throw new Zend_Exception('Parent permissions errors');
      }

    Zend_Loader::loadClass("ItemDao", BASE_PATH . '/core/models/dao');
    $item = new ItemDao;
    $item->setName($name);
    $item->setDescription('');
    $item->setType(0);
    $item->setThumbnail('');
    $itemModel->save($item);

    $feed = $feedModel->createFeed($userDao, MIDAS_FEED_CREATE_ITEM, $item);

    $folderModel->addItem($parent, $item);
    $itemModel->copyParentPolicies($item, $parent, $feed);

    $feedpolicyuserModel->createPolicy($userDao, $feed, MIDAS_POLICY_ADMIN);
    $itempolicyuserModel->createPolicy($userDao, $item, MIDAS_POLICY_ADMIN);

    Zend_Loader::loadClass("ItemRevisionDao", BASE_PATH . '/core/models/dao');
    $itemRevisionDao = new ItemRevisionDao;
    $itemRevisionDao->setChanges('Initial revision');
    $itemRevisionDao->setUser_id($userDao->getKey());
    $itemRevisionDao->setDate(date('c'));
    $itemRevisionDao->setLicense($license);
    $itemModel->addRevision($item, $itemRevisionDao);

    // Add bitstreams to the revision
    Zend_Loader::loadClass('BitstreamDao', BASE_PATH.'/core/models/dao');
    $bitstreamDao = new BitstreamDao;
    $bitstreamDao->setName($name);
    $bitstreamDao->setPath($path);
    $bitstreamDao->setChecksum($filemd5);
    $bitstreamDao->fillPropertiesFromPath();

    $assetstoreDao = $assetstoreModel->getDefault();
    $bitstreamDao->setAssetstoreId($assetstoreDao->getKey());

    if($assetstoreDao == false)
      {
      throw new Zend_Exception("Unable to load default assetstore");
      }

    // Upload the bitstream if necessary (based on the assetstore type)
    $this->uploadBitstream($bitstreamDao, $assetstoreDao);
    $checksum = $bitstreamDao->getChecksum();
    $tmpBitstreamDao = $bitstreamModel->getByChecksum($checksum);
    if($tmpBitstreamDao != false)
      {
      $bitstreamDao->setPath($tmpBitstreamDao->getPath());
      $bitstreamDao->setAssetstoreId($tmpBitstreamDao->getAssetstoreId());
      }
    $itemRevisionModel->addBitstream($itemRevisionDao, $bitstreamDao);

    $this->getLogger()->info(__METHOD__.' Upload ok :'.$path);
    Zend_Registry::get('notifier')->notifyEvent("EVENT_CORE_UPLOAD_FILE", array($item->toArray(), $itemRevisionDao->toArray()));
    return $item;
    }//end createUploadedItem
  
    
    
    
    
  // will generate the config params needed for batchmake execution, but
  // unrelated to the actual jobs
  // this is somewhat general, though specific to the use case of
  // creating a new item in a selected output collection, and 
  // processing the items dumped to the filesystem out of an input collection
 
  
//    $configParams = $this->generateConfigParams($outputFolderId, $outputItemStem, $taskDao, $inputFolderId);//, $$output_collection_id,$output_item_stem,$tmp_dir,$input_collection_dir);
  
  protected function generateConfigParams($outputFolderId, $outputItemStem, $taskDao, $inputFolderId, $inputItemIds, $userDao)
      //$output_collection_id,$output_item_stem,$tmp_dir,$input_collection_dir)
    {
    foreach($inputItemIds as $itemId)
      {
        
      }
          // get an api key for this user
    $modelLoad = new MIDAS_ModelLoader();
    $userApiModel = $modelLoad->loadModel('Userapi', 'api');
    $userApiDao = $userApiModel->getByAppAndUser($webApiApplication,$userDao);
    if(!$userApiDao)
      {
      throw new Zend_Exception('You need to create a web-api key for this user for application: '.$webApiApplication);
      }

    $configVars .= "Set(cfg_apikey '".$userApiDao->getApikey()."')\n";
    $configVars .= "Set(cfg_appname 'Default')\n";
      
      Create an item or update an existing one if one exists by the uuid passed
Parameters
token - Authentication token
name - The name of the item to create
description - (Optional) The description of the item
uuid - (Optional) Uuid of the item. If none is passed, will generate one.
privacy - (Optional) Default 'Public'.
parentid - The id of the parent folder
Return - The item object that was create
      
// create the output item
      $content['title'] = $output_item_stem.'_'.time();
      $content['firstname'] = array($this->User->getFirstName($this->user_id));
      $content['lastname'] = array($this->User->getLastName($this->user_id));
      $content['basehandle'] = $this->getMidasBaseHandle();
 
      $configParams = array();
 
      $itemid = $this->Item->createItem2($output_collection_id, $this->user_id, $content);
      if (!$itemid)
        {
        KwUtils::Error("Failed to create Item");
        return false;
        }
      else
        {
        $itemidArray = array('type' => 'Integer', 'name' => 'cfg_itemid', 'value' => $itemid);
        $configParams['cfg_itemid'] = $itemidArray;
        }
 
      $email = $this->User->getEmail($this->user_id);
      if (!$email)
        {
        KwUtils::Error("Problem with user email");
        return false;
        }
      else
        {
        $emailArray = array('type' => 'String', 'name' => 'cfg_email', 'value' => $email);
        $configParams['cfg_email'] = $emailArray;
        }
 
      $applicationName = 'Default';
      $apikeysids = $this->Api->getUserKeys($this->user_id);
      $apikey = false;
      foreach($apikeysids as $apikeysid)
        {
        $apiKeyApplication = $this->Api->getApplicationName($apikeysid);
        if ($apiKeyApplication == $applicationName)
          {
          $apikey = $this->Api->getKey($apikeysid);
          break;
          }
        }
      if (!$apikey)
        {
        KwUtils::Error("Problem with API Key");
        return false;
        }
      else
        {
        $apikeyArray = array();
        $apikeyArray['type'] = 'String';
        $apikeyArray['name'] = 'cfg_apikey';
        $apikeyArray['value'] = $apikey;
        $configParams['cfg_apikey'] = $apikeyArray;
 
        $appnameArray = array();
        $appnameArray['type'] = 'String';
        $appnameArray['name'] = 'cfg_appname';
        $appnameArray['value'] = $applicationName;
        $configParams['cfg_appname'] = $appnameArray;
        }
  
      include("config/config.php");
      if(!isset($MIDAS_SERVER_NAME) || $MIDAS_SERVER_NAME == "")
        {
        $baseURL = "http://localhost" . $this->webroot;
        }
      else
        {
        // TODO check the format of $MIDAS_SERVER_NAME
        $baseURL = 'http://' . $MIDAS_SERVER_NAME . $this->webroot;
        }
      $condorCallBackScript = ROOT . '/vendors/kwcondormidasbitstreamuploader.php';
 
      // create additional config params for the Midas web root and condor call back script
      $midasBaseURLArray = array('type'=>'String','name'=>'cfg_midas_baseURL','value'=>$baseURL);
      $configParams['cfg_midas_baseURL'] = $midasBaseURLArray;
 
      $condorCallBackScriptArray = array('type'=>'String','name'=>'cfg_condorpostscript','value'=>$condorCallBackScript);
      $configParams['cfg_condorpostscript'] = $condorCallBackScriptArray;
 
      // we need to execute the callback script using PHP
      $condorPhpExeArray = array('type'=>'String','name'=>'cfg_php_exe','value'=> $this->php_exe);
      $configParams['cfg_php_exe'] = $condorPhpExeArray;
 
      $tmpDirArray = array('type'=>'String','name'=>'cfg_output_directory','value'=>$tmp_dir);
      $configParams['cfg_output_directory'] = $tmpDirArray;
       
      $collectionDirArray = array('type' => 'String', 'name' => 'cfg_collection_dir', 'value' => $input_collection_dir);
      $configParams['cfg_collection_dir'] = $collectionDirArray;
 
      return $configParams;
      }
  
    
  public function runDemo($userDao)
    {
    echo "runDemo";
    
    // for now hard code all the ids

    // the collection to run over
    //$pivotal_id = 6;

    $inputFolderId = 0; // TODO  HACK HARDCODE
    // the output folder
    $outputFolderId = 223; // TODO HACK HARDCODE
echo "outputFolder [$outputFolderId]";
    //$output_item_stem = 'Pivotal_LesionSegmentationNistQIBench';
    //$input_collection_id = $pivotal_id;

    $componentLoader = new MIDAS_ComponentLoader();
    $kwbatchmakeComponent = $componentLoader->loadComponent('KWBatchmake', 'batchmake');
    $taskDao = $kwbatchmakeComponent->createTask($userDao);
    
    // TODO do data export
    // HACK for now hardcode
    // export input collection
    // get a list of input items
    // HACK should this be item daos instead?
    $inputItemIds = array ('39');
    // get a list of input item dirs
    $itemDirs = array("/home/mgrauer/dev/buckler_nist/39");
    

    $configParams = $this->generateConfigParams($outputFolderId, $outputItemStem, $taskDao, $inputFolderId, $inputItemIds, $userDao);//, $$output_collection_id,$output_item_stem,$tmp_dir,$input_collection_dir);


//     
  
    
    
   /* echo $kwbatchmakeComponent->testconfig($configParams);
         $configProps = array(MIDAS_BATCHMAKE_TMP_DIR_PROPERTY => $tmpDir.'/batchmake/tests/tmp',
    MIDAS_BATCHMAKE_BIN_DIR_PROPERTY => $tmpDir.'/batchmake/tests/bin',
    MIDAS_BATCHMAKE_SCRIPT_DIR_PROPERTY => $tmpDir.'/batchmake/tests/script',
    MIDAS_BATCHMAKE_APP_DIR_PROPERTY => $tmpDir.'/batchmake/tests/bin',
    MIDAS_BATCHMAKE_DATA_DIR_PROPERTY => $tmpDir.'/batchmake/tests/data',
    MIDAS_BATCHMAKE_CONDOR_BIN_DIR_PROPERTY => $tmpDir.'/batchmake/tests/condorbin');
 */
//return $kwbatchmakeComponent->testconfig($configParams);
   
    
    
/*    
    // setup initial batchmake workspace
    $this->_batchmakeConfig();
    list($tmp_dir,$taskid) =  $this->_setupWorkDir();
    $itemDirs = $this->_exportInputCollectionData($input_collection_id);
    // get the input_collection_dir based off any one of the $itemDirs
    foreach($itemDirs as $itemDir)
      {
      $lastInd = strrpos($itemDir,'/');
      $input_collection_dir = substr($itemDir,0,$lastInd+1);
      break;
      }

    // generate configuration params for the batchmake job
    $configParams = $this->_generateConfigParams($output_collection_id,$output_item_stem,$tmp_dir,$input_collection_dir);
    if (!$configParams)
      {
      KwUtils::Error("Failed to create Output Call Back Parameters");
      exit("Failed to create Output Call Back Parameters");
      }

    // generate all needed jobs
    $jobs = $this->_generateJobs($input_collection_id);

    // generate configuration params specific to the job
    $jobConfigParams = $this->_generateJobsConfigParams($jobs);
    $parameters = array_merge($jobConfigParams,$configParams); 

    // generate batchmake config file
    $ret = $this->kwbatchmakemodule->GenerateBatchmakeConfigFile($tmp_dir, $this->taskname, $parameters);
    if (!$ret){ exit("Failed to generate batchmake config file"); }


    // run batchmake and generate condor scripts
    $ret = $this->kwbatchmakemodule->GenerateCondorScript($this->batchmake_application_directory, $tmp_dir, $this->taskname);
    if (!$ret){ exit("Failed to generate condor script(s)"); }

    // generate condor DAG script
    $condorDagScript = $this->kwbatchmakemodule->GenerateCondorDagScript($tmp_dir, $this->taskname);
    if ($condorDagScript===false){exit("Failed to generate condor DAG script");}

    // submit condor DAG script to the grid
    $ret = $this->kwbatchmakemodule->SubmitCondorJob($tmp_dir, $condorDagScript);
   if ($ret===false){exit("Failed to submit condor job");}

*/


/* 
This work is put on hold.  The goal was to set up a tracker for each job, and expand
the condor POST callback scripts to upload info about each job as the job finishes.
    // create tracker entries for each job
    $outputStems = $jobConfigParams['cfg_outputStems']['value'];
    foreach($outputStems as $outputStem)//['cfg_outputStems']['cfg_outputStems'] as $outputStem)
      {
      $parts = split('_',$outputStem);
      $case_id = $parts[1];
      $lesion_uid = $parts[2];

      $this->NistqiTracker->create();
      $trackerData = array('NistqiTracker' => 
                       array('task_id' => $taskid,
                             'case_id' => $case_id,
                             'lesion_uid' => $lesion_uid));
      $this->NistqiTracker->save($trackerData);
      } 

// redirection, based on the batchmake taskstatus example

    $this->redirect("/nistqi/nistqi/trackdemo/".$taskid.'/'.$itemid);

// the tracker table, for ref
nistqi_tracker_id serial PRIMARY KEY,
    task_id integer,
    case_id integer,
    lesion_uid integer,
    job_params text,
    job_status text,
    job_out    text,
    job_err    text,
    stl_bitstream_id integer,
    mha_bitstream_id integer,
    aim_bitstream_id integer
*/


  /*  // for now redirect to batchmake taskstatus, condor watcher
    $itemid = $configParams['cfg_itemid']['value'];
    $this->redirect("/batchmake/batchmake/taskstatus/".$taskid.'/'.$itemid);
 */
    }
    
    
    
    
    
    
    
    
    
    
    
    
    //TODO remove this
    
  /**
   * will parse the passed in array $rows, assuming each $row in
   * $rows is a string, with column values separated by $separator,
   * and if there is an initial row of headers that should be set in
   * $headersIncluded; for each of these $rows, it will parse the
   * $row, create and then save a LesionseedpointDao.
   * @param type $headersIncluded
   * @param type $separator
   * @param type $rows
   * @return Qibench_LesionseedpointDao
   */
  public function parseAndSave($headersIncluded, $separator, $rows)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $lesionseedpointModel = $modelLoad->loadModel('Lesionseedpoint', 'qibench');
    $lesionseedpointModel->loadDaoClass('LesionseedpointDao', 'qibench');

    // get all of the column names from the model
    // discard the primary key column
    // add these columns to an integer indexed array, used to pull
    // corresponding column names for column data, based on the column index
    $mainData = $lesionseedpointModel->getMainData();
    $mainDataCols = array();
    $mainDataKey = $lesionseedpointModel->getKey();
    foreach($mainData as $colName => $val)
      {
      if($colName === $mainDataKey)
        {
        continue;
        }
      else
        {
        $mainDataCols[] = $colName;
        }
      }

    // loop through rows, create a Dao for each and save it
    $lesionseedpointDaos = array();
    foreach($rows as $ind => $row)
      {
      if($ind === 0 && $headersIncluded)
        {
        continue;
        }
      else
        {
        // parse the row
        $cols = explode($separator, $row);
        $lesionseedpointDao = new Qibench_LesionseedpointDao();
        foreach($cols as $ind => $col)
          {
          // get the column name for this column datum
          $mainDataCol = $mainDataCols[$ind];
          $lesionseedpointDao->set($mainDataCol, $col);
          }
        $lesionseedpointModel->save($lesionseedpointDao);
        $lesionseedpointDaos[] = $lesionseedpointDao;
        }
      }
    return $lesionseedpointDaos;
    }



} // end class
?>
