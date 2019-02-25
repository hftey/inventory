<?php

class Admin_AclController extends Venz_Zend_Controller_Action
{

    public function init()
    {
        $actionName = $this->getRequest()->getActionName();
		switch ($actionName){
		case "users" : parent::init("menu_user_management", NULL, NULL, "acl");break;
		default: parent::init(NULL, NULL, NULL, "acl");
		}	
    }
    public function changepasswordAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$Password = $Request->getParam('Password') ? new Zend_Db_Expr("MD5('".$Request->getParam('Password')."')") : false;
		$arrUpdate = array("Password"=>$Password);
		$db->update("ACLUsers", $arrUpdate, "ID=".$this->userInfo->ID);			
		exit();
	}
    public function indexAction()
    {
	
		try {
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}
			$sessionUsers = new Zend_Session_Namespace('sessionUsers');
			$UserResourceName = "UserProfile";
			
			if(!$this->Acl->has($UserResourceName)) 
				$this->Acl->add(new Zend_Acl_Resource($UserResourceName));	
			else
			{
				$view = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "view"); $this->view->chkView = $view ? "checked" : "";
				$edit = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "edit"); $this->view->chkEdit = $edit ? "checked" : "";
				$delete = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "delete"); $this->view->chkDelete = $delete ? "checked" : "";
			
			}
			$Request = $this->getRequest();
			$UpdatePriviledges = $Request->getParam('UpdatePriviledges');

			if ($UpdatePriviledges){
				$view = $Request->getParam('view'); $this->view->chkView = $view ? "checked" : "";
				$edit = $Request->getParam('edit'); $this->view->chkEdit = $edit ? "checked" : "";
				$delete = $Request->getParam('delete'); $this->view->chkDelete = $delete ? "checked" : "";
			}
			
			if ($UpdatePriviledges){
				
				if ($view)
					$this->Acl->allow($this->userInfo->ACLRole, $UserResourceName, "view");
				else
					$this->Acl->deny($this->userInfo->ACLRole, $UserResourceName, "view");
					
				if ($edit)
					$this->Acl->allow($this->userInfo->ACLRole, $UserResourceName, "edit");
				else
					$this->Acl->deny($this->userInfo->ACLRole, $UserResourceName, "edit");

				if ($delete)
					$this->Acl->allow($this->userInfo->ACLRole, $UserResourceName, "delete");
				else
					$this->Acl->deny($this->userInfo->ACLRole, $UserResourceName, "delete");
			
			}
			$this->view->allowView = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "view");
			$this->view->allowEdit = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "edit");
			$this->view->allowDelete = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "delete");

		}catch (Exception $e) {
		
			echo $e->getMessage();
		}
    }
	

        public function usersAction()   
        {
		
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$sqlSearch = "";
			$search_users = $Request->getParam('search_users');	
			$this->view->searchUsers = false;
			$strHiddenSearch = "";
			$this->view->radActive = "checked";
			if ($search_users)
			{
				$this->view->searchUsers = true;
				$Name = $Request->getParam('Name');	
				$Username = $Request->getParam('Username');	
				$Email = $Request->getParam('Email');	
				$ACLRole = $Request->getParam('ACLRole');
                $ManageRental = $Request->getParam('ManageRental') ? 1 : 0;
                $radioActive = $Request->getParam('radioActive');
				$LastLoginStart = $Request->getParam('LastLoginStart');	
				$LastLoginEnd = $Request->getParam('LastLoginEnd');	
				
				if ($LastLoginStart)
					$LastLoginStartSearch = substr($LastLoginStart, 6, 4)."-".substr($LastLoginStart, 3, 2)."-".substr($LastLoginStart, 0, 2);
				
				if ($LastLoginEnd)
					$LastLoginEndSearch = substr($LastLoginEnd, 6, 4)."-".substr($LastLoginEnd, 3, 2)."-".substr($LastLoginEnd, 0, 2);				

				$sqlSearch .= $Name ? " and ACLUsers.Name LIKE '%".$Name."%'" : "";
				$sqlSearch .= $Username ? " and ACLUsers.Username LIKE '%".$Username."%'" : "";
				$sqlSearch .= $Email ? " and ACLUsers.Email LIKE '%".$Email."%'" : "";
				$sqlSearch .= $ACLRole ? " and ACLUsers.ACLRole = '".$ACLRole."'" : "";
                $sqlSearch .= $ManageRental ? " and ACLUsers.ManageRental = ".$ManageRental : "";
                $sqlSearch .= $radioActive ? " and ACLUsers.Active = '".$radioActive."'" : " and ACLUsers.Active IS NULL";
				$sqlSearch .= $LastLoginStart ? " and ACLUsers.LastLogin >= '".$LastLoginStartSearch."'" : "";
				$sqlSearch .= $LastLoginEnd ? " and ACLUsers.LastLogin <= '".$LastLoginEndSearch." 23:59:59'" : "";

				//print $sqlSearch; exit();
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Username = $Username ? $Username : "";				
				$this->view->Email = $Email ? $Email : "";				
				$this->view->ACLRole = $ACLRole ? $ACLRole : "";
                $this->view->ManageRentalCheck = $ManageRental ? "checked" : "";
                $this->view->radioActive = $radioActive ? $radioActive : "";
				$this->view->radActive = $this->view->radioActive ? "checked" : "";
				$this->view->radNotActive = $this->view->radioActive ? "" : "checked";

				
				$this->view->LastLoginStart = $LastLoginStart ? $LastLoginStart : "";				
				$this->view->LastLoginEnd = $LastLoginEnd ? $LastLoginEnd : "";				
				$strHiddenSearch = "<input type=hidden name='search_users' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='Username' value='".$Username."'>";
				$strHiddenSearch .= "<input type=hidden name='Email' value='".$Email."'>";
				$strHiddenSearch .= "<input type=hidden name='ACLRole' value='".$ACLRole."'>";
                $strHiddenSearch .= "<input type=hidden name='ManageRental' value='".$ManageRental."'>";
				$strHiddenSearch .= "<input type=hidden name='radioActive' value='".$radioActive."'>";
				$strHiddenSearch .= "<input type=hidden name='LastLoginStart' value='".$LastLoginStart."'>";
				$strHiddenSearch .= "<input type=hidden name='LastLoginEnd' value='".$LastLoginEnd."'>";

			}

			
			$add_users = $Request->getParam('add_users');	
			if ($add_users)
			{
				$Username = $Request->getParam('Username') ? $Request->getParam('Username') : new Zend_Db_Expr("NULL");
				$ACLPassword = new Zend_Db_Expr("MD5('".$Request->getParam('ACLPassword')."')");
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				$ACLRole = $Request->getParam('ACLRole') ? $Request->getParam('ACLRole') : new Zend_Db_Expr("NULL");
                $ManageRental = $Request->getParam('ManageRental') ? 1 : 0;
                $radioActive = $Request->getParam('radioActive') ? $Request->getParam('radioActive') : false;
				
				$arrInsert = array("ACLRole"=>$ACLRole,"UserCreated"=>$this->userInfo->Username, "Name"=>$Name,"Username"=>$Username,"Email"=>$Email,
                    "ManageRental"=>$ManageRental,"Active"=>$radioActive, "Password"=>$ACLPassword, "DateCreated"=>new Zend_Db_Expr("now()"));

				$db->insert("ACLUsers", $arrInsert);
				$this->_redirect('/admin/acl/users/');  
				
			}
			
			$this->view->edit_users = '';
			$edit_users = $Request->getParam('edit_users');	
			if ($edit_users)
			{
				$this->appMessage->setNotice(3, $this->translate->_('Please leave the password empty if you do not wish to change the password').".");
				$this->view->edit_users = $edit_users;
				$arrUserDetail = $sysAcl->getUsersDetail($edit_users);

				$this->view->Name = $arrUserDetail['Name'];
				$this->view->Username = $arrUserDetail['Username'];		
				$this->view->Email = $arrUserDetail['Email'];		
				$this->view->ACLRole = $arrUserDetail['ACLRole'];
                $this->view->ManageRentalCheck = $arrUserDetail['ManageRental'] ? "checked" : "";
                $this->view->radioActive = $arrUserDetail['Active'];
				$this->view->radActive = $this->view->radioActive ? "checked" : "";
				$this->view->radNotActive = $this->view->radioActive ? "" : "checked";
			}					
		
			$save_users = $Request->getParam('save_users');	
			if ($save_users)
			{
				$ID = $Request->getParam('save_users_id') ? $Request->getParam('save_users_id') : new Zend_Db_Expr("NULL");
				
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Username = $Request->getParam('Username') ? $Request->getParam('Username') : new Zend_Db_Expr("NULL");
				$ACLPassword = $Request->getParam('ACLPassword') ? new Zend_Db_Expr("MD5('".$Request->getParam('ACLPassword')."')") : false;
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				$ACLRole = $Request->getParam('ACLRole') ? $Request->getParam('ACLRole') : new Zend_Db_Expr("NULL");
                $ManageRental = $Request->getParam('ManageRental') ? 1 : 0;
                $radioActive = $Request->getParam('radioActive') ? $Request->getParam('radioActive') : new Zend_Db_Expr("NULL");

				$arrUpdate = array("ACLRole"=>$ACLRole,"Name"=>$Name,"Username"=>$Username,"ManageRental"=>$ManageRental,"Email"=>$Email,"Active"=>$radioActive);
				if ($ACLPassword)
					$arrUpdate['Password'] = $ACLPassword;

				$db->update("ACLUsers", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, $this->translate->_('Record for')." <B>".$Username."</B> ".$this->translate->_('has been updated').".");
				$this->_redirect('/admin/acl/users/'); 
				  				
			}


			$remove_users = $Request->getParam('remove_users');	
			if ($remove_users)
			{
				$db->delete("ACLUsers", "ID=".$remove_users);
				$this->_redirect('/admin/acl/users/');   				
			}			
			
		
			
			
			
			
			
			$this->view->optionsRole = $libDb->getTableOptions('ACLRole', "Description", "Name", $this->view->ACLRole, "ID");			
			
			$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);

			$arrUsers = $sysAcl->getUsers($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataUsers = $arrUsers[1];
			
			function format_date($colnum, $rowdata)
			{

				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_datetime_simple($rowdata[6], "<BR>");
				
			}
			
			function format_date_created($colnum, $rowdata)
			{

				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_datetime_simple($rowdata[7], "<BR>");
				
			}
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return "<a href='/admin/acl/users/edit_users/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteUsers(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}


            function format_rental($colnum, $rowdata)
            {
                return $rowdata[9] ? "<img src='/images/icons/IconYes.gif'>" : "";
            }

            $sessionUsers = new Zend_Session_Namespace('sessionUsers');
			$sessionUsers->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionUsers = new Zend_Session_Namespace('sessionUsers');
				$sessionUsers->numCounter++;
				return $sessionUsers->numCounter;
			}
			
			function format_active($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return $rowdata[5] ? $systemSetting->translate->_('Yes') : $systemSetting->translate->_('No');
			}
			
			$strSearch = "";
			if ($this->view->searchUsers)
				$strSearch = "<input type=hidden name=''>";
			
			$arrHeader = array ('', $this->translate->_('Name'), $this->translate->_('Username'), $this->translate->_('Role'),$this->translate->_('Email'), $this->translate->_('Active'), $this->translate->_('Manage<BR>Rental'), $this->translate->_('Last Login'), $this->translate->_('Date Created'), $this->translate->_('Action'));
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataUsers,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%1%','%2%', '%3%', '%4%', '{format_active}', '{format_rental}', '{format_date}','{format_date_created}','{format_action}'),
					 'sort_column' 	=> array('','Name', 'Username', 'ACLRole', 'Email', 'Active', 'ManageRental', 'LastLogin', 'DateCreated', ''),
					 'alllen' 		=> $arrUsers[0],
					 'title'		=> $this->translate->_('Users'),					 
					 'aligndata' 	=> 'CLLLLCCCCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "1150px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_users = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
		
        }
		
		public function jsonrolesaccessexAction()
		{
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			$roles = $Request->getParam('roles');	
			$allow = $Request->getParam('allow');	
			$deny = $Request->getParam('deny');	
			
			$arrACLRoleMap = array();
			if ($allow)
				$arrACLRoleAll = $db->fetchAll("SELECT * FROM ACLMap where Role = '".$roles."' and Allow=1");
			else if ($deny)	
				$arrACLRoleAll = $db->fetchAll("SELECT * FROM ACLMap where Role = '".$roles."' and Allow=0");
				
				
			foreach ($arrACLRoleAll as $arrACLRole)
			{
				$arrACLRoleMap[$arrACLRole['Resources']][$arrACLRole['Priviledges']] = 1;
			}
			
			
			$arrACLResourcesAll = $db->fetchAll("SELECT * FROM ACLResources order by Name");
			$arrACLPriviledgesAll = $db->fetchAll("SELECT * FROM ACLPriviledges");
		
			$matrix = array();
			foreach ($arrACLPriviledgesAll as $i => $arrACLPriviledges)
			{
				$matrix['priviledges'][$i] = $arrACLPriviledges['Name'];
		
			}
			
			
			
			foreach ($arrACLResourcesAll as $arrACLResources)
			{
				foreach ($arrACLPriviledgesAll as $arrACLPriviledges)
				{
					$matrix['data'][$arrACLResources['Name']][$arrACLPriviledges['Name']] = $arrACLRoleMap[$arrACLResources['Name']][$arrACLPriviledges['Name']] ? true : false;
			
				}
				
			}

			echo Zend_Json::encode($matrix);
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}			
			
			
			
			
			exit();
		}

		
        public function rolesaccessexAction()   
        {	
			try {
				$Request = $this->getRequest();			
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$sysAcl = new Venz_App_System_Acl();
				$libDb = new Venz_App_Db_Table();
				/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
				$accessMapAllow = $Request->getParam('accessMapAllow');
				
				$accessMapDeny = $Request->getParam('accessMapDeny');
				
				$ACLRole = $Request->getParam('ACLRole');	
				$Update = $Request->getParam('Update');	
				$this->view->optionsRole = $libDb->getTableOptions('ACLRole', "Name", "Name", $ACLRole);
				
				if ($Update)
				{
					
					$db->query("DELETE FROM ACLMap where Role = '".$ACLRole."'");
					
					foreach ($accessMapAllow as $indexResourceAllow => $value)
					{
						$arrIndexResourceAllow = explode("|", $indexResourceAllow);
						$arrResourceAllow = array("Role"=>$ACLRole, "Resources" => $arrIndexResourceAllow[0], "Priviledges" => $arrIndexResourceAllow[1], "Allow" => 1);
						$db->insert("ACLMap", $arrResourceAllow);
					}
					
					foreach ($accessMapDeny as $indexResourceDeny => $value)
					{
						$arrIndexResourceDeny = explode("|", $indexResourceDeny);
						$arrResourceDeny = array("Role"=>$ACLRole, "Resources" => $arrIndexResourceDeny[0], "Priviledges" => $arrIndexResourceDeny[1], "Allow" => 0);
						$db->insert("ACLMap", $arrResourceDeny);
					}					
					
				}
				
			}catch (Exception $e)
			{
				echo $e->getMessage();
			}
						
		}

		
        public function rolesaccessAction()   
        {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////
			$add_rolesaccess = $Request->getParam('add_rolesaccess');	
			if ($add_rolesaccess)
			{
				$Role = $Request->getParam('Role');	
				$Resources = $Request->getParam('Resources');
				$Priviledges = $Request->getParam('Priviledges');
				$Access = $Request->getParam('Access');
				if (!$sysAcl->isRolesaccess($Role, $Resources, $Priviledges))
				{
					$arrInsert = array("Role"=>$Role,"Resources"=>$Resources,"Priviledges"=>$Priviledges,"Allow"=>$Access);
					$db->insert("ACLMap", $arrInsert);
				}
				$this->_redirect('/admin/acl/rolesaccess/');   				
			}
			
			$save_rolesaccess = $Request->getParam('save_rolesaccess');	
			if ($save_rolesaccess)
			{
				$Role = $Request->getParam('Role');	
				$Resources = $Request->getParam('Resources');
				$Priviledges = $Request->getParam('Priviledges');
				$Access = $Request->getParam('Access');		
				$ID = $Request->getParam('save_rolesaccess_id');	
				if (!$sysAcl->isRolesaccess($Role, $Resources, $Priviledges))
				{
					$arrUpdate = array("Role"=>$Role,"Resources"=>$Resources,"Priviledges"=>$Priviledges,"Allow"=>$Access);
					$db->update("ACLMap", $arrUpdate, "ID=".$ID);
				}
				$this->_redirect('/admin/acl/rolesaccess/');   				
			}


			$remove_rolesaccess = $Request->getParam('remove_rolesaccess');	
			if ($remove_rolesaccess)
			{
				$db->delete("ACLMap", "ID=".$remove_rolesaccess);
				$this->_redirect('/admin/acl/rolesaccess/');   				
			}			
			
			$this->view->edit_rolesaccess = '';
			$edit_rolesaccess = $Request->getParam('edit_rolesaccess');	
			if ($edit_rolesaccess)
			{
				$this->view->edit_rolesaccess = $edit_rolesaccess;
				$arrRolesaccessDetail = $sysAcl->getRolesaccessDetail($edit_rolesaccess);
				$this->view->Role = $arrRolesaccessDetail['Role'];			
				$this->view->Resources = $arrRolesaccessDetail['Resources'];		
				$this->view->Priviledges = $arrRolesaccessDetail['Priviledges'];		
				$this->view->Access = $arrRolesaccessDetail['Access'];		
			}			
			
			$sqlSearch = "";
			$search_rolesaccess = $Request->getParam('search_rolesaccess');	
			$strHiddenSearch = "";
			if ($search_rolesaccess)
			{
				$Role = $Request->getParam('Role');	
				$sqlSearch .= $Role ? " and Role LIKE '%".$Role."%'" : "";
				
				$Resources = $Request->getParam('Resources');	
				$sqlSearch .= $Resources ? " and Resources LIKE '%".$Resources."%'" : "";

				$Priviledges = $Request->getParam('Priviledges');	
				$sqlSearch .= $Priviledges ? " and Priviledges LIKE '%".$Priviledges."%'" : "";

				$Access = $Request->getParam('Access');	
				$sqlSearch .= $Access ? " and Allow = ".$Access."" : "";
				
				$this->view->Role = $Role ? $Role : "";				
				$this->view->Resources = $Resources ? $Resources : "";				
				$this->view->Priviledges = $Priviledges ? $Priviledges : "";				
				$this->view->Access = $Access ? $Access : "";				
				$strHiddenSearch = "<input type=hidden name='search_rolesaccess' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Role' value='".$Role."'>";
				$strHiddenSearch .= "<input type=hidden name='Resources' value='".$Resources."'>";
				$strHiddenSearch .= "<input type=hidden name='Priviledges' value='".$Priviledges."'>";
				$strHiddenSearch .= "<input type=hidden name='Access' value='".$Access."'>";
				
			
			}

			
			$this->view->optionsRole = $libDb->getTableOptions('ACLRole', "Name", "Name", $this->view->Role);
			$this->view->optionsResources = $libDb->getTableOptions('ACLResources', "Name", "Name", $this->view->Resources);
			$this->view->optionsPriviledges = $libDb->getTableOptions('ACLPriviledges', "Name", "Name", $this->view->Priviledges);
			
			$this->view->optionsAccess = "";
			foreach (array(1=>'Allow', 0=>'Deny') as $AccessID => $Access)
			{
				if ($this->view->Access == $AccessID && strlen($AccessID) == 0)
					$this->view->optionsAccess .= "<OPTION value='".$AccessID."' SELECTED>".$Access;
				else
					$this->view->optionsAccess .= "<OPTION value='".$AccessID."'>".$Access;
			}
			
			$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
			$arrRolesaccess = $sysAcl->getRolesaccess($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataRolesaccess = $arrRolesaccess[1];
			
			function format_access($colnum, $rowdata)
			{
				return $rowdata[4] ? "Allow" : "Deny";
			}			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');			
				return "<a href='/admin/acl/rolesaccess/edit_rolesaccess/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteRolesaccess(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}
			$sessionRolesaccess = new Zend_Session_Namespace('sessionRolesaccess');
			$sessionRolesaccess->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionRolesaccess = new Zend_Session_Namespace('sessionRolesaccess');
				$sessionRolesaccess->numCounter++;
				return $sessionRolesaccess->numCounter;
			}
			
			$arrHeader = array ('', 'ID', $this->translate->_('Role'), $this->translate->_('Resources'), $this->translate->_('Priviledges'), $this->translate->_('Access'), '');
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataRolesaccess,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','%1%','%2%','%3%','{format_access}', '{format_action}'),					 
					 'sort_column' 	=> array('','ID','Role', 'Resources', 'Priviledges', 'Allow', ''),
					 'alllen' 		=> $arrRolesaccess[0],
					 'title'		=> 'Roles',					 
					 'aligndata' 	=> 'LLLLL',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "700px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_rolesaccess = $displayTable->render();
        			
        }	
	
        public function rolesAction()   
        {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 30 ;
			////////////////////////////////////////////////////////////////////////////////////////
			$add_roles = $Request->getParam('add_roles');	
			if ($add_roles)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');
				$ParentName = $Request->getParam('ParentName');
				$arrInsert = array("Name"=>$Name,"Description"=>$Description,"ParentName"=>$ParentName);
				$db->insert("ACLRole", $arrInsert);
				$this->_redirect('/admin/acl/roles/');   				
			}
			
			$save_roles = $Request->getParam('save_roles');	
			if ($save_roles)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');
				$ParentName = $Request->getParam('ParentName');				
				$ID = $Request->getParam('save_roles_id');	
				$arrUpdate = array("Name"=>$Name,"Description"=>$Description,"ParentName"=>$ParentName);
				$db->update("ACLRole", $arrUpdate, "ID=".$ID);
				$this->_redirect('/admin/acl/roles/');   				
			}


			$remove_roles = $Request->getParam('remove_roles');	
			if ($remove_roles)
			{
				$db->delete("ACLRole", "ID=".$remove_roles);
				$this->_redirect('/admin/acl/roles/');   				
			}			
			
			$this->view->edit_roles = '';
			$edit_roles = $Request->getParam('edit_roles');	
			if ($edit_roles)
			{
				$this->view->edit_roles = $edit_roles;
				$arrRolesDetail = $sysAcl->getRolesDetail($edit_roles);
				$this->view->Name = $arrRolesDetail['Name'];			
				$this->view->Description = $arrRolesDetail['Description'];		
				$this->view->ParentName = $arrRolesDetail['ParentName'];		
			}			
			
			$sqlSearch = "";
			$search_roles = $Request->getParam('search_roles');	
			$strHiddenSearch = "";
			if ($search_roles)
			{
				$Name = $Request->getParam('Name');	
				$sqlSearch .= $Name ? " and Name LIKE '%".$Name."%'" : "";
				
				$Description = $Request->getParam('Description');	
				$sqlSearch .= $Description ? " and Description LIKE '%".$Description."%'" : "";

				$ParentName = $Request->getParam('ParentName');	
				$sqlSearch .= $ParentName ? " and ParentName LIKE '%".$ParentName."%'" : "";

				
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Description = $Description ? $Description : "";				
				$this->view->ParentName = $ParentName ? $ParentName : "";				
				$strHiddenSearch = "<input type=hidden name='search_roles' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='ParentName' value='".$ParentName."'>";
				$strHiddenSearch .= "<input type=hidden name='Description' value='".$Description."'>";

			}


			$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
			$arrRoles = $sysAcl->getRoles($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataRoles = $arrRoles[1];
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');

				$db = Zend_Db_Table::getDefaultAdapter(); 
				$arrMapExist = $db->fetchRow("SELECT * FROM ACLMap where Role ='".$rowdata[1]."'");
				
				if ($arrMapExist)
					return " ** ";
				else
					return "<a href='/admin/acl/roles/edit_roles/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteRoles(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}
			$sessionRoles = new Zend_Session_Namespace('sessionRoles');
			$sessionRoles->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionRoles = new Zend_Session_Namespace('sessionRoles');
				$sessionRoles->numCounter++;
				return $sessionRoles->numCounter;
			}
			
			function format_count($colnum, $rowdata)
			{
			
				$db = Zend_Db_Table::getDefaultAdapter(); 
				return count($db->fetchAll("SELECT * FROM ACLUsers where ACLRole='".$rowdata[1]."'"));
			}
			
			$arrHeader = array ('', 'ID', $this->translate->_('Name'), $this->translate->_('Description'), $this->translate->_('Parent Name'), $this->translate->_('Number of<BR>Users'), 'Action');
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataRoles,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','%1%','%2%','%3%','{format_count}', '{format_action}'),					 
					 'sort_column' 	=> array('','ID','Name', 'Description', 'ParentName', '', ''),
					 'alllen' 		=> $arrRoles[0],
					 'title'		=> $this->translate->_('Roles'),					 
					 'aligndata' 	=> 'LLLLLCL',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "700px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_roles = $displayTable->render();
        			
        }
	
	
	
	
        public function priviledgesAction()   
        {
		
			try {
		
				$Request = $this->getRequest();			
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$sysAcl = new Venz_App_System_Acl();
				$libDb = new Venz_App_Db_Table();
				/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
				
				$sortby = $Request->getParam('sortby');			
				if (strlen($sortby) == 0) $sortby = 'ID';
					
				$ascdesc = $Request->getParam('ascdesc');			
				if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
				
				$showPage = $Request->getParam('Pagerpagenum');			
				if (!$showPage) $showPage = 1; 
					
				$pagerNext = $Request->getParam('Pager_next_page');			
				if (strlen($pagerNext) > 0) $showPage++; 	

				$pagerPrev = $Request->getParam('Pager_prev_page');			
				if (strlen($pagerPrev) > 0) $showPage--; 	
				
				$recordsPerPage = 10 ;
				////////////////////////////////////////////////////////////////////////////////////////
				$add_priviledges = $Request->getParam('add_priviledges');	
				if ($add_priviledges)
				{
					$Name = $Request->getParam('Name');	
					$Description = $Request->getParam('Description');
					$arrInsert = array("Name"=>$Name,"Description"=>$Description);
					$db->insert("ACLPriviledges", $arrInsert);
					$this->_redirect('/admin/acl/priviledges/');   				
				}
				
				$save_priviledges = $Request->getParam('save_priviledges');	
				if ($save_priviledges)
				{
					$Name = $Request->getParam('Name');	
					$Description = $Request->getParam('Description');					
					$ID = $Request->getParam('save_priviledges_id');	
					$arrUpdate = array("Name"=>$Name,"Description"=>$Description);
					$db->update("ACLPriviledges", $arrUpdate, "ID=".$ID);
					$this->_redirect('/admin/acl/priviledges/');   				
				}


				$remove_priviledges = $Request->getParam('remove_priviledges');	
				if ($remove_priviledges)
				{
					$db->delete("ACLPriviledges", "ID=".$remove_priviledges);
					$this->_redirect('/admin/acl/priviledges/');   				
				}			
				
				$this->view->edit_priviledges = '';
				$edit_priviledges = $Request->getParam('edit_priviledges');	
				if ($edit_priviledges)
				{
					$this->view->edit_priviledges = $edit_priviledges;
					$arrPriviledgesDetail = $sysAcl->getPriviledgesDetail($edit_priviledges);
					$this->view->Name = $arrPriviledgesDetail['Name'];			
					$this->view->Description = $arrPriviledgesDetail['Description'];		
				}			
				
				$sqlSearch = "";
				$search_priviledges = $Request->getParam('search_priviledges');	
				$strHiddenSearch = "";
				if ($search_priviledges)
				{
					$Name = $Request->getParam('Name');	
					$sqlSearch .= $Name ? " and Name LIKE '%".$Name."%'" : "";
					
					$Description = $Request->getParam('Description');	
					$sqlSearch .= $Description ? " and Description LIKE '%".$Description."%'" : "";
					
					$this->view->Name = $Name ? $Name : "";				
					$this->view->Description = $Description ? $Description : "";				
					$strHiddenSearch = "<input type=hidden name='search_priviledges' value='true'>";
					$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
					$strHiddenSearch .= "<input type=hidden name='Description' value='".$Description."'>";
							
				
				
				}


				$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
				$arrPriviledges = $sysAcl->getPriviledges($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
				$dataPriviledges = $arrPriviledges[1];
				
				function format_action($colnum, $rowdata)
				{
					$systemSetting = new Zend_Session_Namespace('systemSetting');

					$db = Zend_Db_Table::getDefaultAdapter(); 
					$arrMapExist = $db->fetchRow("SELECT * FROM ACLMap where Priviledges ='".$rowdata[1]."'");
					
					if ($arrMapExist)
						return " ** ";
					else
						return "<a href='/admin/acl/priviledges/edit_priviledges/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeletePriviledges(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
				}
				$sessionPriviledges = new Zend_Session_Namespace('sessionPriviledges');
				$sessionPriviledges->numCounter = $recordsPerPage * ($showPage-1);
				function format_counter($colnum, $rowdata)
				{
					$sessionPriviledges = new Zend_Session_Namespace('sessionPriviledges');
					$sessionPriviledges->numCounter++;
					return $sessionPriviledges->numCounter;
				}
				
				$arrHeader = array ('', 'ID', $this->translate->_('Name'), $this->translate->_('Description'), $this->translate->_('Action'));
				$displayTable = new Venz_App_Display_Table(
					array (
						 'data' => $dataPriviledges,
						 'headings' => $arrHeader,
						 'format' 		=> array('{format_counter}','%0%','%1%','%2%', '{format_action}'),					 
						 'sort_column' 	=> array('','ID','Name', 'Description', ''),
						 'alllen' 		=> $arrPriviledges[0],
						 'title'		=> $this->translate->_('Priviledges'),					 
						 'aligndata' 	=> 'LLLLL',
						 'pagelen' 		=> $recordsPerPage,
						 'numcols' 		=> sizeof($arrHeader),
						 'tablewidth' => "700px",
						 'sortby' => $sortby,
						 'ascdesc' => $ascdesc,
						 'hiddenparam' => $strHiddenSearch,
					)
				);
				$this->view->content_priviledges = $displayTable->render();
        	}catch (Exception $e) {
		
				echo $e->getMessage();
			}		
        }

		
        public function resourcesAction()   
        {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 30 ;
			////////////////////////////////////////////////////////////////////////////////////////
			$add_resources = $Request->getParam('add_resources');	
			if ($add_resources)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');	
				$Category = $Request->getParam('Category');	
				$ParentName = $Request->getParam('ParentName');	
				$arrInsert = array("Name"=>$Name,"Description"=>$Description,"Category"=>$Category,"ParentName"=>$ParentName);
				$db->insert("ACLResources", $arrInsert);
				$this->_redirect('/admin/acl/resources/');   				
			}
			
			$save_resources = $Request->getParam('save_resources');	
			if ($save_resources)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');	
				$Category = $Request->getParam('Category');	
				$ParentName = $Request->getParam('ParentName');					
				$ID = $Request->getParam('save_resources_id');	
				$arrUpdate = array("Name"=>$Name,"Description"=>$Description, "Category"=>$Category, "ParentName"=>$ParentName);
				$db->update("ACLResources", $arrUpdate, "ID=".$ID);
				$this->_redirect('/admin/acl/resources/');   				
			}


			$remove_resources = $Request->getParam('remove_resources');	
			if ($remove_resources)
			{
				$db->delete("ACLResources", "ID=".$remove_resources);
				$this->_redirect('/admin/acl/resources/');   				
			}			
			
			$this->view->edit_resources = '';
			$edit_resources = $Request->getParam('edit_resources');	
			if ($edit_resources)
			{
				$this->view->edit_resources = $edit_resources;
				$arrResourcesDetail = $sysAcl->getResourcesDetail($edit_resources);
				$this->view->Name = $arrResourcesDetail['Name'];			
				$this->view->Description = $arrResourcesDetail['Description'];			
				$this->view->Category = $arrResourcesDetail['Category'];			
				$this->view->ParentName = $arrResourcesDetail['ParentName'];			
			}			
			
			$sqlSearch = "";
			$search_resources = $Request->getParam('search_resources');	
			$strHiddenSearch = "";
			if ($search_resources)
			{
				$Name = $Request->getParam('Name');	
				$sqlSearch .= $Name ? " and Name LIKE '%".$Name."%'" : "";
				
				$Description = $Request->getParam('Description');	
				$sqlSearch .= $Description ? " and Description LIKE '%".$Description."%'" : "";
				
				$Category = $Request->getParam('Category');	
				$sqlSearch .= $Category ? " and Category LIKE '%".$Category."%'" : "";
				
				$ParentName = $Request->getParam('ParentName');	
				$sqlSearch .= $ParentName ? " and ParentName LIKE '%".$ParentName."%'" : "";
				
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Description = $Description ? $Description : "";				
				$this->view->Category = $Category ? $Category : "";				
				$this->view->ParentName = $ParentName ? $ParentName : "";				
				$strHiddenSearch = "<input type=hidden name='search_resources' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='Description' value='".$Description."'>";
				$strHiddenSearch .= "<input type=hidden name='Category' value='".$Category."'>";
				$strHiddenSearch .= "<input type=hidden name='ParentName' value='".$ParentName."'>";
					
			
			
			}



			$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
			$arrResources = $sysAcl->getResources($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataResources = $arrResources[1];
			
			function format_action($colnum, $rowdata)
			{
				
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$arrMapExist = $db->fetchRow("SELECT * FROM ACLMap where Resources ='".$rowdata[1]."'");
				$systemSetting = new Zend_Session_Namespace('systemSetting');	
				if ($arrMapExist)
					return " ** ";
				else
					return "<a href='/admin/acl/resources/edit_resources/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteResources(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}
			$sessionResources = new Zend_Session_Namespace('sessionResources');
			$sessionResources->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionResources = new Zend_Session_Namespace('sessionResources');
				$sessionResources->numCounter++;
				return $sessionResources->numCounter;
			}
			
			$arrHeader = array ('', 'ID', $this->translate->_('Name'), $this->translate->_('Description'), $this->translate->_('Category'), $this->translate->_('Parent Name'), '');
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataResources,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','%1%','%2%','%3%','%4%', '{format_action}'),					 
					 'sort_column' 	=> array('','ID','Name', 'Description', 'Category', 'ParentName', ''),
					 'alllen' 		=> $arrResources[0],
					 'title'		=> $this->translate->_('Resources'),					 
					 'aligndata' 	=> 'LLLL',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "700px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_resources = $displayTable->render();
        			
        }


}

