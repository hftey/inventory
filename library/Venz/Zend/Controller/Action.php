<?php

class Venz_Zend_Controller_Action extends Zend_Controller_Action
{
	private $_resourceName = '';
	private $_roleName = '';
	private $_priviledgeName = 'view'; // general priviledge to view the page
	private $_pageAllow = false;
	public $userInfo = NULL;
	public $appMessage = NULL;
	public $Acl = NULL;
	
	
	function postDispatch()
	{

		
		$appMsg = new Venz_App_Msg();
		if ($appMsg->gotMsg() || $this->appMessage->gotMsg())
		{
			
			$strMessage = $appMsg->getMsg();
			$this->view->appMsg = $strMessage;
		}

		if ($appMsg->gotNotice() || $this->appMessage->gotNotice())
		{
			
			$strMessage = $appMsg->getNotice();
			$this->view->appNotice = $strMessage;
		}		
		
		
	}	
	
	function init($resourceName = NULL, $recordHistory = NULL, $recordHistory = true, $submenuID = NULL)
    {

		try {

			$db = Zend_Db_Table::getDefaultAdapter();		
			$this->view->moduleName = $this->getRequest()->getModuleName();
			$this->view->controllerName = $this->getRequest()->getControllerName();
			$this->view->actionName = $this->getRequest()->getActionName();
		

			$config_env = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application_env.ini'); 
			Zend_Registry::set('config_env', $config_env); 

			$this->appMessage = new Venz_App_Msg();	

			
			$this->_resourceName = $resourceName;
			if (empty($this->_resourceName)){
				// treat every page without resource name as public page
				$this->_resourceName = 'public';
			}
			$sessionUsers = new Zend_Session_Namespace('sessionUsers');	
			if(Zend_Auth::getInstance()->hasIdentity())   
			{   
				$this->view->login = true;
				$userInfo = Zend_Auth::getInstance()->getStorage()->read(); 
				$this->view->userInfo = $userInfo;
				$this->userInfo = $userInfo;
				$this->_roleName = $userInfo->ACLRole;	
				if ($sessionUsers->Acl){
					$Acl = $sessionUsers->Acl;
				}else{
				
					$Acl = new Venz_Zend_Acl($this->_roleName);
				
				}
			}else{
				$this->_roleName = "User";
				$Acl = new Venz_Zend_Acl($this->_roleName);
				if (!($this->view->moduleName == "auth" && $this->view->controllerName == "index"))
				{
					$this->_redirect("/auth");
				
				}
				
			}
			$this->Acl = $Acl;
			
			$this->_pageAllow = $Acl->isAllowed($this->_roleName, $this->_resourceName, $this->_priviledgeName);
			$appMessage = new Venz_App_Msg();

			if (!$this->_pageAllow)
			{

				$appMessage->setMsg(0, "You have no access to view the page. The system has directed you to the main page");
				$this->_redirect("/");
				//exit();
			}
			
			$arrSettings = $db->fetchRow("SELECT * FROM Settings");
			
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$systemSetting->language = $arrSettings['SettingLanguage'];		
			$systemSetting->currency = $arrSettings['SettingCurrency'];		
			$systemSetting->markup_type = $arrSettings['SettingMarkupType'];		
			$systemSetting->markup = $arrSettings['SettingMarkup'];		

			$systemSetting->userInfo = $userInfo;
			$systemSetting->currentPage = $_SERVER['REQUEST_URI'];
			$sessionUsers->Acl = $Acl;
			
			$config_env = Zend_Registry::get('config_env'); 
			$layout = $this->_helper->layout();
			if ($systemSetting->template)
				$layout->setLayout($systemSetting->template);
			else
				$layout->setLayout("default");

			$systemSetting->arrEnvironments = $config_env->environments->toArray();
			$systemSetting->arrTemplates = $config_env->templates->toArray();
			$systemSetting->arrLanguages = $config_env->language->toArray();
			$systemSetting->arrCurrency = $config_env->currency->toArray();
			$systemSetting->arrMarkupType = $config_env->markup_type->toArray();
			$systemSetting->arrStockStatus = $config_env->stock_status->toArray();
			$systemSetting->arrPOStatus = $config_env->po_status->toArray();

			$langPath = NULL;
			$scriptPath = $this->view->getScriptPaths();
			$languageSet = $systemSetting->language;
			
			// must exist
			$langSysPath = APPLICATION_PATH."/language/".$systemSetting->language."/system.".$systemSetting->arrLanguages[$systemSetting->language][1];	
			$translate = new Zend_Translate($systemSetting->arrLanguages[$systemSetting->language][1], $langSysPath, $systemSetting->language, array('delimiter' => '|'));			
			$translate->addTranslation($langSysPath, $systemSetting->language);
			$langPath = $scriptPath[0].$this->getRequest()->getControllerName()."/".$languageSet."/".$this->getRequest()->getActionName().".".
				$systemSetting->arrLanguages[$languageSet][1];				
			
			if (is_file($langPath))
			{
				$translate->addTranslation($langPath, $languageSet);
			}
			$systemSetting->translate = $translate;		
			$this->translate = $translate;
			$this->view->translate = $translate;				
			
	

		//var_dump($container->toArray());
		$xml = new Zend_Config_Xml(APPLICATION_PATH . '/configs/application_menu.xml', 'navigation');
		
		$container = new Zend_Navigation($xml);
		$this->view->navigation($container);

		$arrSubmenu = array();
		$xml_sub = new Zend_Config_Xml(APPLICATION_PATH . '/configs/application_sub_menu.xml');
		foreach ($xml_sub->submenu->toArray() as $id => $sub_container)
		{
			$arrSubmenu[$id] = $sub_container;
		}


		$container_sub = new Zend_Navigation();
		$this->view->navigation_sub = NULL;
		if ($submenuID){
			$container_sub = new Zend_Navigation($arrSubmenu[$submenuID]);
			$temp = new Zend_View();
			
			$this->view->navigation_sub = $temp->navigation($container_sub);		
			
		}
		

		if ($recordHistory){
			$systemSetting->currentPage = $_SERVER['REQUEST_URI'];
			$systemSetting->moduleName =  $this->getRequest()->getModuleName();
			$systemSetting->controllerName =  $this->getRequest()->getControllerName();
			$systemSetting->actionName =  $this->getRequest()->getActionName();
			
			$config_env = Zend_Registry::get('config_env'); 
			$layout = $this->_helper->layout();
			if ($systemSetting->template)
				$layout->setLayout($systemSetting->template);
			else
				$layout->setLayout("default");		

			$systemSetting->arrTemplates = $config_env->templates->toArray();		
			$strPOST = print_r($_POST, true);
			
		//	print $strPOST; exit();
			
//			foreach ($_POST as $key => $val)
//			{
//				$strPOST .= $key."=".$val."|";
//			}

			$arrData = array("username"=>$userInfo->Username, "role"=>$userInfo->ACLRole, "logtime"=>new Zend_Db_Expr('now()'), "zendmodule"=>$this->getRequest()->getModuleName(),
				"zendcontroller"=>$this->getRequest()->getControllerName(),"zendaction"=>$this->getRequest()->getActionName(),"postdata"=>$strPOST,"IP"=>$_SERVER["REMOTE_ADDR"],
				"getdata"=>$_SERVER['QUERY_STRING']);
			$db = Zend_Db_Table::getDefaultAdapter(); 
						

			$db->insert("SYSLog", $arrData);

		}
		
		//print_r($container_sub->toArray());
		//$this->view->navigation_sub = $container_sub;
		// echo $this->view->navigation()->breadcrumbs();

		//echo "--".$this->view->navigation()->menu();
		
		}catch (Exception $e)
		{
			print $e->getMessage();
		}
		
	}

}
