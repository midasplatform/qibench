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
 *  Qibench_IndexController
 */
class Qibench_IndexController extends Qibench_AppController {

  /**
   * @method indexAction(), will display the index page of the qibench module
   */
  public function indexAction()
    {
    $this->view->header = "Qibench Quantitative Medical Imaging";
    }




}

//end class
