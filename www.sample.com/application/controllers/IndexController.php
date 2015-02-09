<?php

class IndexController extends Star_Controller_Action
{
    public function init()
	{
		
	}
	
	public function indexAction()
	{

        $page = (int) $this->request->getParam('page');

        //$this->openCache('', 0, true);
        //if ($this->hasCache())
        //{
            //return $this->showCache();
        //}
        
        //$this->view->assign('title', 'Hello world!');
        
        $this->view->setJsConfig(array(
            'files' => array('jquery')
        ));
        echo date('Y-m-d H:i:s');
        //$this->disableLayout();
        //$this->view->assign(array('title' => 'Hello world', 'data' => 'fadjfakl'));
        //echo "Hello world";
        
        //$this->view->setNoRender();
        //$this->render('hello');
	}
	
	public function helloAction()
	{
        $this->view->title = 'Hello world';
        //$this->view->setNoRender();
	}
    
}

?>