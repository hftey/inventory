<?php

class Database_IndexController extends Venz_Zend_Controller_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {

		$front = Zend_Controller_Front::getInstance();
		$front->throwExceptions(true);
		try {
			$userHelper = new Venz_App_Users_Helper();
			$this->view->userHelper = $userHelper->getUserList();
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}
    }

}

