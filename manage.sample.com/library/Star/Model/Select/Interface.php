<?php
/**
 * @package library\Star\Model\Select
 */

/**
 * 数据库检索 接口
 * 
 * @package library\Star\Model\Select
 * @author zhangqy
 *
 */
interface Star_Model_Select_Interface {
	
	public function where($conditions, $value = null);
	
	public function orWhere($conditions, $value = null);
	
	public function from($table_name, $columns = '*');
	
	public function joinLeft($table_name, $conditions, $columns = '');
	
	public function joinRight($table_name, $conditions, $columns = '');
	
	public function joinInner($table_name, $conditions, $columns = '');
	
	public function having($column);
	
	public function limit($number);
	
	public function limitPage($page, $page_number);
	
	public function group($column);
	
	public function order($order);
}


?>