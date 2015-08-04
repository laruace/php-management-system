<?php

require 'Star/Application/Bootstrap/Bootstrap.php';

class Bootstrap extends Star_Application_Bootstrap_Bootstrap
{
    
	protected function _initLayout()
	{
        return ;
		Star_Layout::startMvc(array(
			'base_path' => APPLICATION_PATH . '/layouts',
			'script_path' => 'default',
		));
	}

	
}