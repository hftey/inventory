<?php
	class IndexController extends Venz_Zend_Controller_Action {

		public function init()
		{
			parent::init();

			
		}

		public function indexAction() {
			

			$this->view->login = false;
			if(Zend_Auth::getInstance()->hasIdentity())   
			{   

			} 		
		}
	}

?>