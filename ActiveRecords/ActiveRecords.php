<?php
/**
 * Active Records.
 * @author    Rupinder Singh <rupinder.developer@yahoo.in>
 * @copyright 2017
 */
namespace MySQL;

require_once dirname(__FILE__).'/config.php';

/**
 * ActiveRecords Class
 *
 * * How to make object of this Class ?
 * > $obj =  new MySQL\ActiveRecords();
 *
 */
class ActiveRecords
{
    /**
     * Private Variables
     *
     * @var Connection $handler
     */
    private $handler;

    /**
     * Private Variables
     * If this variable holds `BINARY` value in it, then all the queries are case-sensitive
     *
     * @var $binary
     */
    private $binary;

    /**
     * This functions help to build the connection with Database.
     *
     * @return mixed
     */
    public function __construct($_binary = '')
    {
        try {
            $this->handler = new \PDO('mysql:host='.HOSTNAME.';dbname='.DB_NAME, USERNAME, PASSWORD);
            $this->handler->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->binary = strtoupper($_binary);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }//end __contruct()

    /**
     * Clean up resources here
     *
     * @return null
     */
    public function __destruct()
    {
        $this->handler = null;
    }//end __destruct()

    /**
     * select() Function.
     *
     * * Working :
     * *
     * * $array = array (
     * *            'col_1' => 'val_1',
     * *            'col_2' => 'val_2'
     * *          );
     * * 1. Code   : $obj->select('table_name');
     * *    Output : SELECT * FROM table_name
     * *
     * * 2. Code   : $obj->select(['col_name_1,col_name_2','table_name']);
     * *    Output : SELECT col_name_1,col_name_2 FROM table_name
     * *
     * * 3. Code   : $obj->select('table_name', $array);
     * *    Output : SELECT * FROM table_name WHERE BINARY[Optional] col_1=val_1 AND BINARY col_2=val2
     * *
     * * 4. Code   : $obj->select('table_name', $array, 'OR');
     * *    Output : SELECT * FROM table_name WHERE BINARY[Optional] col_1=val_1 OR BINARY col_2=val2
     *
     * @param string $table
     * @param array  $condition
     * @param string $glue
     *
     * @return array
     */
    public function select($select, $condition = [], $glue = 'AND')
    {
        if (is_array($select)) {
            $sql = 'SELECT '.$select[0].' FROM '.$select[1];
        } else {
            $sql = 'SELECT * FROM '.$select;
        }

        if (empty($condition)) {
            $query = $this->handler->prepare($sql);
        } else {
            foreach ($condition as $key => $value) {
                $pieces[] = $this->binary.' '.$key.'=:'.$key;
            }
            $where = ' WHERE '.implode(' '.trim($glue).' ', $pieces);
            $query = $this->handler->prepare($sql.$where);
            foreach ($condition as $key => &$value) {
                $query->bindParam(':'.$key, $value);
            }
        }

        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_BOTH);
        return $result;
    }//end select()

