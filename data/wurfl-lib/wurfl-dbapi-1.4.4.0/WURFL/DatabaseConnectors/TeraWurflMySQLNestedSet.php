<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING.txt file distributed with this package.
 *
 * @package    WURFL_Database
 * @copyright  ScientiaMobile, Inc.
 * @author     Steve Kamerman <steve AT scientiamobile.com>
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * Takes an existing table with data in ancestor..descendent hierarchial format
 * and ALTERs the table by adding a right and left (`rt` and `lt`) columns.  These
 * columns contain the nested set relationships between the nodes.  This makes certain
 * lookups (like fallback trees) extremely simple and very fast.
 * 
 * This class is used by Tera-WURFL's MySQL5 Nested Set Database Connector
 * 
 * @package TeraWurflDatabase
 */
class TeraWurflMySQLNestedSet {
	
	/**
	 * How deeply the recursion can go into the tree
	 * @var int
	 */
	protected $max_level = 100;
	protected $dbcon;
	protected $left;
	protected $source_table;
	protected $index_table;
	protected $column_node_id;
	protected $column_parent_id;
	protected $column_left;
	protected $column_right;	
	protected $child_query;
	protected $update_query;
	
	public $numQueries = 0;
	
	public function __construct(MySQLi &$dbcon,$source_table,$index_table,$column_node_id,$column_parent_id,$column_left='lt',$column_right='rt'){
		$this->dbcon =& $dbcon;
		$this->source_table = $source_table;
		$this->index_table = $index_table;
		$this->column_node_id = $column_node_id;
		$this->column_parent_id = $column_parent_id;
		$this->column_left = $column_left;
		$this->column_right = $column_right;
	}
	public function generateNestedSet($root_node_id='generic'){
		$this->left = 1;
		$this->child_query = sprintf("SELECT `%s` FROM %s WHERE `%s` = '?' ORDER BY `%s`",
			$this->column_node_id,
			$this->source_table,
			$this->column_parent_id,
			$this->column_node_id
		);
		$this->child_query = str_replace('?','%s',$this->child_query);
		$this->update_query = sprintf("UPDATE %s SET `?` = '?' WHERE `%s` = '?'",
			$this->index_table,
			$this->column_node_id
		);
		$this->update_query = str_replace('?','%s',$this->update_query);
		$this->dropColumns();
		$this->addColumns();
		$this->processChildren($root_node_id,1);
		$this->addIndices();
	}
	public function queryGetAncestors($id,$order="ASC"){
		$query = sprintf("SELECT parent.%s FROM %s AS node, %s AS parent
WHERE node.%s BETWEEN parent.%s AND parent.%s
AND node.%s = '%s'
ORDER BY parent.%s %s",
			$this->column_node_id,
			$this->index_table,
			$this->index_table,
			$this->column_left,
			$this->column_left,
			$this->column_right,
			$this->column_node_id,
			$id,
			$this->column_right,
			$order
		);
		$this->numQueries++;
		$res = $this->dbcon->query($query);
		$data = array();
		if($res->num_rows == 0) return $data;
		while($row = $res->fetch_assoc()){
			$data[]=$row[$this->column_node_id];
		}
		return $data;
	}
	public function dropColumns(){
		try{
			$query = "ALTER TABLE %s DROP COLUMN `%s`, DROP COLUMN `%s`";
			$this->numQueries++;
			$this->dbcon->query(sprintf($query,$this->index_table,$this->column_left,$this->column_right));
		}catch(Exception $e){return false;}
		return true;
	}
	public function addColumns(){
		try{
			$query = "ALTER TABLE %s ADD COLUMN `%s` int(11) NULL";
			$this->numQueries+=2;
			$this->dbcon->query(sprintf($query,$this->index_table,$this->column_left));
			$this->dbcon->query(sprintf($query,$this->index_table,$this->column_right));
		}catch(Exception $e){return false;}
		return true;
	}
	public function addIndices(){
		try{
			$query = "ALTER TABLE %s ADD UNIQUE (`%s`), ADD UNIQUE (`%s`)";
			$this->numQueries++;
			$this->dbcon->query(sprintf($query,$this->index_table,$this->column_left,$this->column_right));
		}catch(Exception $e){return false;}
		return true;
	}
	protected function processChildren($node_id,$level){
		if($level >= $this->max_level) return;
		// Update LEFT
		$this->numQueries++;
		if(!$this->dbcon->query(sprintf($this->update_query,$this->column_left,$this->left++,$node_id))){throw new Exception($this->dbcon->error);}
		// Find children of this device
		$this->numQueries++;
		if(!$res = $this->dbcon->query(sprintf($this->child_query,$node_id))){throw new Exception($this->dbcon->error);}
		if($res->num_rows == 0){
			// Dead end.  Update RIGHT
			$this->numQueries++;
			if(!$this->dbcon->query(sprintf($this->update_query,$this->column_right,$this->left++,$node_id))){throw new Exception($this->dbcon->error);}
			return;
		}
		while($row = $res->fetch_assoc()){
			$this->processChildren($row[$this->column_node_id],$level+1);
		}
		// No more children.  Update RIGHT
		$this->numQueries++;
		if(!$this->dbcon->query(sprintf($this->update_query,$this->column_right,$this->left++,$node_id))){throw new Exception($this->dbcon->error);}
	}
}