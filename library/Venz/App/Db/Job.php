<?php

class Venz_App_Db_Job extends Zend_Db_Table_Abstract
{
    protected $_jobdb  = NULL;

    public function __construct($DbMode = Zend_Db::FETCH_ASSOC)
    {
        parent::__construct();
        $this->_jobdb = Zend_Registry::get('jobdb');
        $this->_jobdb->setFetchMode($DbMode);
    }

    public function setFetchMode($DbMode = Zend_Db::FETCH_ASSOC)
    {
        $this->_jobdb->setFetchMode($DbMode);
    }

    public function getTableOptions($table, $displayField = "Name", $IDField = "ID", $defaultValue = null, $orderBy = null, $where = null, $group = null)
    {
        $systemSetting = new Zend_Session_Namespace('systemSetting');
        if ($orderBy)
            $orderBy = " ORDER BY $orderBy ";


        $sql = "select * from `$table` where 1=1 $where GROUP BY $IDField $orderBy ";
        $record = $this->_jobdb->fetchAll($sql);
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



}




?>