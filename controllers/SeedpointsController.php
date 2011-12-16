<?php

/* =========================================================================
  MIDAS Server
  Copyright (c) Kitware SAS. 20 rue de la Villette. All rights reserved.
  69328 Lyon, FRANCE.

  See Copyright.txt for details.
  This software is distributed WITHOUT ANY WARRANTY; without even
  the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
  PURPOSE.  See the above copyright notices for more information.
  ========================================================================= */

/**
 *  Qibench_SeedpointsController
 */
class Qibench_SeedpointsController extends Qibench_AppController {

  public $_moduleForms = array('Seedpoints');
  public $_moduleModels = array('Lesionseedpoint');
  public $_moduleDaos = array('Lesionseedpoint');
  public $_moduleComponents = array('Execute', 'LesionseedpointCSV');




  /** view seedpoints action
   *
   * I now have a view action, this will display the initial table of the seedpoints
   * uncomment the "test code to load test data" three lines to load data into your db
   * then comment them again after
   *
   *
   *
   */
  public function viewAction()
    {
    set_time_limit(0);
    if(!$this->logged || !$this->userSession->Dao->getAdmin() == 1)
      {
      $this->_redirect('/');
      }

    // test code to load test data
    //$filepath = BASE_PATH . '/modules/qibench/tests/testfiles/lesionseedpoints_full.txt';
    //$contents = file($filepath, FILE_IGNORE_NEW_LINES);
    //$lesionseedpointDaos = $this->ModuleComponent->LesionseedpointCSV->parseAndSave(false, ',', $contents);





    // same controller method either for view of initial table of seedpoints
    // or after execute button has been pressed, in which case we come into the post


    if($this->_request->isPost())
      {

      // for now hard code all the ids
      $inputFolderId = 14; // TODO  HACK HARDCODE  where the input dicoms are stored
      // the output folder
      $outputFolderId = 15; // TODO HACK HARDCODE  where the output image files should go,
      // actually they will be created in a new folder under this folder

      $seedpointsItemrevisionId = 134;

   // added cleaning the output buffer in case that would help, it didn't
//ob_start();


      list($runDao, $jobConfigParams) = $this->ModuleComponent->Execute->runDemo($this->userSession->Dao, $inputFolderId, $outputFolderId, $seedpointsItemrevisionId);
//ob_end_clean();
//
//
//

      while(ob_get_level() > 0)
      {
      ob_end_clean();
      }
      /*
      // transpose the jobsConfigParams to display output table rows
      $outputTable = array();
      $numJobs = sizeof($jobConfigParams['cfg_itemNames']);
      for($i = 0; $i < $numJobs; $i++)
        {
        $row = array();
        $itemName = $jobConfigParams['cfg_itemNames'][$i];
        $itemNameParts = explode('_', $itemName);
        $caseId = $itemNameParts[0];
        $lesionId = $itemNameParts[1];
        $row[] = $caseId;
        $row[] = $lesionId;
        $row[] = 'lstk';
        // input item
        $row[] = $jobConfigParams['cfg_itemIDs'][$i];
        // output item
        $row[] = 'output item';
        // ???
        $row[] = $jobConfigParams['cfg_runItemIDs'][$i];
        // volume
        $row[] = 'volume read-out';
        $outputTable[] = $row;
        }


      $this->view->header = $this->t("Case Reading Results");
      $tableHeaders = array(
  'Series number' => ' Series number',
  'LesionUID' => 'LesionUID',
  'Analysis SW Model' => 'Analysis SW Model',
  'Input Item' => 'Input Item',
  'Output Item' => 'Output Item',
  'Run Item' => 'Run Item',
  'Volume Read-Out' => 'Volume Read-Out');

   $this->view->tableHeaders = $tableHeaders;
   $this->view->tableData = $outputTable;*/





      $this->_redirect('/qibench/seedpoints/output/?run='.$runDao->getQibenchRunId());







      }
      else
        {
        // not a post request, just display the seedpoints


        $this->view->header = $this->t("Lesion Seedpoints");
        //$this->view->seedpointDaos = $this->Qibench_Lesionseedpoint->getAll();
        //$seedpointDaos = $this->Qibench_Lesionseedpoint->getAll();

        $modelLoad = new MIDAS_ModelLoader();
        $lesionseedpointModel = $modelLoad->loadModel('Lesionseedpoint', 'qibench');
        $seedpointsItemrevisionId = 134;
        $seedpointDaos = $lesionseedpointModel->getAll($seedpointsItemrevisionId, $this->userSession->Dao);

        //$seedpointDaos = array();
        $seedpointsForm = $this->ModuleForm->Seedpoints->createSeedpointsForm();
        $formArray = $this->getFormAsArray($seedpointsForm);
        $this->view->seedpointsForm = $formArray;

        $tableHeaders = array(
        'case_id' => 'case id',
        'lesion_id' => 'lesion id',
        'seed_x' => 'seed x',
        'seed_y' => 'seed y',
        'seed_z' => 'seed z',
        'bounding_box_x0' => 'x0',
        'bounding_box_x1' => 'x1',
        'bounding_box_y0' => 'y0',
        'bounding_box_y1' => 'y1',
        'bounding_box_z0' => 'z0',
        'bounding_box_z1' => 'z1');
         $this->view->tableHeaders = $tableHeaders;

         $tableData = array();
         foreach($seedpointDaos as $seedpointDao)
           {
           $row = array();
           foreach($tableHeaders as $dbCol => $tableHeader)
             {
             $row[] = $seedpointDao->get($dbCol);
             }
           $tableData[] = $row;
           }
         $this->view->tableData = $tableData;
         }








    }//end view


