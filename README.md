Active Records - v2.1
===

**What's New?**

- Fix Bugs
- Implemented Method Chaining
- New Active Record's methods
    - *where(), nin(), in(), limit(), orderBy(), like(), notLike()*
- Remove *join()* method from Active Records


**Active Record Database Pattern**
This pattern allows information to be retrieved, inserted, and updated in your database with minimal scripting. In some cases only one or two lines of code are necessary to perform a database action.

Beyond simplicity, It also allows for safer queries, since the values are escaped automatically by the system.

> **Note**: Active Records uses PDO (PHP Data Objects) to interact with MySQL Database.

**Tip**: A great benefit of PDO is that it has an exception class to handle any problems that may occur in our database queries. If an exception is thrown within the try{ } block, the script stops executing and flows directly to the first catch(){ } block.

1. Selecting Data
2. Inserting Data
3. Updating Data
4. Deleting Data
5. Method Chaining

## Documentaion

If you intend to use a Active Record Class inside your PHP Project, open the ./ActiveRecords/config.php file with a text editor and set your database settings.

**./ActiveRecords/config.php**
```php
<?php
/**
 * Configuration File.
 * (You can also define your own custom GLOBAL VARIABLES in this file.)
 *
 * This file contains the following variables :
 * * HOSTNAME
 * * USERNAME
 * * PASSWORD
 * * DATABASE NAME
 *
 */
// Enter your Hostname.
define('HOSTNAME', 'localhost');
// Enter your Username.
define('USERNAME', 'root');
// Enter your Server Password.
define('PASSWORD', '');
// Enter your Database Name.
define('DB_NAME', 'dbname');

```

### Initialize Database Connection

You can initialize database connection by creating an object of this class.
```php
require_once('path/ActiveRecords/ActiveRecords.php');
$db = new MySQL\ActiveRecords();
$db->connect();
```

## Active Records  


### Selecting Data
The following functions allow you to build SQL SELECT statements.

### $db->select();
```php
$query  = $db->select('table_name')->execute();
$result = $query->fetchAll();

// Produces: SELECT * FROM table_name
```

### $db->project();
```php
$query  = $db->select('table_name')->project('col1, col2')->execute();
$result = $query->fetchAll();

// Produces: SELECT col1, col2 FROM table_name
```

### $db->where();
This function enables you to set WHERE clause.
> **Note**: All values passed to this function are escaped automatically, producing safer queries.

**WHERE clause with *AND***
```php
$query  = $db->select('table_name')
                    ->where(array(
                        'col_1' => 'val_1',
                        'col_2' => 'val_2',
                    ))
                    ->execute();
$result = $query->fetchAll();

// Produces: SELECT * FROM table_name WHERE col_1='val_1' AND col_2='val_2'
```

**WHERE clause with *OR***
```php
$query  = $db->select('table_name')
                    ->where(array(
                        'col_1' => 'val_1',
                        'col_2' => 'val_2',
                    ), 'OR')
                    ->execute();
$result = $query->fetchAll();

// Produces: SELECT * FROM table_name WHERE col_1='val_1' OR col_2='val_2'
```

If you use multiple function calls they will be chained together with AND between them:
```php
$query  = $db->select('table_name')
                    ->where(array(
                        'col_1' => 'val_1',
                        'col_2' => 'val_2',
                    ), 'OR')
                    ->where(array(
                        'col_3' => 'val_3',
                        'col_4' => 'val_4',
                    ), 'OR')
                    ->execute();
$result = $query->fetchAll();

// Produces: SELECT * FROM table_name WHERE (col_1='val_1' OR col_2='val_2') AND (col_3='val_3' OR col_4='val_4')
```

###  $db->in()
This function enables you to IN operator in a WHERE clause.
```php
$query  = $db->select('table_name')
                    ->in('col_name', ['value1', 'value2'])
                    ->execute();
$result = $query->fetchAll();
// Produces: SELECT * FROM table_name WHERE col_name IN ('value1', 'value2')
```

### $db->nin()

This function is same as $db->in(), but only the difference is that it will produce NOT IN query as given below:
```php
$query  = $db->select('table_name')
                    ->nin('col_name', ['value1', 'value2'])
                    ->execute();
$result = $query->fetchAll();
// Produces: SELECT * FROM table_name WHERE col_name NOT IN ('value1', 'value2')
```

