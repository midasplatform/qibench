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
?>
<?php
/** Qibench_LesionseedpointCSVComponent */
class Qibench_LesionseedpointCSVComponent extends AppComponent
  {

  /**
   * will parse the passed in array $rows, assuming each $row in
   * $rows is a string, with column values separated by $separator,
   * and if there is an initial row of headers that should be set in
   * $headersIncluded; for each of these $rows, it will parse the
   * $row, create and then save a LesionseedpointDao.
   * @param type $headersIncluded
   * @param type $separator
   * @param type $rows
   * @return Qibench_LesionseedpointDao
   */
  public function parseAndSave($headersIncluded, $separator, $rows)
    {
    $modelLoad = new MIDAS_ModelLoader();
    $lesionseedpointModel = $modelLoad->loadModel('Lesionseedpoint', 'qibench');
    $lesionseedpointModel->loadDaoClass('LesionseedpointDao', 'qibench');

    // get all of the column names from the model
    // discard the primary key column
    // add these columns to an integer indexed array, used to pull
    // corresponding column names for column data, based on the column index
    $mainData = $lesionseedpointModel->getMainData();
    $mainDataCols = array();
    $mainDataKey = $lesionseedpointModel->getKey();
    foreach($mainData as $colName => $val)
      {
      if($colName === $mainDataKey)
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
    foreach($rows as $ind => $row)
      {
      if($ind === 0 && $headersIncluded)
        {
        continue;
        }
      else
        {
        // parse the row
        $cols = explode($separator, $row);
        $lesionseedpointDao = new Qibench_LesionseedpointDao();
        foreach($cols as $ind => $col)
          {
          // get the column name for this column datum
          $mainDataCol = $mainDataCols[$ind];
          $lesionseedpointDao->set($mainDataCol, $col);
          }
        $lesionseedpointModel->save($lesionseedpointDao);
        $lesionseedpointDaos[] = $lesionseedpointDao;
        }
      }
    return $lesionseedpointDaos;
    }



} // end class
?>
