<?php

class User_IndexController extends Venz_Zend_Controller_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
	
		try {
			if (!$this->userInfo){
				$appMessage = new Venz_App_Msg();
				$appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}
			
			$UserResourceName = "UserProfile";
			if(!$this->Acl->has($UserResourceName)) 
				$this->Acl->add(new Zend_Acl_Resource($UserResourceName));	
			
			$Request = $this->getRequest();
			$this->view->allowView = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "view");
			$this->view->allowEdit = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "edit");
			$this->view->allowDelete = $this->Acl->isAllowed($this->userInfo->ACLRole, $UserResourceName, "delete");
			if ($this->view->allowView) print "can view<BR>";
			if ($this->view->allowEdit) print "can edit<BR>";
			if ($this->view->allowDelete) print "can delete<BR>";
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}
    }

}

