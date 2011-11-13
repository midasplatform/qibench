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
/** RunItemScalarvalueModel Base class */
abstract class Qibench_RunItemScalarvalueModelBase extends Qibench_AppModel {

  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'qibench_run_item_scalarvalue';
    $this->_key = 'qibench_run_item_scalarvalue_id';
    $this->_daoName = 'RunItemScalarvalueDao';

    $this->_mainData = array(
      'qibench_run_item_scalarvalue_id' => array('type' => MIDAS_DATA),
      'qibench_run_item_id' => array('type' => MIDAS_DATA),
      'name' => array('type' => MIDAS_DATA),
      'value' => array('type' => MIDAS_DATA)
       );
    $this->initialize(); // required
    }

}  // end class Qibench_RunItemScalarvalueModelBase