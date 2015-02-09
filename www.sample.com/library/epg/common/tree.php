<?php

/**
 * tree.php
 * 无限级分类解析成树形
 *
 * @author      hfcorriez <hfcorriez@gmail.com>
 * @version     $Id: tree.php v 0.1 2011-03-22 17:50:46 hfcorriez $
 * @copyright   Copyright (c) 2007-2011 PPLive Inc.
 * @todo        排序问题，目前父分类必须比子分类靠前
 * @example
  $arr = array(
  1 => array('id' => '1', 'parentid' => 0, 'name' => '一级栏目一'),
  2 => array('id' => '2', 'parentid' => 0, 'name' => '一级栏目二'),
  3 => array('id' => '3', 'parentid' => 1, 'name' => '二级栏目一'),
  4 => array('id' => '4', 'parentid' => 1, 'name' => '二级栏目二'),
  5 => array('id' => '5', 'parentid' => 2, 'name' => '二级栏目三'),
  6 => array('id' => '6', 'parentid' => 3, 'name' => '三级栏目一'),
  7 => array('id' => '7', 'parentid' => 3, 'name' => '三级栏目二'),
  );

  $tree = new tree();
  print_r($tree->set_array($arr)->get_tree());
  print_r($tree->set_array($arr)->get_node(4));
  print_r($tree->set_array($arr)->get_top(7));
 *
 */
class Tree {

    private $tree;
    private $parent;
    private $top;
    private $path;
    //列名设置
    private $_id = 'id';
    private $_pid = 'pid';
    private $_sub = 'sub';
    private $_name = 'name';

    /**
     * 设置数组
     * @param array $array      数组
     * @return tree
     */
    public function set_array($array) {
        $this->reset();
        $this->build_tree_node($array);
        return $this;
    }

    /**
     * 设置键名
     * @param array $array      键名配置
     */
    public function set_key($array) {
        foreach ($array as $k => $v) {
            $param = '_' . $k;
            $this->{$param} = $v;
        }
        return $this;
    }

    /**
     * 获取树形结构
     * @return array            属性结构
     */
    public function get_tree() {
        return $this->tree;
    }

    /**
     * 获取子节点
     * @param int $id
     * @return array            某个节点
     */
    public function get_node($id) {
        return $this->parent[$id];
    }

    /**
     * 获取Path
     * @param int $id
     * @return array            某个节点
     */
    public function get_path($id) {
        return $this->path[$id];
    }

    /**
     * 获取根节点
     * @param int $id           节点ID
     * @return array            根节点
     */
    public function get_top($id) {
        return $this->top[$id];
    }

    /**
     * 标记选中位置
     * @param int $id
     */
    public function mark($id) {
        $path = $this->path[$id];
        count($path) > 0 && array_pop($path);
        $len = count($path);
        if ($len > 0) {
            $tmp = &$this->tree[$path[0]];
            $tmp['mark'] = true;
            for ($i = 1; $i < $len; $i++) {
                $tmp = &$tmp['sub'][$path[$i]];
                $tmp['mark'] = true;
            }
            $tmp['sub'][$id]['mark'] = true;
        }
        return $this;
    }

    /**
     *
     * @param <type> $id
     * @return <type> 
     */
    public function get_all($id) {
        return array(
            'id' => $id,
            'node' => $this->get_node($id),
            'top' => $this->get_top($id),
            'path' => $this->get_path($id),
            'level' => count($this->get_path($id)) - 1,
            'tree' => $this->get_tree(),
        );
    }

    /**
     * 分析树节点
     * @staticvar array $parent 保存父级引用
     * @param array $array     节点
     */
    private function build_tree_node($array) {
        $p = null;
        if (!isset($array[$this->_id])) {
            foreach ($array as $k => $v) {
                $this->build_tree_node($v);
            }
        } elseif ($array) {
            $pid = $array[$this->_pid];
            $id = $array[$this->_id];
            $name = $array[$this->_name];
            if ($pid == 0) {
                $array['level'] = 0;
                $this->tree[$id] = $array;
                $this->parent[$id] = &$this->tree[$id];
                $this->top[$id] = $array;
                $this->path[$id] = array($id);
            } else {
                $array['level'] = count($this->path[$pid]);
                $p = &$this->parent[$pid];
                $p[$this->_sub][$id] = $array;
                $this->parent[$id] = &$p[$this->_sub][$id];
                $this->top[$id] = &$this->top[$pid];
                $this->path[$id] = array_merge(is_array($this->path[$pid]) ? $this->path[$pid] : array(), array($id));
            }
        }
    }

    /**
     * 重置
     */
    private function reset() {
        $this->tree = array();
        $this->parent = array();
    }

    /**
     * 单例
     * @staticvar string $instance
     * @return self
     */
    public function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

}

?>
