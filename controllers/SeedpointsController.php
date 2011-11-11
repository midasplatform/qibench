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
  public $_moduleComponents = array('Execute');

  //public $_moduleComponents = array('LesionseedpointCSV');

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
        echo "SUCKA";
        
        $this->ModuleComponent->Execute->runDemo($this->userSession->Dao);
    
        
/*      $this->_helper->layout->disableLayout();
      $this->_helper->viewRenderer->setNoRender();
      $submitConfig = $this->_getParam(MIDAS_BATCHMAKE_SUBMIT_CONFIG);

      if(isset($submitConfig))
        {
        // user wants to save config
        $this->archiveOldModuleLocal();
        // save only those properties we are interested for local configuration
        foreach($configPropertiesRequirements as $configProperty => $configPropertyRequirement)
          {
          $fullConfig[MIDAS_BATCHMAKE_GLOBAL_CONFIG_NAME][$this->moduleName.'.'.$configProperty] = $this->_getParam($configProperty);
          }
        $this->Component->Utility->createInitFile(MIDAS_BATCHMAKE_MODULE_LOCAL_CONFIG, $fullConfig);
        $msg = $this->t(MIDAS_BATCHMAKE_CHANGES_SAVED_STRING);
        echo JsonComponent::encode(array(true, $msg));
        }
*/
      }
   /* else
      {
      // populate seedpoints form with values
      $seedpointsForm = $this->ModuleForm->Seedpoints->createSeedpointsForm();
      $formArray = $this->getFormAsArray($seedpointsForm);
      $this->view->seedpointsForm = $formArray;
      }
*/
    }
  
  /** view seedpoints action */
  public function viewAction()
    {
      
    //$filepath = '/home/mgrauer/dev/buckler_nist/bm/lesionseedpoints.txt';
    //$contents = file($filepath, FILE_IGNORE_NEW_LINES);
    //$lesionseedpointDaos = $this->ModuleComponent->LesionseedpointCSV->parseAndSave(false, ',', $contents);
 
      
      
      
    $this->view->header = $this->t("Lesion Seedpoints");
    $this->view->seedpointDaos = $this->Qibench_Lesionseedpoint->getAll();
    //var_dump($lesions);
    /*
    foreach($seedpoints as $ind=>$seedpoint)
  {
  echo '<tr>';
  $row = $seedpoint['NistqiLesionSeedpoint'];
  foreach($row as $colName=>$colVal)*/
    
    
/*    
    $this->view->Date = $this->Component->Date;
    $this->view->Utility = $this->Component->Utility;
    $itemId = $this->_getParam("itemId");
    if(!isset($itemId) || !is_numeric($itemId))
      {
      throw new Zend_Exception("itemId  should be a number");
      }
    $itemDao = $this->Item->load($itemId);
    if($itemDao === false)
      {
      throw new Zend_Exception("This item doesn't exist.");
      }
    if(!$this->Item->policyCheck($itemDao, $this->userSession->Dao))
      {
      throw new Zend_Exception("Problem policies.");
      }

    $this->view->isAdmin = $this->Item->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_ADMIN);
    $this->view->isModerator = $this->Item->policyCheck($itemDao, $this->userSession->Dao, MIDAS_POLICY_WRITE);
    $itemRevision = $this->Item->getLastRevision($itemDao);
*/
    $seedpointsForm = $this->ModuleForm->Seedpoints->createSeedpointsForm();
    $formArray = $this->getFormAsArray($seedpointsForm);
    $this->view->seedpointsForm = $formArray;
    
    }//end view


}

//end class