<?php
/**
 * @package library\Star\Model
 */

/**
 *
 * @package library\Star\Model
 * @author qinyang.zhang  2010/05/27
 *
 */
interface Star_Model_Interface {
	
	public function connect($db);
	
	/**
	 *
	 * @param $data
	 */
	public function insert($table, Array $data);
	
	/**��
     * 	 
     * @param $where
	 * @param $data
	 */
	public function update($table, $where, Array $data);
	
	/**
��	 * @param $where
	 */
	public function delete($table, $where);
	
	/**����
	 * @param $select
	 */
	public function fetchAll($where, $conditions = null);
	
	/**
	 * 
	 * @param $id
	 */
	public function fetchOne($id);
	
	/**
	 * sql���
	 * @param $select
	 */
	public function fetchRow($select);
	
	public function fetchCol($select);
	
	/**
	 * ִsql���
	 * @param $sql
	 */
	public function query($sql);
	
	public function select();
	
	public function close();
}

?>