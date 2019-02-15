<?php
 
class Venz_App_Db_Table extends Zend_Db_Table_Abstract
{
	protected $_db  = NULL;

	public function __construct($DbMode = Zend_Db::FETCH_ASSOC)
	{
		parent::__construct();
		$this->_db = $this->getAdapter();
		$this->_db->setFetchMode($DbMode);
	}
	
	public function setFetchMode($DbMode = Zend_Db::FETCH_ASSOC)
	{
		$this->_db->setFetchMode($DbMode);
	}
	
	public function getTableOptions($table, $displayField = "Name", $IDField = "ID", $defaultValue = null, $orderBy = null, $where = null)
   {
		$systemSetting = new Zend_Session_Namespace('systemSetting');
	   	if ($orderBy)
   			$orderBy = " ORDER BY $orderBy ";
		
		
   		$sql = "select * from `$table` where 1=1 $where GROUP BY $IDField $orderBy ";

		$record = $this->_db->fetchAll($sql);
		$option_string = "";
		foreach ($record as $index => $TypeData)
		{	
			if (!is_null($TypeData[$displayField])){
				if (!is_null($defaultValue))
				{
					if ($defaultValue == $TypeData[$IDField])
						$option_string .= "<option value=\"".$TypeData[$IDField]."\" selected>".$systemSetting->translate->_($TypeData[$displayField])."</option>";
					else
						$option_string .= "<option value=\"".$TypeData[$IDField]."\">".$systemSetting->translate->_($TypeData[$displayField])."</option>";
				}else
					$option_string .= "<option value=\"".$TypeData[$IDField]."\">".$systemSetting->translate->_($TypeData[$displayField])."</option>";
			}
		}
		return $option_string;
   }
   
	public function getSystemOptions($table, $defaultValue = null, $arrShow = [], $arrHide = [])
   {
   
		$systemSetting = new Zend_Session_Namespace('systemSetting');
		$arrTableAll = $arrTable = $systemSetting->$table;

		// only php 5.6.x can return key
//       if ($arrShow){
//           $arrTable = array_filter($systemSetting->$table, function($key) use($arrShow){
//               return in_array($key, $arrShow);
//           }, ARRAY_FILTER_USE_KEY);
//       }

       if ($arrShow){
           $arrTable = array_filter($systemSetting->$table, function($val) use($arrShow, $arrTableAll){
               $key = array_search($val, $arrTableAll);
               return in_array($key, $arrShow);
           });
       }

        if ($arrHide){
            $arrTable = array_filter($systemSetting->$table, function($val) use($arrHide, $arrTableAll){
                $key = array_search($val, $arrTableAll);
                return !in_array($key, $arrHide);
            });
        }
        $option_string = "";
		foreach ($arrTable as $index => $TypeData)
		{	
			if (is_array($TypeData))
			{
				if (!is_null($defaultValue))
				{
					if ($defaultValue ==$index)
						$option_string .= "<option value='".$index."' selected>".$systemSetting->translate->_($TypeData[1])."</option>";
					else
						$option_string .= "<option value='".$index."'>".$systemSetting->translate->_($TypeData[1])."</option>";
				}else
					$option_string .= "<option value='".$index."'>".$systemSetting->translate->_($TypeData[1])."</option>";
				
			}else
			{
				if (!is_null($defaultValue))
				{
					if ($defaultValue ==$index)
						$option_string .= "<option value='".$index."' selected>".$systemSetting->translate->_($TypeData)."</option>";
					else
						$option_string .= "<option value='".$index."'>".$systemSetting->translate->_($TypeData)."</option>";
				}else
					$option_string .= "<option value='".$index."'>".$systemSetting->translate->_($TypeData)."</option>";
			}
		}
		return $option_string;
		

   }
       
   
    
}



?>