<?php
namespace core;

class db extends \mysqli
{
	protected static $instance;
	private function __clone()    {  }
	private function __wakeup()   {  }

	/**
	 * @param string $db
	 *
	 * @return db
	 */

	public static function getInstance($db = 'main')
	{
		if ( !isset(self::$instance[$db]) || is_null(self::$instance[$db]) ) {
			self::$instance[$db] = new self($db);
		}
		return self::$instance[$db];
	}

	public function __construct($db)
	{
		$conf = parse_ini_file(__DIR__.'/../config.ini', true);
		$conf = $conf['db/'.$db];
		parent::__construct($conf['host'], $conf['user'], $conf['password'], $conf['dbname']);
		$this->set_charset('utf8');
	}

	public function insert($table, $data, $duplicate_update = false)
	{
		foreach ($data as $field => $value) {
			$values[] = "`$field` = '".addcslashes($this->real_escape_string($value), "'")."'";
		}

		$sql = "INSERT INTO `$table` SET ".implode(',' ,$values);
		if ($duplicate_update) {
			$sql .= " ON DUPLICATE KEY UPDATE ".implode(',' ,$values);
		}

		$this->query($sql);

		return $this->error?$this->error:$this->insert_id;
	}

	public function multi_insert($table, $data, $duplicate_update = false)
	{
		foreach ($data as $i => $row) {
			foreach ($row as $field => $value) {
				$fields[$field] = "`$field`";
				$values[$i][$field] = "'".addcslashes($this->real_escape_string($value), "'")."'";
			}
			ksort($values[$i]);
			$values[$i] = '('.implode(',', $values[$i]).')';
		}
		ksort($fields);

		if ($duplicate_update) {
			foreach ($fields as $field) {
				$update_fields[$field] = "$field = VALUES($field)";
			}
		}

		$sql = "INSERT INTO `$table`(".implode(', ' ,$fields).") VALUES ".implode(',' ,$values);
		if ($duplicate_update) {
			$sql .= " ON DUPLICATE KEY UPDATE ".implode(',',$update_fields);
		}

		return $this->query($sql)?:$this->error;
	}

	public function replace($table, $data)
	{
		foreach ($data as $field => $value) {
			$values[] = "`$field` = '".addcslashes($this->real_escape_string($value), "'")."'";
		}

		$sql = "REPLACE INTO `$table` SET ".implode(', ' ,$values);
		return $this->query($sql);
	}

	public function update($table, $data, $params)
	{
		if (is_array($data) && !empty($data)) {
			foreach ($data as $field => $value) {
				$values[] = "`$field` = '".addcslashes($this->real_escape_string($value), "'")."'";
			}

			foreach ($params as $field => $value) {
				$where[] = "`$field` = '".addcslashes($value, "'")."'";
			}
			$sql = 'UPDATE `'.$table.'` SET '.implode(', ' ,$values).' WHERE '.implode(' AND ', $where);

            return $this->query($sql);
		} else {
			return false;
		}
	}

	public function delete($table, $params)
	{
		foreach ($params as $field => $value) {
			if (is_array($value)) {
				foreach ($value as &$val) {
					if ((int)$val != $val) {
						$val = '"'.addcslashes($this->real_escape_string($val), '"').'"';
					}
				}
				$value = implode(',', $value);
				$where[] = "`$field` IN (".$value.")";
			} else {
				$where[] = "`$field` = '".addcslashes($this->real_escape_string($value), "'")."'";
			}
		}

		$sql = 'DELETE FROM `'.$table.'` WHERE '.implode(' AND ', $where);
		if ($this->query($sql)) {
			return $this->affected_rows;
		} else {
			return false;
		}
	}

	public function getFiltered($table, $params = [], $order = [], $limit = [], $fields = '*', $group = [])
	{
		$where = $this->where($params);

		$sql = 'SELECT '.$fields.' FROM `'.$table.'` '.(!is_null($where)?'WHERE '.implode(' AND ', $where):'');
		if (!empty($order)) {
			foreach ($order as $field => $value) {
				$orders[] = $field.' '.$value;
			}
			$sql .= ' ORDER BY '.implode(',',$orders);
		}

		if (!empty($group)) {
			$sql .= ' GROUP BY '.implode(',', $group);
		}

		if (!empty($limit)) {
			$sql .= ' LIMIT '.implode(',', $limit);
		}

		return $this->query($sql)->fetch_all(MYSQLI_ASSOC);
	}

	private function where($params) {
		$where = null;
		if (!empty($params)) {
			foreach ($params as $field => $value) {

				if (!is_array($value)) {
					if (
						substr($value, 0, 2) == '>='
						|| substr($value, 0, 2) == '<='
						|| substr($value, 0, 2) == '!='
					) {
						$where[] = "`$field` " . substr($value, 0, 2) . " '" . $this->real_escape_string(substr($value, 2)) . "'";
					} elseif (
						substr($value, 0, 1) == '>'
						|| substr($value, 0, 1) == '<'
						|| substr($value, 0, 1) == '='
					) {
						$where[] = "`$field` " . substr($value, 0, 1) . " '" . $this->real_escape_string(substr($value, 1)) . "'";
					} elseif (stripos($value, 'SELECT') !== false) {
						$where[] = "`$field` IN ('" . $this->real_escape_string($value) . "')";
					} elseif (is_bool($value)) {
						if ($value) {
							$where[] = "`$field` IS NULL";
						} else {
							$where[] = "`$field` NOT NULL";
						}
					} elseif (
						substr($value, 0, 2) == '&='
					) {
						$where[] = "(`$field` & " . $this->real_escape_string(substr($value, 2)) . ") > 0";
					} elseif (
						substr($value, 0, 2) == '^='
					) {
						$where[] = "(`$field` ^ " . $this->real_escape_string(substr($value, 2)) . ") == 0";
					} else {
						$where[] = "`$field` = '" . $this->real_escape_string($value) . "'";
					}
				} else {
					foreach ($value as &$item) {
						$item = $this->real_escape_string($item);
					}
					$where[] = "`$field` IN ('" . implode("','", $value) . "')";
				}
			}
		}

		return $where;
	}

	public function getRowByKeys($table, $params)
	{
		$where = $this->where($params);
		$sql = 'SELECT * FROM `'.$table.'` '.(!is_null($where)?'WHERE '.implode(' AND ', $where):'');
		return $this->query($sql)->fetch_assoc();
	}

	public function getFoundRows()
	{
		return $this->query('SELECT FOUND_ROWS()')->fetch_assoc()['FOUND_ROWS()'];
	}

	public function query($sql, $resultmode = NULL)
	{
		$res = parent::query($sql);
		if ($this->error) {
            error_log($this->error);
		}
		return $res;
	}

    public function getGrid($table, $data = array(), $offset = 0, $limit = false)
    {
        if (empty($data)) {
            $fields = "*";
        } else {
            $fields = implode(',', $data);
        }
        $sql ='SELECT SQL_CALC_FOUND_ROWS '.$fields.' FROM `'.$table.'`';
        if ($limit) {
            $sql .= "LIMIT `'.$limit.'` OFFSET `'.$offset*$limit.'`";
        }

        return $this->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}