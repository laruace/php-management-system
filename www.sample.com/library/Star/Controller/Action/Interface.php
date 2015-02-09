<?php
/**
 * @package library\Star\Controller\Action
 */

/**
 * 控制器 接口
 *
 * @package library\Star\Controller\Action
 * @author zhangqy
 *
 */
interface Star_Controller_Action_Interface {
	
	public function __construct(Star_Http_Request $request, Star_Http_Response $response, Star_View $view);
	
	public function setView(Star_View $view);
	
    public function dispatch($action);
}

?>