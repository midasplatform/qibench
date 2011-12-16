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
class Qibench_LesionseedpointModelBase extends Qibench_AppModel {

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

  function getAll($seedpointItemrevisionId, $userDao)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $itemModel = $modelLoad->loadModel('Item');
    $bitstreamModel = $modelLoad->loadModel('Bitstream');
    $itemRevisionModel = $modelLoad->loadModel('ItemRevision');
    $folderModel = $modelLoad->loadModel('Folder');
    $bitstreamModel = $modelLoad->loadModel('Bitstream');
    $assetstoreModel = $modelLoad->loadModel('Assetstore');
    $itempolicyuserModel = $modelLoad->loadModel('Itempolicyuser');

    $itemrevisionDao = $itemRevisionModel->load($seedpointItemrevisionId);
    $itemDao = $itemModel->load($itemrevisionDao->getItemId());

    if(!$itemModel->policyCheck($itemDao, $userDao))
      {
      throw new Zend_Execption("user does not have read access to this item");
      }

    $bitstreamDaos = $itemrevisionDao->getBitstreams();
    if(empty($bitstreamDaos) || sizeof($bitstreamDaos) != 1)
      {
      throw new Zend_Execption("unexpected number of bitstreams");
      }
    $bitstream = $bitstreamDaos[0];
    $filepath = $bitstream->getAssetstore()->getPath().'/'.$bitstream->getPath();
    if(!file_exists($filepath))
      {
      throw new Zend_Execption($filepath." is not a valid file path");
      }

    $contents = file_get_contents($filepath);

    // get all of the column names from the model
    // discard the primary key column
    // add these columns to an integer indexed array, used to pull
    // corresponding column names for column data, based on the column index
    //$mainData = $lesionseedpointModel->getMainData();
    $mainDataCols = array();
    ///$mainDataKey = $lesionseedpointModel->getKey();
    foreach($this->_mainData as $colName => $val)
      {
      if($colName === $this->_key)
        {
        continue;
        }
      else
        {
        $mainDataCols[] = $colName;
        }
      }

    // loop through rows, create a Dao for each and save it
    $lesionseedpointDaos = array();
    $separator = ",";
    $rows = explode("\n", $contents);
    $separator = ",";
    foreach($rows as $ind => $row)
      {
      $row = trim($row);
      if(empty($row))
        {
        continue;
        }
      //if($ind === 0 && $headersIncluded)
      //  {
      //  continue;
      //   }
      // else
      //   {
      // parse the row
      $cols = explode($separator, $row);
      $lesionseedpointDao = new Qibench_LesionseedpointDao();
      foreach($cols as $ind => $col)
        {
        // get the column name for this column datum
        $mainDataCol = $mainDataCols[$ind];
        $lesionseedpointDao->set($mainDataCol, $col);
        }
      //$lesionseedpointModel->save($lesionseedpointDao);
      $lesionseedpointDaos[] = $lesionseedpointDao;
      //}
      }
    return $lesionseedpointDaos;


    }



}  // end class Qibench_LesionseedpointModelBase