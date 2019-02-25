<?php
 
class Venz_App_Msg
{
	private $_arrError = array();
	private $_arrSuccess = array();
	private $_arrNotice = array();
	private $_arrInfo = array();
	
	private $_arrNoticeError = array();
	private $_arrNoticeSuccess = array();
	private $_arrNoticeNotice = array();
	private $_arrNoticeInfo = array();
	
	
	const APP_MSG = 0;
	const APP_TITLE = 1;
	
	public function __construct()
	{
		$this->_arrError = new Zend_Session_Namespace('AppError');		
		$this->_arrSuccess = new Zend_Session_Namespace('AppSuccess');		
		$this->_arrNotice = new Zend_Session_Namespace('AppNotice');		
		$this->_arrInfo = new Zend_Session_Namespace('AppInfo');	

		$this->_arrNoticeError = new Zend_Session_Namespace('AppNoticeError');		
		$this->_arrNoticeSuccess = new Zend_Session_Namespace('AppNoticeSuccess');		
		$this->_arrNoticeNotice = new Zend_Session_Namespace('AppNoticeNotice');		
		$this->_arrNoticeInfo = new Zend_Session_Namespace('AppNoticeInfo');
		
	}
	//$errType = 0 (Error) $errType = 1 (Success) $errType = 2 (Notice) $errType = 3 (Info)
    public function setMsg($errType = 0, $strMsg)
    {
		if ($errType == 1)
		{
			$arrSuccess = $this->_arrSuccess->array;
			$arrSuccess[] = $strMsg;
			$this->_arrSuccess->array = $arrSuccess;
		
		}
		else if ($errType == 2)
		{
			$arrNotice = $this->_arrNotice->array;
			$arrNotice[] = $strMsg;
			$this->_arrNotice->array = $arrNotice;
		}
		else if ($errType == 3)
		{
			$arrInfo = $this->_arrInfo->array;
			$arrInfo[] = $strMsg;
			$this->_arrInfo->array = $arrInfo;
		}
		else
		{
			$arrErrors = $this->_arrError->array;
			$arrErrors[] = $strMsg;
			$this->_arrError->array = $arrErrors;
		}
    }
	
	public function gotMsg()
	{
		$bMsg = $this->_arrError->array ? true : ($this->_arrSuccess->array ? true : ($this->_arrNotice->array ? true : ($this->_arrInfo->array ? true : false)));
		return $bMsg;
	}

	public function getMsg()
	{
		$arrErrors = $this->_arrError->array;
		$msgString = '';
		if ($arrErrors){
			foreach ($arrErrors as $errMsg)
			{
				$msgString .= "<img src='/images/icons/IconAlert.png'>&nbsp;".$errMsg . "<BR>";
			}
			$this->_arrError->array = array();
		}
		
		$arrSuccess = $this->_arrSuccess->array;
		if ($arrSuccess){
			foreach ($arrSuccess as $sucMsg)
			{
				$msgString .= "<img src='/images/icons/IconSuccess.gif'>&nbsp;".$sucMsg . "<BR>";
			}
			$this->_arrSuccess->array = array();
		}
		
		$arrNotice = $this->_arrNotice->array;
		if ($arrNotice){
			foreach ($arrNotice as $sucMsg)
			{
				$msgString .= "<img src='/images/icons/IconWarning.png'>&nbsp;".$sucMsg . "<BR>";
			}
			$this->_arrNotice->array = array();		
		}
		
		$arrInfo = $this->_arrInfo->array;
		if ($arrInfo){
			foreach ($arrInfo as $sucMsg)
			{
				$msgString .= "<img src='/images/icons/IconInfo.png'>&nbsp;".$sucMsg . "<BR>";
			}
			$this->_arrInfo->array = array();		
		}		
		
		return $msgString;
	}
    
	
	
	public function setNotice($errType = 0, $strMsg)
    {
		if ($errType == 1)
		{
			
			$arrNoticeSuccess = $this->_arrNoticeSuccess->array;
			$arrNoticeSuccess[] = $strMsg;
			$this->_arrNoticeSuccess->array = $arrNoticeSuccess;
		
		}
		else if ($errType == 2)
		{
			$arrNoticeNotice = $this->_arrNoticeNotice->array;
			$arrNoticeNotice[] = $strMsg;
			$this->_arrNoticeNotice->array = $arrNoticeNotice;
		}
		else if ($errType == 3)
		{
			$arrNoticeInfo = $this->_arrNoticeInfo->array;
			$arrNoticeInfo[] = $strMsg;
			$this->_arrNoticeInfo->array = $arrNoticeInfo;
		}
		else
		{
			$arrNoticeErrors = $this->_arrNoticeError->array;
			$arrNoticeErrors[] = $strMsg;
			$this->_arrNoticeError->array = $arrNoticeErrors;
		}
    }
	
	public function gotNotice()
	{
		$bMsg = $this->_arrNoticeError->array ? true : ($this->_arrNoticeSuccess->array ? true : ($this->_arrNoticeNotice->array ? true : ($this->_arrNoticeInfo->array ? true : false)));
		return $bMsg;
	}

	public function getNotice()
	{
	
		$arrNoticeErrors = $this->_arrNoticeError->array;
		$msgString = '';
		if ($arrNoticeErrors){
			foreach ($arrNoticeErrors as $errMsg)
			{
				$msgString .= "<div class=\"alert alert-error\">&nbsp;".$errMsg . "</div>";
			}
			$this->_arrNoticeError->array = array();
		}
		
		$arrNoticeSuccess = $this->_arrNoticeSuccess->array;
		
		if ($arrNoticeSuccess){
			foreach ($arrNoticeSuccess as $sucMsg)
			{
				$msgString .= "<div class=\"alert alert-success\">&nbsp;".$sucMsg . "</div>";
			}
			$this->_arrNoticeSuccess->array = array();
		}

		$arrNoticeNotice = $this->_arrNoticeNotice->array;
		if ($arrNoticeNotice){
			foreach ($arrNoticeNotice as $sucMsg)
			{
				$msgString .= "<div class=\"alert alert-block\">&nbsp;".$sucMsg . "</div>";
			}
			$this->_arrNoticeNotice->array = array();		
		}
		
		$arrNoticeInfo = $this->_arrNoticeInfo->array;
		if ($arrNoticeInfo){
			foreach ($arrNoticeInfo as $sucMsg)
			{
				$msgString .= "<div class=\"alert alert-info\">&nbsp;".$sucMsg . "</div>";
			}
			$this->_arrNoticeInfo->array = array();		
		}		
		
		return $msgString;
	}
	
	
}




?>