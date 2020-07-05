<?php
/**
 * Active Records.
 * @author    Rupinder Singh <rupinder.developer@gmail.com>
 * @copyright 2020
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
class ActiveRecords {
    /**
     * Private Variables
     *
     * @var Boolean    $isConnected
     * @var Connection $handler
     * @var array      $bindParams
     * @var array      $updateCols 
     * @var array      $where
     * 
     * @var string     $orderBy
     * @var string     $select 
     * @var string     $update
     * @var string     $delete
     * @var string     $cols     // Columns for projection
     * @var string     $limit
     */
    private $isConnected;
    private $bindParams;
    private $updateCols;
    private $handler;
    private $orderBy;
    private $select;
    private $update;
    private $delete;
    private $limit;
    private $where;
    private $cols;

    function __construct() {
        // Initialization
        $this->isConnected = false;
        $this->bindParams  = [];
        $this->updateCols  = [];
        $this->orderBy     = '';
        $this->select      = '';
        $this->update      = '';
        $this->delete      = '';
        $this->limit       = '';
        $this->where       = [];
        $this->cols        = '*';
    }//end __construct()

    public function __destruct() {
        $this->isConnected = null;
        $this->handler     = null;
        $this->bindParams  = null;
        $this->updateCols  = null;
        $this->orderBy     = null;
        $this->select      = null;
        $this->update      = null;
        $this->delete      = null;
        $this->limit       = null;
        $this->where       = null;
        $this->cols        = null;
    }//end __destruct()

    public function connect() {
        try {
            // PDO Connection
            $this->handler = new \PDO('mysql:host='.HOSTNAME.';dbname='.DB_NAME, USERNAME, PASSWORD);
            $this->handler->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->isConnected = true;
            return $this->handler;
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }//end connect()

    public function select($tableName) {
        $this->select = $tableName;
        return $this;
    }//end select()

    public function project($cols) {
        $this->cols = $cols;
        return $this;
    }//end project()

    public function where($conditions, $glue = 'AND') {
        $temp   = [];
        $uniqid = uniqid();
        foreach($conditions as $key => $value) {
            array_push($temp, "{$key}=:where_{$key}_{$uniqid}");
            $this->bindParams[":where_{$key}_{$uniqid}"] = $value;
        }
        array_push($this->where, ' ('.implode(" {$glue} ", $temp).')');
        return $this;
    }//end where()

    public function like($conditions, $glue = 'AND') {
        $temp   = [];
        $uniqid = uniqid();
        foreach($conditions as $key => $value) {
            array_push($temp, "{$key} LIKE :like_{$key}_{$uniqid}");
            $this->bindParams[":like_{$key}_{$uniqid}"] = "%{$value}%";
        }
        array_push($this->where, ' ('.implode(" {$glue} ", $temp).')');
        return $this;
    }//end like()

    public function notLike($conditions, $glue = 'AND') {
        $temp   = [];
        $uniqid = uniqid();
        foreach($conditions as $key => $value) {
            array_push($temp, "{$key} NOT LIKE :not_like_{$key}_{$uniqid}");
            $this->bindParams[":not_like_{$key}_{$uniqid}"] = "%{$value}%";
        }
        array_push($this->where, ' ('.implode(" {$glue} ", $temp).')');
        return $this;
    }//end notLike()

    public function in($cols, $values) {
        $i      = 1;
        $temp   = [];
        $uniqid = uniqid();
        foreach($values as $value) {
            array_push($temp, ":{$i}_{$uniqid}");
            $this->bindParams[":{$i}_{$uniqid}"] = $value;
            $i++;
        }
        array_push($this->where, "({$cols} IN (".implode(', ', $temp)."))");
        return $this;
    }//end in()

    public function nin($cols, $values) {
        $i      = 1;
        $temp   = [];
        $uniqid = uniqid();
        foreach($values as $value) {
            array_push($temp, ":{$i}_{$uniqid}");
            $this->bindParams[":{$i}_{$uniqid}"] = $value;
            $i++;
        }
        array_push($this->where, "({$cols} NOT IN (".implode(', ', $temp)."))");
        return $this;
    }//end nin()

    public function orderBy($cols, $sortBy = '') {
        $this->orderBy = " ORDER BY {$cols} {$sortBy} ";
        return $this;
    }//end orderBy()

    public function limit($limit, $offset = null) {
        $this->limit = ' LIMIT '.($offset?$offset.', ':'').' '.$limit;
        return $this;
    }//end limit()

    public function execute() {
        if ($this->isConnected === true) {
            if (count($this->where) > 0) {
                $where = ' WHERE '.implode(' AND ',$this->where);
            } else {
                $where = '';
            }
            
            if ($this->select) {
                $query = $this->handler->prepare('SELECT '.$this->cols.' FROM '.$this->select.$where.$this->orderBy.$this->limit);
                $query->execute($this->bindParams);
            } else if ($this->update) {
                $query = $this->handler->prepare("UPDATE {$this->update} SET ".implode(', ', $this->updateCols).$where);
                $query->execute($this->bindParams);
            } else if ($this->delete) {
                $query = $this->handler->prepare("DELETE FROM {$this->delete}".$where);
                $query->execute($this->bindParams);
            } else {
                $query = null;
            }          
        } else {
            die('Failed to initialize database connection, connect() method is missing.');
        }
        
        // Cleaning up resources
        $this->bindParams = [];
        $this->updateCols = [];
        $this->orderBy    = '';
        $this->select     = '';
        $this->update     = '';
        $this->delete     = '';
        $this->limit      = '';
        $this->where      = [];
        $this->cols       = '*';  
        
        return $query;
    }//end execute()

    public function insert($tableName, $values) {
        if ($this->isConnected === true) {  
            $col        = [];
            $val        = [];
            $bindParams = [];
            foreach($values as $key => $value) {
                array_push($col, $key);
                array_push($val, ":{$key}");
                $bindParams[":{$key}"] = $value;
            }
            $query = $this->handler->prepare("INSERT INTO {$tableName}(".implode(', ', $col).") VALUES(".implode(', ', $val).")");
            return $query->execute($bindParams);
        } else {
            die('Failed to initialize database connection, connect() method is missing.');
        }
    }//end insert()

    public function update($tableName, $values) {
        $this->update = $tableName;
        foreach ($values as $key => $value) {
            array_push($this->updateCols, "{$key}=:update_{$key}");
            $this->bindParams[":update_{$key}"] = $value;
        }
        return $this;
    }//end update()

    public function delete($tableName) {
        $this->delete = $tableName;
        return $this;
    }//end delete()

    public function query($sql) {
        return $this->handler->prepare($sql);
    }//end query()

    public function installSQL($url) {
        if ($this->isConnected === true) { 
            $stmt = file_get_contents($url);
            $query = $this->handler->prepare($stmt);
            return $query->execute();
        } else {
            die('Failed to initialize database connection, connect() method is missing.');
        }
       
    }//end installSQL()

    public function scanTables() {
        if ($this->isConnected === true) {  
            $sql = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = "BASE TABLE" AND ';
            $sql .= 'TABLE_SCHEMA=:dbName';
            $query = $this->handler->prepare($sql);
            $db = DB_NAME;
            $query->bindParam(':dbName', $db);
            $query->execute();
            $array = $query->fetchAll(\PDO::FETCH_ASSOC);
            return $array;
        } else {
            die('Failed to initialize database connection, connect() method is missing.');
        }   
    }//end scanTables()
}//end class
