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

    
    
    
  // will generate the config params needed for batchmake execution, but
  // unrelated to the actual jobs
  // this is somewhat general, though specific to the use case of
  // creating a new item in a selected output collection, and 
  // processing the items dumped to the filesystem out of an input collection
 
  
//    $configParams = $this->generateConfigParams($outputFolderId, $outputItemStem, $taskDao, $inputFolderId);//, $$output_collection_id,$output_item_stem,$tmp_dir,$input_collection_dir);
  
/*     
     // will generate the jobs for the input collection.  comparing the
  // item_names in the input collection with the seedpoints stored in the DB,
  // for every match a single job (and a single run of the EXE) will be created
  function _generateJobs($input_collection_id)
    {

    // generate the jobs for this collection
    // this means find matches between seedpoints and items

    // map collection's items by name, since that is how the seedpoints ids it
    $num_items = $this->Collection->getNumberOfItems($input_collection_id);
    $groups = $this->User->getGroups( $this->user_id );
    $items_info = $this->Collection->getAllItemsInfo( $input_collection_id, $this->user_id, $groups, 0, $num_items);

    $item_names_to_ids = array();
    foreach($items_info['items'] as $item_info)
      {
      $item_names_to_ids[$item_info['title']] = $item_info['id'];
      } 

    // create jobs for each match
    $jobs = array(); 

    // look through seedpoints, creating a job for each match between a seedpoint
    // and an item in the collection
    $seedpoints = $this->NistqiLesionSeedpoint->find('all');
    foreach($seedpoints as $seedpoint)
      {
      $seed_data = $seedpoint['NistqiLesionSeedpoint'];
      $item_name = $seed_data['case_id'];
      if(array_key_exists($item_name,$item_names_to_ids))
        {
        $job = array();
        $job['case_id'] = $item_name;
        $job['item_id'] = $item_names_to_ids[$item_name];
        $job['lesion_uid'] = $seed_data['lesion_uid'];
        $job['lesion_uid'] = $seed_data['lesion_uid'];
        $job['seed'] = "3 " . $seed_data['seed_x'] . ' ' . $seed_data['seed_y'] . ' ' . $seed_data['seed_z'];
        $job['bounding_box'] = "6 " . $seed_data['bounding_box_x0'] . ' ' . $seed_data['bounding_box_x1'] . ' ' . $seed_data['bounding_box_y0'] . ' ' . $seed_data['bounding_box_y1'] . ' ' . $seed_data['bounding_box_z0'] . ' ' . $seed_data['bounding_box_z1'];
        if(!$seed_data['is_in_physical_space']) $job['physical_space'] = '0';
        else $job['physical_space'] = '1';
        $job['output_stem'] = 'lstk' . '_' . $item_name . '_' . $seed_data['lesion_uid'] . '_V_lstk';
        $jobs[] = $job;
        }
      }
    
    return $jobs;
    }
*/
    
    
  protected function generateJobs($inputFolderId, $userDao)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $lesionseedpointModel = $modelLoad->loadModel('Lesionseedpoint', 'qibench');
    $seedpointDaos = $lesionseedpointModel->getAll();
    //echo "seedpoints:";
    //var_dump($seedpointDaos);
    $modelLoad = new MIDAS_ModelLoader();
    $folderModel = $modelLoad->loadModel('Folder');
    $folderDao = $folderModel->load($inputFolderId);
    $items = $folderModel->getItemsFiltered($folderDao, $userDao);
    //echo "items";
    //var_dump($items);
    // HACK a hacky join
    $jobs = array();
    $jobsItems = array();
    foreach($seedpointDaos as $seedpointDao)
      {
      $caseId = $seedpointDao->getCaseId();
      foreach($items as $itemDao)
        {
        $itemName = $itemDao->getName();
        if($itemName === $caseId)
          {
          $jobsItems[$seedpointDao->getKey()] = $itemDao;
          $jobs[$seedpointDao->getKey()] = $seedpointDao;
          }
        }
      }
    return array($jobs, $jobsItems);
    //echo "jobs:";
    //var_dump($jobs);

     
    }
    //$outputFolderId, $outputItemStem, $taskDao, $inputFolderId, $inputItemIds, $userDao);
      
    
    
  protected function generatePythonConfigParams($taskDao, $userDao)
    {
    // generate an config file for this run
    // HARDCODED
    $configs = array();
    $configs[] = 'url http://localhost/midas3';
    $configs[] = 'appname Default';
    
    $email = $userDao->getEmail();
    // get an api key for this user
    $modelLoad = new MIDAS_ModelLoader();
    $userApiModel = $modelLoad->loadModel('Userapi', 'api');
    $userApiDao = $userApiModel->getByAppAndUser('Default', $userDao);
    if(!$userApiDao)
      {
      throw new Zend_Exception('You need to create a web-api key for this user for application: Default');
      }
    $configs[] = 'email '.$email;
    $configs[] = 'apikey '.$userApiDao->getApikey();
    $filepath = $taskDao->getWorkDir() . '/' . 'config.cfg';
    //echo $filepath;
    
    if(!file_put_contents($filepath, implode("\n",$configs)))
      {
      throw new Zend_Exception('Unable to write configuration file: '.$filepath);
      }
    }
  
  protected function generateBatchmakeConfig($taskDao, $datapath, $jobs, $jobsItems, $outputFolderId)
    {
    echo "generateBatchmakeConfig[$datapath]";
    $jobConfigParams = array();
    
    $jobConfigParams['cfg_itemNames'] = array();//'type' => 'Integer', 'name' => 'cfg_itemIDs', 'value' => $cfg_itemIDs); 
    $jobConfigParams['cfg_itemIDs'] = array();//'type' => 'Integer', 'name' => 'cfg_itemIDs', 'value' => $cfg_itemIDs); 
    $jobConfigParams['cfg_outputStems'] = array();//'type' => 'String', 'name' => 'cfg_outputStems', 'value' => $cfg_outputStems);
    $jobConfigParams['cfg_seeds'] = array();
    $jobConfigParams['cfg_rois'] = array();
    $jobConfigParams['cfg_usePhysicalSpaces'] = array();
    $jobConfigParams['cfg_jobInds'] = array();
    $jobInd = 0;
    foreach($jobs as $seedpointDao)
      {
      $seedpointId = $seedpointDao->getKey();
      $itemId = $jobsItems[$seedpointId]->getKey();
      $itemName = $jobsItems[$seedpointId]->getName();
      $jobConfigParams['cfg_itemNames'][] = $seedpointDao->getCaseId() . '_' . $seedpointDao->getLesionId(); 
      $jobConfigParams['cfg_itemIDs'][]= $itemId;
      $jobConfigParams['cfg_outputStems'][] = 'lstk_' . $seedpointDao->getCaseId() . '_' . $seedpointDao->getLesionId() . '_V_lstk';
      $jobConfigParams['cfg_seeds'][] = "3 " . $seedpointDao->getSeedX() . ' ' . $seedpointDao->getSeedY() . ' ' . $seedpointDao->getSeedZ();
      $jobConfigParams['cfg_rois'][] = "6 " . $seedpointDao->getBoundingBoxX0() . ' ' . $seedpointDao->getBoundingBoxX1() . ' ' . $seedpointDao->getBoundingBoxY0() . ' ' . $seedpointDao->getBoundingBoxY1() . ' ' . $seedpointDao->getBoundingBoxZ0() . ' ' . $seedpointDao->getBoundingBoxZ1();
      $jobConfigParams['cfg_usePhysicalSpaces'][] = 1;
      $jobConfigParams['cfg_jobInds'][] = $jobInd++;
      }
      
    $configFileLines = array();  
    foreach($jobConfigParams as $jobConfigParamName => $jobConfigParamValues)
      {
      $configFileLine = "Set(" . $jobConfigParamName;
      foreach($jobConfigParamValues as $jobConfigParamValue)
        {
        $configFileLine .= " '" . $jobConfigParamValue . "'";  
        }
      $configFileLine .= ")";
      $configFileLines[] = $configFileLine;
      }
    $configFileLines[] = "Set(cfg_collection_dir '" . $datapath . "')";
    $configFileLines[] = "Set(cfg_output_directory '" . $taskDao->getWorkDir() . "')";
    $configFileLines[] = "Set(cfg_exe '/usr/bin/python')";
    $configFileLines[] = "Set(cfg_condorpostscript '" . BASE_PATH . "/modules/qibench/library/qibench_condor_postscript.py')";
    $configFileLines[] = "Set(cfg_outputFolderID '" . $outputFolderId . "')";
/*    
    
    require_once BASE_PATH.'/modules/batchmake/library/Executor.php';
          
          
    
    #! /usr/bin/python
    cfg_exe 
    ${cfg_condorpostscript} qibench_condor_postscript.py
    
      (scriptName, outputDir, outputFolderId, itemName, outputAim, outputImage, outputMesh, exeOutput)
  */          
            
    
    //var_dump($jobConfigParams);
    $configFilePath = $taskDao->getWorkDir() . "/LesionSegmentationQIBench.config.bms";
    echo "configFilePath[$configFilePath]";
    if(!file_put_contents($configFilePath, implode("\n", $configFileLines)))
      {
      throw new Zend_Exception('Unable to write configuration file: '.$configFilePath);
      }
    
    
    
    
    
    
    /*
    Set(cfg_itemIDs '39' '39' '39' '39')
Set(cfg_outputStems 'lstk_39_4_V_lstk' 'lstk_39_10_V_lstk' 'lstk_39_41_V_lstk' 'lstk_39_43_V_lstk')
Set(cfg_seeds '3 129.4 256.22 136.01' '3 118.37 224.71 62' '3 142.16 264.98 73.6' '3 116.05 246.89 131.61')
Set(cfg_rois '6 109.4 149.4 236.22 276.22 116.01 156.01' '6 78.37 158.37 184.71 264.71 22 102' '6 122.16 162.16 244.98 284.98 53.6 93.6' '6 76.05 156.05 206.89 286.89 91.61 171.61') 
Set(cfg_usePhysicalSpaces '1' '1' '1' '1')
Set(cfg_jobInds '0' '1' '2' '3')
Set(cfg_outputfolderid '223')
Set(cfg_email 'michael.grauer@kitware.com')
Set(cfg_apikey 'E0O6TVvz1xzueMvciZv8tU2XqSoKePuJG8FqT1PL')
Set(cfg_appname 'Default')
Set(cfg_collection_dir '/home/mgrauer/dev/buckler_nist/')
Set(cfg_output_directory '/home/mgrauer/dev/buckler_nist/39out/')
    */
    
    
    
    
    
    
    
    
    
    
    
//    exit();
    }
    
    /*
    // will generate config params for batchmake execution specific to the jobs
    // that will be run, iterates over a set of jobs and sets up
    // the necessary batchmake params
    function _generateJobsConfigParams($jobs)
      {
      $jobConfigParams = array();

      $cfg_itemIDs = array();
      $cfg_outputStems = array();
      $cfg_seeds = array();
      $cfg_rois = array();
      $cfg_usePhysicalSpaces = array();

      $count = 0;
      foreach($jobs as $job)
        {
        $cfg_itemIDs[] = $job['item_id'];
        $cfg_outputStems[] = $job['output_stem'];
        $cfg_seeds[] = $job['seed'];
        $cfg_rois[] = $job['bounding_box'];
        $cfg_usePhysicalSpaces[] = $job['physical_space'];
        $count = $count +1;
        // if DBG_limit_count is set, this will limit how many jobs are actually run
        if($this->DBG_limit_count && $count >= $this->DBG_limit_count) break;
        }

      $jobConfigParams['cfg_itemIDs'] = array('type' => 'Integer', 'name' => 'cfg_itemIDs', 'value' => $cfg_itemIDs); 
      $jobConfigParams['cfg_outputStems'] = array('type' => 'String', 'name' => 'cfg_outputStems', 'value' => $cfg_outputStems);
      $jobConfigParams['cfg_seeds'] = array('type' => 'String', 'name' => 'cfg_seeds', 'value' => $cfg_seeds);
      $jobConfigParams['cfg_rois'] = array('type' => 'String', 'name' => 'cfg_rois', 'value' => $cfg_rois);
      $jobConfigParams['cfg_usePhysicalSpaces'] = array('type' => 'String', 'name' => 'cfg_usePhysicalSpaces', 'value' => $cfg_usePhysicalSpaces);

      // HACK this is slightly distasteful, need to add in a variable which is
      // a list of indices, one for each job.  
      // Though it is not nearly as gross as an earlier solution of directly
      // appending to the batchmake config file:
      // "Sequence(cfg_jobInds 0 ". count($jobs)-1 ." 1)\n"
      $jobConfigParams['cfg_jobInds'] = array('type' => 'Integer', 'name' => 'cfg_jobInds', 'value' => range(0,count($cfg_itemIDs)-1,1));
      
      return $jobConfigParams;
      }    
    */
    
    
    
    
    
    
    
    
    
    
    
  public function runDemo($userDao)
    {
    //echo "runDemo";
    
    // for now hard code all the ids

    // the collection to run over
    //$pivotal_id = 6;

    $inputFolderId = 237; // TODO  HACK HARDCODE
    // the output folder
    $outputFolderId = 242; // TODO HACK HARDCODE
    //echo "outputFolder [$outputFolderId]";
    //$output_item_stem = 'Pivotal_LesionSegmentationNistQIBench';
    //$input_collection_id = $pivotal_id;

    $componentLoader = new MIDAS_ComponentLoader();
    $kwbatchmakeComponent = $componentLoader->loadComponent('KWBatchmake', 'batchmake');
    $taskDao = $kwbatchmakeComponent->createTask($userDao);
    //var_dump($taskDao);
    //exit();
    
    // TODO do data export
    // HACK for now hardcode
    // export input collection
    // get a list of input items
    // HACK should this be item daos instead?
    $inputItemIds = array ('39');
    // get a list of input item dirs
    $itemDirs = array("/home/mgrauer/dev/buckler_nist/39");
    
    $outputItemStem = "qibench";
    $this->generatePythonConfigParams($taskDao, $userDao);
    list($jobs, $jobsItems) = $this->generateJobs($inputFolderId, $userDao);//

    // export the items to the work dir data dir
    $datapath = $taskDao->getWorkDir() . '/' . 'data';
    //echo "datapath[$datapath]";
    if(!KWUtils::mkDir($datapath))
      {
      throw new Zend_Exception("couldn't create data export dir: ". $datapath);  
      }
    $exportComponent = $componentLoader->loadComponent('Export');
    $jobsItemsIds = array();
    foreach($jobsItems as $jobItemDao)
      {
      $jobsItemsIds[] = $jobItemDao->getKey();  
      }
//echo "jobsItemsIds";
//exit();    
//var_dump($jobsItemsIds);
    $exportComponent->exportBitstreams($userDao, $datapath, $jobsItemsIds, true);
    // need a mapping of item name to item id
    $this->generateBatchmakeConfig($taskDao, $datapath, $jobs, $jobsItems, $outputFolderId);
 
    $bmScript = "LesionSegmentationQIBench.bms";
    $kwbatchmakeComponent->preparePipelineScripts($taskDao->getWorkDir(), $bmScript);
echo "prepare1";
    $kwbatchmakeComponent->preparePipelineBmms($taskDao->getWorkDir(), array($bmScript));
echo "prepare2";
    $kwbatchmakeComponent->compileBatchMakeScript($taskDao->getWorkDir(), $bmScript);
echo "compile";
    $dagScript = $kwbatchmakeComponent->generateCondorDag($taskDao->getWorkDir(), $bmScript);
echo "generateDag[$dagScript]";
    $kwbatchmakeComponent->condorSubmitDag($taskDao->getWorkDir(), $dagScript);
echo "submitted";
    exit();

//     public function generateCondorDag($workDir, $bmScript)
//              public function condorSubmitDag($workDir, $dagScript)
    
    
    
    
    
    
    
    
    
    
    
    
    
//$outputFolderId, $outputItemStem, $taskDao, $inputFolderId, $inputItemIds, $userDao);
    
//
    ////, $$output_collection_id,$output_item_stem,$tmp_dir,$input_collection_dir);
//    $configParams = $this->generateConfigParams($outputFolderId, $outputItemStem, $taskDao, $inputFolderId, $inputItemIds, $userDao);//, $$output_collection_id,$output_item_stem,$tmp_dir,$input_collection_dir);


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
    
    
    
    
    
    
    
    
    
    
    
    



} // end class
?>
