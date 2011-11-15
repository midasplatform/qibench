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
    // Zach added time limit in case that helped, it didn't
    set_time_limit(0); 
    if(!$this->logged || !$this->userSession->Dao->getAdmin() == 1)
      {
      $this->_redirect('/');
      //throw new Zend_Exception("You should be an administrator");
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
      
   // added cleaning the output buffer in case that would help, it didn't   
//ob_start();

      
      list($runDao, $jobConfigParams) = $this->ModuleComponent->Execute->runDemo($this->userSession->Dao, $inputFolderId, $outputFolderId);
//ob_end_clean();
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
     
/* 
 * 
 * some test data I was playing around with,
 * when i display this and don't actually run the runDemo, the view displays
 */      
/*      
//      $tableHeaders = array(
//  'case_id' => 'case id',
//  'lesion_id' => 'lesion id',
//  'seed_x' => 'seed x');
   $this->view->tableHeaders = $tableHeaders;
  
      $tableData = array();
      for($i = 0; $i < sizeof($tableHeaders); $i++)
        {
        $row = array();
        for($j = 0; $j < sizeof($tableHeaders); $j++)
          {
          $row[] = $i * $j;        
          }
        $tableData[] = $row;
        }
      $this->view->tableData = $tableData;
*/
      $this->view->tableData = $outputTable;
      
      
      
      
      
      
      
      
      
      
      
      
      
    
      } 
      else 
        {
        // not a post request, just display the seedpoints

          
        $this->view->header = $this->t("Lesion Seedpoints");
        //$this->view->seedpointDaos = $this->Qibench_Lesionseedpoint->getAll();
        $seedpointDaos = $this->Qibench_Lesionseedpoint->getAll();
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


}

//end class