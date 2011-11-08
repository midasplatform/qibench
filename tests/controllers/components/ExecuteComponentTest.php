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


require_once BASE_PATH.'/modules/qibench/controllers/components/ExecuteComponent.php';


class ExecuteComponentTest extends ControllerTestCase
  {

  protected $executeComponent;

  
  /** set up tests*/
  public function setUp()
    {
    $this->setupDatabase(array('default'));
    $this->enabledModules = array('qibench');
    parent::setUp();
    if(!isset($this->executeComponent))
      {
      $this->executeComponent = new Qibench_ExecuteComponent();
      }
    }

  /** clean up after tests */
  public function tearDown()
    {
    }


  
  
  public function testRunDemo()
    {
    $this->executeComponent->runDemo();
    }


  } // end class
