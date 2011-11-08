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
/** LesionseedpointModel Base class */
abstract class Qibench_LesionseedpointModelBase extends Qibench_AppModel {

  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'qibench_lesionseedpoint';
    $this->_key = 'qibench_lesionseedpoint_id';

    $this->_mainData = array(
      'qibench_lesionseedpoint_id' => array('type' => MIDAS_DATA),
      'case_id' => array('type' => MIDAS_DATA, ),
      'lesion_id' => array('type' => MIDAS_DATA, ),
      'case_id' => array('type' => MIDAS_DATA, ),
      'seed_x' => array('type' => MIDAS_DATA, ),
      'seed_y' => array('type' => MIDAS_DATA, ),
      'seed_z' => array('type' => MIDAS_DATA, ),
      'bounding_box_x0' => array('type' => MIDAS_DATA, ),
      'bounding_box_x1' => array('type' => MIDAS_DATA, ),
      'bounding_box_y0' => array('type' => MIDAS_DATA, ),
      'bounding_box_y1' => array('type' => MIDAS_DATA, ),
      'bounding_box_z0' => array('type' => MIDAS_DATA, ),
      'bounding_box_z1' => array('type' => MIDAS_DATA, ),
      'is_in_physical_space' => array('type' => MIDAS_DATA, ),
       );
    $this->initialize(); // required
    }



  /** Abstract functions */
  abstract function getAll();


}  // end class Qibench_LesionseedpointModelBase