<?php
    require_once 'Zend/Session.php';
    
    /** Zend_Controller_Action */
    require_once 'Zend/Controller/Action.php';

    class ErrorController extends Zend_Controller_Action
    {
        protected $_namespaceTemplate = 'AFCAS_TEMPLATE';
        
        function init(){
		
        }
        
        public function errorAction()
        {
            $errors = $this->_getParam('error_handler');
            
			switch ($errors->type)
			{
				case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
				case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				
					$this->getResponse()->setHttpResponseCode(404);
					$this->view->message = 'Page not found';
					break;
				default:
				
					$this->getResponse()->setHttpResponseCode(500);
					$this->view_message='Application error' ;
					break;
			}
				
				
			$this->view_exception = $errors->exception;
			$this->view->request = $errors->request ;
        }
    }
