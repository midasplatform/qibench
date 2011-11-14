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


  /**
   * execute an executable pipeline against the parameters
   */
  public function executeAction()
    {
         
    if(!$this->logged || !$this->userSession->Dao->getAdmin() == 1)
      {
      throw new Zend_Exception("You should be an administrator");
      }

    if($this->_request->isPost())
      {
      // for now hard code all the ids
      $inputFolderId = 14; // TODO  HACK HARDCODE
      // the output folder
      $outputFolderId = 15; // TODO HACK HARDCODE
      $jobConfigParams = $this->ModuleComponent->Execute->runDemo($this->userSession->Dao, $inputFolderId, $outputFolderId);
 
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
      $this->view->outputRows = $outputTable;
    
      
      $this->_redirect('/qibench/seedpoints/execute');
      //echo "here";
      //exit();
      
      
      
      
      
      }
      
    }
  
  /** view seedpoints action */
  public function viewAction()
    {
    
    // test code to load test data
    //$filepath = BASE_PATH . '/modules/qibench/tests/testfiles/lesionseedpoints.txt';
    //$contents = file($filepath, FILE_IGNORE_NEW_LINES);
    //$lesionseedpointDaos = $this->ModuleComponent->LesionseedpointCSV->parseAndSave(false, ',', $contents);
 
      
    $this->view->header = $this->t("Lesion Seedpoints");
    $this->view->seedpointDaos = $this->Qibench_Lesionseedpoint->getAll();
    $seedpointsForm = $this->ModuleForm->Seedpoints->createSeedpointsForm();
    $formArray = $this->getFormAsArray($seedpointsForm);
    $this->view->seedpointsForm = $formArray;
    
    }//end view


}

//end class
