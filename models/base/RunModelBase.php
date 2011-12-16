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
/** RunModel Base class */
abstract class Qibench_RunModelBase extends Qibench_AppModel {

  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'qibench_run';
    $this->_key = 'qibench_run_id';

    $this->_mainData = array(
      'qibench_run_id' => array('type' => MIDAS_DATA),
      'executable_name' => array('type' => MIDAS_DATA),
      'params' => array('type' => MIDAS_DATA),
      'batchmake_task_id' => array('type' => MIDAS_DATA),
      'seedpoints_itemrevision_id' => array('type' => MIDAS_DATA),
      'input_folder_id' => array('type' => MIDAS_DATA),
      'output_folder_id' => array('type' => MIDAS_DATA),
      'condor_dag_id' => array('type' => MIDAS_DATA),
      'date' => array('type' => MIDAS_DATA)
       );
    $this->initialize(); // required
    }

}  // end class Qibench_RunModelBase