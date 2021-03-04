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



        public function ajaxGetPaymentTermsAction()
        {
            $Request = $this->getRequest();
            $strSearch = $Request->getParam('query');
            $arrReturn = array();
            $db = Zend_Db_Table::getDefaultAdapter();
            $arrAll = $db->fetchAll("SELECT PaymentTerms FROM Vendors WHERE PaymentTerms LIKE '%".$strSearch."%' GROUP BY PaymentTerms LIMIT 25 ");
            foreach ($arrAll as $arrData)
            {
                $arrReturn[] = $arrData['PaymentTerms'];
            }
            echo json_encode($arrReturn);

            exit();
        }
	}

?>