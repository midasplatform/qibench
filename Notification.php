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
require_once BASE_PATH . '/modules/api/library/APIEnabledNotification.php';

/** notification manager*/
class Qibench_Notification extends ApiEnabled_Notification
  {
  public $moduleName = 'qibench';
  public $_moduleComponents=array('Api');

  /** init notification process*/
  public function init()
    {
    $this->enableWebAPI($this->moduleName);
    $this->addCallBack('CALLBACK_CORE_GET_FOOTER_LAYOUT', 'getFooter');
    $this->addCallBack('CALLBACK_CORE_GET_FOOTER_HEADER', 'getHeader');
    $this->addCallBack('CALLBACK_CORE_GET_SUPRA_FOOTER', 'getSupraFooter');
    }//end init

  /** get layout footer */
  public function getFooter()
    {
    return '<script type="text/javascript" src="'.Zend_Registry::get('webroot').'/modules/qibench/public/js/layout/qibench.js"></script>';
    }

  /** get layout header */
  public function getHeader()
    {
    return '<link type="text/css" rel="stylesheet" href="'.Zend_Registry::get('webroot').'/modules/qibench/public/css/layout/qibench.css" />';
    }

  /** get footer content */
  public function getSupraFooter()
    {
    $leftText = "Content of this website is copyright 2011 BBMSC and QI-Bench contributors, unless otherwise noted.  Contact info@bbmsc.com for questions about the use of this site's content.  See here for more information about the web infrastructure.";

    $bbmscLogo = array('img'=>'bbmsc-logo-for-footer.jpg', 'id'=>'bbmsc-footer-logo', 'alt'=>'BBMSC Logo');
    $kitwareLogo = array('img'=>'KitwareLogo.jpg', 'id'=>'kitware-footer-logo', 'alt'=>'Kitware Logo');
    $nistLogo = array('img'=>'nist-logo-for-footer.jpg', 'id'=>'nist-footer-logo', 'alt'=>'NIST Logo');
    $imgs = array($bbmscLogo, $kitwareLogo, $nistLogo);

    $imgsWebroot = Zend_Registry::get('webroot').'/modules/qibench/public/images/';

    $html = '<div id="footer-logos"><span id="copyright">' . $leftText . '</span>';
    foreach($imgs as $img)
      {
      $imgHtml = '<img alt="'.$img['alt'].'" src="'.$imgsWebroot . $img['img'].'">';
      $html .= '<span id="'.$img['id'].'" >' . $imgHtml . '</span>';
      }
    $html .= '</div>';
    return $html;
    }


  } //end class


?>