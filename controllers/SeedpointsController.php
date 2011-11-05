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


  /** simple upload*/
  public function uploadAction()
    {
    if(!$this->logged)
      {
      throw new Zend_Exception("You have to be logged in to do that");
      }
    if(!$this->getRequest()->isXmlHttpRequest() && !$this->isTestingEnv())
      {
      throw new Zend_Exception("Error, should be an ajax action.");
      }
    $this->disableLayout();
    $this->view->form = $this->getFormAsArray($this->Form->Upload->createUploadLinkForm());
    $this->userSession->uploaded = array();
    $this->view->selectedLicense = Zend_Registry::get('configGlobal')->defaultlicense;

    $this->view->defaultUploadLocation = $this->userSession->Dao->getPrivatefolderId();
    $this->view->defaultUploadLocationText = $this->t('My Private Folder');

    $parent = $this->_getParam('parent');
    if(isset($parent))
      {
      $parent = $this->Folder->load($parent);
      if($this->logged && $parent != false)
        {
        $this->view->defaultUploadLocation = $parent->getKey();
        $this->view->defaultUploadLocationText = $parent->getName();
        }
      }

    }//end simple upload


}

//end class
