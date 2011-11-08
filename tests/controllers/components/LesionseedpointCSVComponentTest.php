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


require_once BASE_PATH.'/modules/qibench/controllers/components/LesionseedpointCSVComponent.php';


class LesionseedpointCSVComponentTest extends ControllerTestCase
  {

  protected $colHeaders = array("case_id","LesionUID","x","y","z","x_0","x_1","y_0","y_1","z_0","z_1", "is_in_physical_space");
  protected $data = array(
    array("3","39","313.92","232.29","48.8","305.92","321.92","224.29","240.29","40.8","56.8", "1"),
    array("3","40","271.56","248.64","55.2","263.56","279.56","240.64","256.64","47.2","63.2", "1"),
    array("9","10","118.37","224.71","62","204.71","244.71","204.71","244.71","42","82", "1"),
    array("9","41","142.16","264.98","73.6","132.16","152.16","254.98","274.98","63.6","83.6", "1"),
    array("15","39","313.92","232.29","48.8","305.92","321.92","224.29","240.29","40.8","56.8", "1"),
    array("15","40","271.56","248.64","55.2","263.56","279.56","240.64","256.64","47.2","63.2", "1"));

  
  protected $lesionseedpointCSVComponent;

  
  /** set up tests*/
  public function setUp()
    {
    $this->setupDatabase(array('default'));
    $this->enabledModules = array('qibench');
    parent::setUp();
    if(!isset($this->lesionseedpointCSVComponent))
      {
      $this->lesionseedpointCSVComponent = new Qibench_LesionseedpointCSVComponent();
      }
    }

  /** clean up after tests */
  public function tearDown()
    {
    }


  protected function generateInputRows($headersIncluded, $separator)
    {
    $rows = array();
    if($headersIncluded)
      {
      $rows[] = implode($separator, $this->colHeaders);
      }
    foreach($this->data as $datum)
      {
      $rows[] = implode($separator, $datum);
      }
    return $rows;
    }
    
  protected function spotCheckSaves($dao, $index)  
    {
    $datum = $this->data[$index];
    $this->assertEquals($datum[0], $dao->getCaseId());
    $this->assertEquals($datum[1], $dao->getLesionId());
    $this->assertEquals($datum[2], $dao->getSeedX());
    $this->assertEquals($datum[3], $dao->getSeedY());
    $this->assertEquals($datum[4], $dao->getSeedZ());
    $this->assertEquals($datum[5], $dao->getBoundingBoxX0());
    $this->assertEquals($datum[6], $dao->getBoundingBoxX1());
    $this->assertEquals($datum[7], $dao->getBoundingBoxY0());
    $this->assertEquals($datum[8], $dao->getBoundingBoxY1());
    $this->assertEquals($datum[9], $dao->getBoundingBoxZ0());
    $this->assertEquals($datum[10], $dao->getBoundingBoxZ1());
    $this->assertEquals($datum[11], $dao->getIsInPhysicalSpace());
  }
    

  protected function combinationTest($headersIncluded, $separator)
    {      
    $headersTabData = $this->generateInputRows($headersIncluded, $separator);
    $lesionseedpointDaos = $this->lesionseedpointCSVComponent->parseAndSave($headersIncluded, $separator, $headersTabData);
    // test the count is right
    $this->assertEquals(count($lesionseedpointDaos), count($this->data));
    // test the data and the saved daos
    foreach($lesionseedpointDaos as $index => $dao)
      {
      $this->spotCheckSaves($dao, $index);  
      }
    
    // clear out the data
    $modelLoad = new MIDAS_ModelLoader();
    $lesionseedpointModel = $modelLoad->loadModel('Lesionseedpoint', 'qibench');
    foreach($lesionseedpointDaos as $lesionseedpointDao)
      {
      $lesionseedpointModel->delete($lesionseedpointDao);
      }
    }
    

  
  
  public function testParseAndSave()
    {
    $headersIncludedVals = array( true, false);
    $separatorVals = array('\t', ',');
    foreach($headersIncludedVals as $headersIncluded)
      {
      foreach($separatorVals as $separator)
        {
        $this->combinationTest($headersIncluded, $separator);
        }
      }
    }


  } // end class