  /**
   * action to show display of output based on a qibench run.
   */
  public function outputAction()
    {


    //$componentLoader = new MIDAS_ComponentLoader();
    //$kwbatchmakeApiComponent = $componentLoader->loadComponent('Api', 'batchmake');
    //$params = array("batchmaketaskid"=>"190", "logfilename"=>"mylogyo", "dagfilename"=>"mydagyo", "useSession"=>"useSession" );

    //$kwbatchmakeApiComponent->addCondorDag($params);

    //$params = array("batchmaketaskid"=>"190", "logfilename"=>"mylogyo", "outputfilename"=>"myout", "errorfilename"=>"myerror", "postfilename"=>"mypost", "jobdefinitionfilename"=>"myjobdefinitionfilename", "useSession"=>"useSession" );
    //$kwbatchmakeApiComponent->addCondorJob($params);


    //$qibenchApiComponent = $componentLoader->loadComponent('Api', 'qibench');
    //$params = array("qibenchrunitemid"=>"322", "condorjobid"=>"3", "useSession"=>"useSession" );
    //$qibenchApiComponent->runitemCondorjobSet($params);



    // this is seriously inefficient
    $this->view->header = $this->t("Case Reading Results");
    $tableHeaders = array(
      'Series number' => ' Series number',
      'LesionUID' => 'LesionUID',
      'Analysis SW Model' => 'Analysis SW Model',
      'Input Item' => 'Input Item',
      'Output Item' => 'Output Item',
      //'Run Item' => 'Run Item',
      'Volume Read-Out' => 'Volume Read-Out');

    $this->view->tableHeaders = $tableHeaders;
    $runId = $this->_getParam('run');



    $modelLoad = new MIDAS_ModelLoader();
    $runModel = $modelLoad->loadModel('Run', 'qibench');
    $runModel->loadDaoClass('RunDao', 'qibench');
    $runDao = $runModel->load($runId);
    $runItemModel = $modelLoad->loadModel('RunItem', 'qibench');
    // this find by could easily be worked in with the first load
    $runItemDaos = $runItemModel->findBy('qibench_run_id', $runId);
    $runItemScalarvalueModel = $modelLoad->loadModel('RunItemScalarvalue', 'qibench');

    $outputTable = array();

    foreach($runItemDaos as $runItemDao)
      {
      $row = array();
      $row[] = $runItemDao->getCaseId();
      $row[] = $runItemDao->getLesionId();
      $row[] = $runDao->getExecutableName();
      //$row[] = $runItemDao->getInputItemId();
      $row[] = '<a href="'.$this->view->webroot.'/item/'.$runItemDao->getInputItemId().'" target="_blank">view</a>';
      $outputItemId = $runItemDao->getOutputItemId();
      if(!isset($outputItemId) || !is_numeric($outputItemId) || $outputItemId == 0)
        {
        $row[] = "unavailable";
        }
      else
        {
        $row[] = '<a href="'.$this->view->webroot.'/item/'.$outputItemId.'" target="_blank">view</a>';
        }
      //$row[] = $runItemDao->getOutputItemId();

      // these find bys are expensive and could easily be worked back in with the first load
      $runItemScalarvalueDaos = $runItemScalarvalueModel->findBy('qibench_run_item_id', $runItemDao->getQibenchRunItemId());
      // just take the first one for now
      $found = false;
      foreach($runItemScalarvalueDaos as $runItemScalarvalueDao)
        {
        // just take the first match for now
        if($runItemScalarvalueDao->getName() == "CaseReading")
          {
          $row[] = $runItemScalarvalueDao->getValue();
          $found = true;
          break;
          }
        }
      if(!$found)
        {
        $row[] = "unavailable";
        }


      $outputTable[] = $row;
      }
    $this->view->tableData = $outputTable;







    // debug info

    $runId = $runDao->getQibenchRunId();
    $taskModel = $modelLoad->loadModel('Task', 'batchmake');
    $taskDao = $taskModel->load($runDao->getBatchmakeTaskId());
    $condorDagModel = $modelLoad->loadModel('CondorDag', 'batchmake');
    $condorDagDaos = $condorDagModel->findBy('batchmake_task_id', $runDao->getBatchmakeTaskId());
    $condorDagDao = $condorDagDaos[0];



    $topDebug = array();
    //$topDebug['batchmake_task_id'] = $runDao->getBatchmakeTaskId();
    //$topDebug['batchmake_work_dir'] = $taskDao->getWorkDir();
    //$topDebug['qibench_run_id'] = $runDao->getQibenchRunId();
    //$topDebug['condor_dag_id'] = $condorDagDao->getCondorDagId();
    //$topDebug['log_filename'] = $condorDagDao->getLogFilename();

    //if(empty($condorDagDao) || empty($condorDagDao->getLogFilename()))
    $topDebug['log_filename'] = '<a href="'.$this->view->webroot.'/qibench/seedpoints/showfile/?filepath='.$taskDao->getWorkDir().$condorDagDao->getLogFilename().'" target="_blank">'.$condorDagDao->getLogFilename().'</a>';
    //$topDebug['dag_filename'] = $condorDagDao->getDagFilename();
    $dagFile = $condorDagDao->getDagFilename() . '.dagjob';
    $topDebug['dag_filename'] = '<a href="'.$this->view->webroot.'/qibench/seedpoints/showfile/?filepath='.$taskDao->getWorkDir().$dagFile.'" target="_blank">'.$dagFile.'</a>';




    $this->view->topDebug = $topDebug;

    $condorJobModel = $modelLoad->loadModel('CondorJob', 'batchmake');

    $debugTableHeaders = array(
      'Series number' => ' Series number',
      'LesionUID' => 'LesionUID',
      'Job Definition' => 'Job Definition',
      'Output' => 'Output',
      'Error' => 'Error',
      'Log' => 'Log',
      'Post' => 'Post');

    $this->view->debugTableHeaders = $debugTableHeaders;
    // debug the rows
    $rowDebug = array();
    foreach($runItemDaos as $runItemDao)
      {
      $row = array();
      $row[] = $runItemDao->getCaseId();
      $row[] = $runItemDao->getLesionId();
      $condorJobDao = $condorJobModel->load($runItemDao->getCondorDagJobId());
//      $row[] = $condorJobDao->getJobdefinitionFilename();
      $row[] = '<a href="'.$this->view->webroot.'/qibench/seedpoints/showfile/?filepath='.$taskDao->getWorkDir().$condorJobDao->getJobdefinitionFilename().'" target="_blank">'.$condorJobDao->getJobdefinitionFilename().'</a>';
//      $row[] = $condorJobDao->getOutputFilename();
      $row[] = '<a href="'.$this->view->webroot.'/qibench/seedpoints/showfile/?filepath='.$taskDao->getWorkDir().$condorJobDao->getOutputFilename().'" target="_blank">'.$condorJobDao->getOutputFilename().'</a>';
      $row[] = '<a href="'.$this->view->webroot.'/qibench/seedpoints/showfile/?filepath='.$taskDao->getWorkDir().$condorJobDao->getErrorFilename().'" target="_blank">'.$condorJobDao->getErrorFilename().'</a>';
      $row[] = '<a href="'.$this->view->webroot.'/qibench/seedpoints/showfile/?filepath='.$taskDao->getWorkDir().$condorJobDao->getLogFilename().'" target="_blank">'.$condorJobDao->getLogFilename().'</a>';
      $row[] = '<a href="'.$this->view->webroot.'/qibench/seedpoints/showfile/?filepath='.$taskDao->getWorkDir().$condorJobDao->getPostFilename().'" target="_blank">'.$condorJobDao->getPostFilename().'</a>';
//      $row[] = $condorJobDao->getErrorFilename();
//      $row[] = $condorJobDao->getLogFilename();
//      $row[] = $condorJobDao->getPostFilename();
      $rowDebug[] = $row;
      }
    $this->view->debugTableData = $rowDebug;















    }

  /**
   * action to display a file, dumping it to the screen
   */
  public function showfileAction()
    {
    $filepath = $this->_getParam('filepath');
    $this->view->header = $this->t("Contents of [$filepath]");
    $contents = file_get_contents($filepath);
    $this->view->filecontents = $contents;

    }
}

//end class