    /**
     * join() Function.
     *
     * * Working :
     * *
     * * $array = array (
     * *            'col_1' => 'val_1',
     * *            'col_2' => 'val_2'
     * *          );
     * * 1. Code   : $obj->join('table_1', 'table_2', 'table_1.col_name=table_2.col_name');
     * *    Output : SELECT * FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name;
     * *
     * * 2. Code   : $obj->join('table_1', ['table_2','INNER'], 'table_1.col_name=table_2.col_name');
     * *    Output : SELECT * FROM table_1 INNER JOIN table_2 ON table_1.col_name=table_2.col_name;
     * *
     * * 3. Code   : $obj->join(['col_name_1,col_name_2','table_1'], 'table_2', 'table_1.col_name=table_2.col_name');
     * *    Output : SELECT col_name_1,col_name_2 FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name;
     *
     * * 4. Code   : $obj->join('table_1', 'table_2', 'table_1.col_name=table_2.col_name', $array);
     * *    Output : SELECT * FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name
     * *             WHERE BINARY col_1=val_1 AND BINARY[Optional] col_2=val2;
     *
     * * 5. Code   : $obj->join('table_1', 'table_2', 'table_1.col_name=table_2.col_name', $array, 'OR');
     * *    Output : SELECT * FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name
     * *             WHERE BINARY col_1=val_1 OR BINARY[Optional] col_2=val2;
     *
     * @param mixed  $select
     * @param mixed  $join
     * @param string $on
     * @param array  $condition
     * @param string $glue
     *
     * @return array
     */
    public function join($select, $join, $on, $condition = [], $glue = 'AND')
    {
        if (is_array($select)) {
            $select = 'SELECT '.$select[0].' FROM '.$select[1];
        } else {
            $select = 'SELECT * FROM '.$select;
        }

        if (is_array($join)) {
            $join = ' '.$join[1].' JOIN '.$join[0].' ON '.$on;
        } else {
            $join = ' JOIN '.$join.' ON '.$on;
        }

        if (empty($condition)) {
            $where = '';
        } else {
            foreach ($condition as $key => $value) {
                $pieces[] = $this->binary.' '.$key.'=:'.str_replace('.', '_', $key);
            }
            $where = ' WHERE '.implode(' '.trim($glue).' ', $pieces);
        }
        $query = $this->handler->prepare($select.$join.$where);
        foreach ($condition as $key => &$value) {
            $key = str_replace('.', '_', $key);
            $query->bindParam(':'.$key, $value);
        }

        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_BOTH);
        return $result;
    }//end join()

    /**
     * insert() Function.
     *
     * * Working :
     * * $array = array (
     * *            'col_1' => 'val_1',
     * *            'col_2' => 'val_2'
     * *          );
     * * 1. Code   : $obj->insert('table_name', $array);
     * *    Output : INSERT INTO table_name(col_1, val_1) VALUES('val_1', 'val_2')
     *
     * @param string $table
     * @param array  $values
     *
     * @return boolean
     */
    public function insert($table, $values)
    {
        foreach ($values as $key => $value) {
            $col[] = $key;
            $val[] = ':'.$key;
        }
        $cols  = implode(',', $col);
        $vals  = implode(', ', $val);
        $sql   = 'INSERT INTO '.$table.'('.$cols.') VALUES('.$vals.')';
        $query = $this->handler->prepare($sql);
        foreach ($values as $key => &$value) {
            $query->bindParam(':'.$key, $value);
        }
        $result = $query->execute();
        return $result;
    }//end insert()

    /**
     * update() Function.
     *
     * * Working :
     * * $array1 = array (
     * *            'col_1' => 'val_1',
     * *            'col_2' => 'val_2'
     * *          );
     * * $array2 = array (
     * *            'col_name_1' => 'value_1',
     * *            'col_name_2' => 'value_2',
     * *          );
     * * 1. Code   : $obj->update('table_name', $array1, $array2);
     * *    Output : UPDATE table_name SET col_1=val_1, col_2=val_2 WHERE col_name_1=value_1 AND col_name_2=value_2
     * *
     * * 2. Code   : $obj->update('table_name', $array1, $array2, 'OR');
     * *    Output : UPDATE table_name SET col_1=val_1, col_2=val_2 WHERE col_name_1=value_1 OR col_name_2=value_2
     *
     * @param string $table
     * @param array  $values
     * @param array  $condition
     * @param string $glue
     *
     * @return boolean
     */
    public function update($table, $values, $condition = [], $glue = 'AND')
    {
        foreach ($values as $key => $value) {
            $pieces[] = $key.'=:'.$key;
        }
        if (empty($condition) !== true) {
            foreach ($condition as $key => $value) {
                $wherePieces[] = $this->binary.' '.$key.'=:where'.$key;
            }
            $where = ' WHERE '.implode(' '.trim($glue).' ', $wherePieces);
        } else {
            $where = '';
        }
        $update = implode(', ', $pieces);
        $sql    = 'UPDATE '.$table.' SET '.$update.$where;
        $query  = $this->handler->prepare($sql);
        foreach ($values as $key => &$value) {
            $query->bindParam(':'.$key, $value);
        }
        if (empty($condition) !== true) {
            foreach ($condition as $key => &$value) {
                $query->bindParam(':where'.$key, $value);
            }
        }
        $result = $query->execute();
        return $result;
    }//end update()

    /**
     * delete() Function.
     *
     * * Working :
     * * $array = array (
     * *            'col_1' => 'val_1',
     * *            'col_2' => 'val_2'
     * *          );
     * * 1. Code   : $obj->delete('table_name', $array);
     * *    Output : DELETE FROM table_name WHERE col_1=val_1 AND col_2=val_2
     * *
     * * 2. Code   : $obj->delete('table_name', $array, 'OR');
     * *    Output : DELETE FROM table_name WHERE col_1=val_1 OR col_2=val_2
     *
     * @param string $table
     * @param array  $condition
     * @param string $glue
     *
     * @return boolean
     */
    public function delete($table, $condition, $glue = 'AND')
    {
        foreach ($condition as $key => $value) {
            $pieces[] = $this->binary.' '.$key.'=:'.$key;
        }
        $where = ' WHERE '.implode(' '.trim($glue).' ', $pieces);
        $sql   = 'DELETE FROM '.$table.$where;
        $query = $this->handler->prepare($sql);
        foreach ($condition as $key => &$value) {
            $query->bindParam(':'.$key, $value);
        }
        $result = $query->execute();
        return $result;
    }//end delete()

    /**
     * query() Function.
     *
     * @param string $sql
     * @param array  $bind
     *
     * @return Preapared Statement
     */
    public function query($sql, $bind = [])
    {
        $result = $this->handler->prepare($sql);
        if (empty($bind) !== true) {
            foreach ($bind as $key => &$value) {
                $result->bindParam($key, $value);
            }
        }
        return $result;
    }//end query()

    /**
     * installSQL() Function.
     *
     * Working :
     *
     * 1. Example 1st : $obj->installSQL('path/file_name.sql');
     *    Output      : Install SQL file to your connected database
     *
     * @param  $url
     * @return boolean
     */
    public function installSQL($url)
    {
        if ($this->__construct() === true) {
            $stmt = file_get_contents($url);
            $query = $this->handler->prepare($stmt);
            if ($query->execute() === true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }//end installSQL()

    /**
     * dropTables() Function.
     *
     * @return void
     */
    public function dropTables()
    {
        if ($this->__construct() === true) {
            $sql = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = "BASE TABLE" AND ';
            $sql .= 'TABLE_SCHEMA=:dbName';
            $query = $this->handler->prepare($sql);
            $db = DB_NAME;
            $query->bindParam(':dbName', $db);
            $query->execute();
            $array = $query->fetchAll(\PDO::FETCH_BOTH);
            foreach ($array as $table) {
                $queryMain = $this->handler->prepare('DROP TABLE ' . $table['TABLE_NAME']);
                $queryMain->execute();
            }
        }
    }//end dropTables()

    /**
     * scanTables() Function.
     * This function return the list of tables present in your database.
     *
     * @return array
     */
    public function scanTables()
    {
        if ($this->__construct() === true) {
            $sql = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = "BASE TABLE" AND ';
            $sql .= 'TABLE_SCHEMA=:dbName';
            $query = $this->handler->prepare($sql);
            $db = DB_NAME;
            $query->bindParam(':dbName', $db);
            $query->execute();
            $array = $query->fetchAll(\PDO::FETCH_BOTH);
            return $array;
        }
    }//end scanTables()
}//end class