### $db->like()
This function enables you to LIKE operator in a WHERE clause.
```php
$query  = $db->select('table_name')
                    ->like(array(
                        'col_1' => 'value1',
                        'col_2' => 'value2'
                    ))
                    ->like(array(
                        'col_3' => 'value3',
                        'col_4' => 'value4',
                    ), 'OR')
                    ->execute();
$result = $query->fetchAll();
// Produces: SELECT * FROM table_name WHERE (col_1 LIKE '%value1%' AND col_2 LIKE '%value2%') AND (col_3 LIKE '%value3%' OR col_4 LIKE '%value4%') 
```

### $db->notLike()
This is funcation is same as $db->like(), but it will product NOT LIKE queries instead of LIKE.
```php
$query  = $db->select('table_name')
                    ->notLike(array(
                        'col_1' => 'value1',
                        'col_2' => 'value2'
                    ))
                    ->notLike(array(
                        'col_3' => 'value3',
                        'col_4' => 'value4',
                    ), 'OR')
                    ->execute();
$result = $query->fetchAll();

// Produces: SELECT * FROM table_name WHERE (col_1 NOT LIKE '%value1%' AND col_2 NOT LIKE '%value2%') AND (col_3 NOT LIKE '%value3%' OR col_4 NOT LIKE '%value4%') 
```

### $db->orderBy()

```php
$query  = $db->select('table_name')
                    ->orderBy('id', 'DESC')
                    ->execute();
$result = $query->fetchAll();
                        
// Produces: SELECT * FROM table_name ORDER BY id DESC

$query  = $db->select('table_name')
                    ->orderBy('title DESC, name ASC')
                    ->execute();
$result = $query->fetchAll();
                        
// Produces: SELECT * FROM table_name ORDER BY title DESC, name ASC
```

### $db->limit()
Lets you limit the number of rows you would like returned by the query:
```php
$query  = $db->select('table_name')
                    ->limit(10)
                    ->execute();
$result = $query->fetchAll();
                        
// Produces: SELECT * FROM table_name LIMIT 10
```

The second parameter lets you set a result offset.

```php
$query  = $db->select('table_name')
                    ->limit(10, 20)
                    ->execute();
$result = $query->fetchAll();
                        
// Produces: SELECT * FROM table_name LIMIT 20, 10
```

### Inserting Data

**$db->insert()**
```php
$db->insert('table_name', array(
    'col_1' => 'val_1',
    'col_2' => 'val_2'
)); 

//Produces : INSERT INTO table_name(col_1, val_1) VALUES('val_1', 'val_2')
```

### Updating Data

**$db->update()**
```php
$db->update('table_name', array(
                'col1' => 'value1',
                'col2' => 'value2',
            ))
            ->where(array(
                'id' => 1,
                'col' => 'val'
            ))
            ->execute();

//Produces : UPDATE table_name SET col1='value1', col2='value2' WHERE id=1 AND col='val'

$db->update('table_name', array(
                'col1' => 'value1',
                'col2' => 'value2',
            ))->execute();

//Produces : UPDATE table_name SET col1='value1', col2='value2' 
```

### Deleting Data

**$db->delete()**
```php
$db->delete('table_name')
            ->where(array(
                'id' => 1,
                'col' => 'val'
            ))
            ->execute();

//Produces : DELETE FROM table_name WHERE id=1 AND col='val'

$db->delete('table_name')->execute();

//Produces : DELETE FROM table_name
```

### Other Active Record's Methods

**$db->installSQL()**
```php
$db->installSQL('path/file_name.sql');
//Output: Install SQL file to your connected database
```

**$db->scanTables()**
```php
$result = $db->scanTables();
//Output: Return the list of all tables present in database
```

**$db->query()**
$db->query() method is used to generate custom SQL queries.
```php

// It will return Prepared Statement 
$preparedStmt = $db->query('SELECT * FROM table_name WHERE col_1=:col_1 AND col_2=:col_2'); 

// Bind Parameters
$value1 = 'Value1';
$value2 = 'Value2';
$preparedStmt->bindParam(':col_1', $value1);
$preparedStmt->bindParam(':col_2', $value2);

// Execute Statement
$preparedStmt->execute();

// Fetching Result 
$result = $preparedStmt->fetchAll();
```


