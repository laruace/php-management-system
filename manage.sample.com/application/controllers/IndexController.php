<?php

class IndexController extends Star_Controller_Action
{
    public function init()
	{
		
	}
	
	public function indexAction()
	{
        return $this->redirect('/admin');
	}
	
	public function helloAction()
	{
        $this->view->title = 'Hello world';
        //$this->view->setNoRender();
	}
    
}

?>