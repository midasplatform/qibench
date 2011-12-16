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


  protected function generateJobs($inputFolderId, $userDao, $seedpointsItemrevisionId)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $lesionseedpointModel = $modelLoad->loadModel('Lesionseedpoint', 'qibench');
    $seedpointDaos = $lesionseedpointModel->getAll($seedpointsItemrevisionId, $userDao);
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
    foreach($seedpointDaos as $ind => $seedpointDao)
      {
      $caseId = $seedpointDao->getCaseId();
      foreach($items as $itemDao)
        {
        $itemName = $itemDao->getName();
        if($itemName === $caseId)
          {
          $jobsItems[$ind] = $itemDao;
          $jobs[$ind] = $seedpointDao;
//          $jobsItems[$seedpointDao->getKey()] = $itemDao;
//          $jobs[$seedpointDao->getKey()] = $seedpointDao;
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

  protected function generateBatchmakeConfig($taskDao, $runDao, $datapath, $jobs, $jobsItems, $outputFolderId)
    {
    //echo "generateBatchmakeConfig[$datapath]";

    // create a RunItem per job
    $modelLoad = new MIDAS_ModelLoader();
    $runItemModel = $modelLoad->loadModel('RunItem', 'qibench');
    $runItemModel->loadDaoClass('RunItemDao', 'qibench');
    $runId = $runDao->getKey();

    $jobConfigParams = array();

    $jobConfigParams['cfg_itemNames'] = array();
    $jobConfigParams['cfg_itemIDs'] = array();
    $jobConfigParams['cfg_outputStems'] = array();
    $jobConfigParams['cfg_seeds'] = array();
    $jobConfigParams['cfg_rois'] = array();
    $jobConfigParams['cfg_usePhysicalSpaces'] = array();
    $jobConfigParams['cfg_jobInds'] = array();
    $jobConfigParams['cfg_runItemIDs'] = array();
    $jobInd = 0;
    foreach($jobs as $seedpointInd=>$seedpointDao)
      {
      //$seedpointId = $seedpointDao->getKey();
      //$itemId = $jobsItems[$seedpointId]->getKey();
      //$itemName = $jobsItems[$seedpointId]->getName();
      $itemId = $jobsItems[$seedpointInd]->getKey();
      $itemName = $jobsItems[$seedpointInd]->getName();
      $jobConfigParams['cfg_itemNames'][] = $seedpointDao->getCaseId() . '_' . $seedpointDao->getLesionId();
      $jobConfigParams['cfg_itemIDs'][]= $itemId;
      $jobConfigParams['cfg_outputStems'][] = 'lstk_' . $seedpointDao->getCaseId() . '_' . $seedpointDao->getLesionId() . '_V_lstk';
      $jobConfigParams['cfg_seeds'][] = "3 " . $seedpointDao->getSeedX() . ' ' . $seedpointDao->getSeedY() . ' ' . $seedpointDao->getSeedZ();
      $jobConfigParams['cfg_rois'][] = "6 " . $seedpointDao->getBoundingBoxX0() . ' ' . $seedpointDao->getBoundingBoxX1() . ' ' . $seedpointDao->getBoundingBoxY0() . ' ' . $seedpointDao->getBoundingBoxY1() . ' ' . $seedpointDao->getBoundingBoxZ0() . ' ' . $seedpointDao->getBoundingBoxZ1();
      $jobConfigParams['cfg_usePhysicalSpaces'][] = 1;
      $jobConfigParams['cfg_jobInds'][] = $jobInd++;
      // create the RunItem
      $runItemDao = new Qibench_RunItemDao();
      $runItemDao->setQibenchRunId($runId);
      $runItemDao->setInputItemId($itemId);
      $runItemDao->setCaseId($seedpointDao->getCaseId());
      $runItemDao->setLesionId($seedpointDao->getLesionId());
      // HACK need to set outputItemId somewhere
      $runItemModel->save($runItemDao);
      $jobConfigParams['cfg_runItemIDs'][] = $runItemDao->getKey();
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
    $configFileLines[] = "Set(cfg_condordagpostscript '" . BASE_PATH . "/modules/qibench/library/qibench_condor_dag_postscript.py')";
    $configFileLines[] = "Set(cfg_outputFolderID '" . $outputFolderId . "')";
    $configFileLines[] = "Set(cfg_runID '" . $runId . "')";
    $configFileLines[] = "Set(cfg_taskID '" . $taskDao->getBatchmakeTaskId() . "')";
    $configFilePath = $taskDao->getWorkDir() . "/LesionSegmentationQIBench.config.bms";
    //echo "configFilePath[$configFilePath]";
    if(!file_put_contents($configFilePath, implode("\n", $configFileLines)))
      {
      throw new Zend_Exception('Unable to write configuration file: '.$configFilePath);
      }
    return $jobConfigParams;
    }



  public function runDemo($userDao, $inputFolderId, $outputFolderId, $seedpointsItemrevisionId)
    {

    $componentLoader = new MIDAS_ComponentLoader();
    $kwbatchmakeComponent = $componentLoader->loadComponent('KWBatchmake', 'batchmake');
    $taskDao = $kwbatchmakeComponent->createTask($userDao);


    // create a Run
    $modelLoad = new MIDAS_ModelLoader();
    $runModel = $modelLoad->loadModel('Run', 'qibench');
    $runModel->loadDaoClass('RunDao', 'qibench');
    $runDao = new Qibench_RunDao();
    $runDao->setBatchmakeTaskId($taskDao->getBatchmakeTaskId());
    $runDao->setSeedpointsItemrevisionId($seedpointsItemrevisionId);
    $runDao->setInputFolderId($inputFolderId);
    $runDao->setOutputFolderId($outputFolderId);
    $runDao->setExecutableName('lstk');
    $runModel->save($runDao);
    // now that we have created a run, create a new folder for this run under
    // the outputFolder
    $folderModel = $modelLoad->loadModel('Folder');
    $outputFolderDao = $folderModel->createFolder('Run ' . $runDao->getKey() . ' Output', '', $outputFolderId);
    // now set the outputFolderId to be the newly created one
    $outputFolderId = $outputFolderDao->getKey();


    // TODO do data export
    // HACK for now hardcode
    // export input collection

    $outputItemStem = "qibench";
    $this->generatePythonConfigParams($taskDao, $userDao);
    list($jobs, $jobsItems) = $this->generateJobs($inputFolderId, $userDao, $seedpointsItemrevisionId);//

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
      // use the item id as both key and value so we don't end up exporting duplicates
      $jobsItemsIds[$jobItemDao->getKey()] = $jobItemDao->getKey();
      }
    $exportComponent->exportBitstreams($userDao, $datapath, $jobsItemsIds, true);


    // need a mapping of item name to item id
    $jobConfigParams = $this->generateBatchmakeConfig($taskDao, $runDao, $datapath, $jobs, $jobsItems, $outputFolderId);


    $bmScript = "LesionSegmentationQIBench.bms";
    $kwbatchmakeComponent->preparePipelineScripts($taskDao->getWorkDir(), $bmScript);

    $kwbatchmakeComponent->preparePipelineBmms($taskDao->getWorkDir(), array($bmScript));

    //$kwbatchmakeComponent->compileBatchMakeScript($taskDao->getWorkDir(), $bmScript);
    $dagScript = $kwbatchmakeComponent->generateCondorDag($taskDao->getWorkDir(), $bmScript);
    $kwbatchmakeComponent->condorSubmitDag($taskDao->getWorkDir(), $dagScript);

    /*
//when i uncomment either of these two lines, even though they work, the
//view breaks


// this line is commented out just to not take so much time/cpu, it submits to condor
         //$kwbatchmakeComponent->condorSubmitDag($taskDao->getWorkDir(), $dagScript);
*/
    return array($runDao, $jobConfigParams);
    }






} // end class
?>