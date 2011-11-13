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

/** Component for api methods */
class Qibench_ApiComponent extends AppComponent
{


  /**
   * Pass the args and a list of required parameters.
   * Will throw an exception if a required one is missing.
   */
  private function _validateParams($args, $requiredList)
    {
    foreach($requiredList as $param)
      {
      if(!array_key_exists($param, $args))
        {
        throw new Exception('Parameter '.$param.' is not defined', MIDAS_INVALID_PARAMETER);
        }
      }
    }

  /** Return the user dao */
  private function _getUser($args)
    {
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    return $authComponent->getUser($args,  Zend_Registry::get('userSession')->Dao);
    }


  /**
   * add a scalarvalue to a QibenchRunItem
   * @param qibenchrunitemid 
   * @param name 
   * @param value 
   * @return The QibenchRunItemScalarValueDao
   */
  function runitemscalarvalueAdd($args)
    {
    $this->_validateParams($args, array('qibenchrunitemid', 'name', 'value'));
    $userDao = $this->_getUser($args);
    if(!$userDao)
      {
      throw new Exception('Anonymous users may not add runitemscaluevalue-s');
      }

    $qibenchrunitemid = $args['qibenchrunitemid'];
    $name = $args['name'];
    $value = $args['value'];

    $modelLoader = new MIDAS_ModelLoader();
    $runitemModel = $modelLoader->loadModel('RunItem','qibench');
    $runitem = $runitemModel->load($qibenchrunitemid);
    // HACK SOME CHECKING FOR ITEM VALUE
    $runitemscalarvalueModel = $modelLoader->loadModel('RunItemScalarvalue','qibench');
    $runitemscalarvalueModel->loadDaoClass('RunItemScalarvalueDao', 'qibench');
    $runitemscalarvalueDao = new Qibench_RunItemScalarvalueDao();
    $runitemscalarvalueDao->setQibenchRunItemId($qibenchrunitemid);
    $runitemscalarvalueDao->setName($name);
    $runitemscalarvalueDao->setValue($value);
    $runitemscalarvalueModel->save($runitemscalarvalueDao);
    return $runitemscalarvalueDao;
    }


} // end class




