<?php

class Template_SetController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		//parent::init();
		
    }

    public function indexAction()
    {
		$Request = $this->getRequest();		
		$templateType = $Request->getParam('template');	
		$systemSetting = new Zend_Session_Namespace('systemSetting');
		$systemSetting->template = $templateType;
		$this->_redirect($systemSetting->currentPage);		
		exit();
	}

}

