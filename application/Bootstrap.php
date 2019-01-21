<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{


    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }

    protected function _initDbAdaptersToRegistry()
    {
        $this->bootstrap('multidb');
        $resource = $this->getPluginResource('multidb');
        $Adapter2 = $resource->getDb('jobdb');
        Zend_Registry::set('jobdb',$Adapter2);
    }


    protected function _initAppAutoload()
	{
		// Loading Venz Libraries
		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
			'basePath'  => LIB_PATH . '/Venz',
			'namespace' => 'Venz',
			'resourceTypes' => array(
				'zend' => array(
					'path'      => 'Zend/',
					'namespace' => 'Zend',
				),
				'app' => array(
					'path'      => 'App/',
					'namespace' => 'App',
				)
			)
		));
		
		// Loading Application Libraries
		$autoloader = new Zend_Application_Module_Autoloader(
			array(
			'namespace' => 'App',
			'basePath' => dirname(__FILE__),
			)
		);
		return $autoloader;

	}
	
	
	protected function _initLayoutHelper()
	{
		$this->bootstrap('frontController');
		$layout = Zend_Controller_Action_HelperBroker::addHelper(
			new Zend_Controller_Action_Helper_LayoutLoader());
	}


		
}

