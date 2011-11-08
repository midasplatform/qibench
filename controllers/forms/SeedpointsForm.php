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

/**
 * Qibench_SeedpointsForm
 */
class Qibench_SeedpointsForm extends AppForm
{

  /**
   * @method createSeedpointsForm
   * does what it says.
   */
  public function createSeedpointsForm()
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/qibench/seedpoints/execute')
          ->setMethod('post');

    $formElements = array();

    $submit = new  Zend_Form_Element_Submit("submitExecute");
    $submit ->setLabel($this->t("execute"));
    $formElements[] = $submit;


    $form->addElements($formElements);
    return $form;
    }
} // end class
?>
