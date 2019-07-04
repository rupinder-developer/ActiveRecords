Active Records
===

PHP Script
---

**Active Record Database Pattern**
This pattern allows information to be retrieved, inserted, and updated in your database with minimal scripting. In some cases only one or two lines of code are necessary to perform a database action.

Beyond simplicity, It also allows for safer queries, since the values are escaped automatically by the system.

> **Note** : This Class only support MySQL Database and uses PDO (PHP Data Objects) to interact with MySQL Database.

1. Selecting Data
2. Inserting Data
3. Updating Data
4. Deleting Data
5. Method Chaining

## Documentaion

If you intend to use a Active Record Class inside your PHP Project, open the ./ActiveRecords/config.php file with a text editor and set your database settings. You can also create global variables inside congfig.php file which you can access all over the framework. 

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

You can use `$db` variable inside your model class to interact with your MySQL Database. 

### Initialize Database Connection

You can initialize database connection by creating an object of this class.
```php
require_once('path/ActiveRecords/ActiveRecords.php');
$db = new MySQL\ActiveRecords();
```

## Active Records  

Followings are the functions which are supported by our Active Records

| Function               | Description                                                                 |
|:-----------------------|:----------------------------------------------------------------------------|
| $db->select()    | Fetch Data from DB *(Returns Multidimensional Array)*.                      |
| $db->join()      | To generate Join Queries *(Returns Multidimensional Array)*.                |
| $db->insert()    | To insert data into your database.                                          |
| $db->update()    | To generate update query.                                                   |
| $db->delete()    | To delete data from your database.                                          |
| $db->query()     | To generate custom database queries.                                        |
| $db->installSQL()| To install SQL file to your connected database.                             |
| $db->dropTables()| To Drop all the tables inside your database.                                |
| $db->scanTables()| Returns the list of tables present in your database.                        |

**$db->select()**

```php
$db->select('table_name');
//Output: SELECT * FROM table_name
```

```php
$db->select([ 'col_name_1, col_name_2', 'table_name' ]);
//Output: SELECT col_name_1,col_name_2 FROM table_name
```

```php
$condition = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$db->select('table_name', $condition);
//Output : SELECT * FROM table_name WHERE col_1=val_1 AND col_2=val2
```

```php
$condition = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$db->select('table_name', $condition, 'OR');
//Output : SELECT * FROM table_name WHERE col_1=val_1 OR col_2=val2
```
**$db->join()**

```php
$db->join('table_1', 'table_2', 'table_1.col_name=table_2.col_name');
//Output : SELECT * FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name;
```

```php
$db->join('table_1', ['table_2','INNER'], 'table_1.col_name=table_2.col_name');
//Output : SELECT * FROM table_1 INNER JOIN table_2 ON table_1.col_name=table_2.col_name;
```

```php
$db->join(['col_name_1,col_name_2','table_1'], 'table_2', 'table_1.col_name=table_2.col_name');
//Output : SELECT col_name_1,col_name_2 FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name;
```

```php
$condition = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$db->join('table_1', 'table_2', 'table_1.col_name=table_2.col_name', $condition);
//Output : SELECT * FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name WHERE col_1=val_1 AND col_2=val2;
```
```php
$condition = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$db->join('table_1', 'table_2', 'table_1.col_name=table_2.col_name', $condition, 'OR');
//Output : SELECT * FROM table_1 JOIN table_2 ON table_1.col_name=table_2.col_name WHERE col_1=val_1 OR col_2=val2;
```

**$db->insert()**
```php
$values = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$db->insert('table_name', $values);
//Output : INSERT INTO table_name(col_1, val_1) VALUES('val_1', 'val_2')
```

**$db->update()**
```php
$query = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$condition = [
    'col_name_1' => 'value_1',
    'col_name_2' => 'value_2',
];

$db->update('table_name', $condition, $condition);
//Output : UPDATE table_name SET col_1=val_1, col_2=val_2 WHERE col_name_1=value_1 AND col_name_2=value_2

$db->update('table_name', $condition, $condition, 'OR');
//Output : UPDATE table_name SET col_1=val_1, col_2=val_2 WHERE col_name_1=value_1 OR col_name_2=value_2
```

**$db->delete()**
```php
$condition = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$db->delete('table_name', $condition);
//Output : DELETE FROM table_name WHERE col_1=val_1 AND col_2=val_2
```
```php
$condition = [
    'col_1' => 'val_1',
    'col_2' => 'val_2'
];
$db->delete('table_name', $condition, 'OR');
//Output : DELETE FROM table_name WHERE col_1=val_1 OR col_2=val_2
```

**$db->installSQL()**
```php
$db->installSQL('path/file_name.sql');
//Output: Install SQL file to your connected database
```

**$db->query()**

query() function is used to generate custom SQL queries and also provides the functionality to bind parameters with in your custom query.

```php
$query = $db->query('SELECT * FROM table_name WHERE col_1=:col_2 AND col_2=:col_2', [ ':col_1'=> 'val_1', ':col_2'=>'val_2' ] );
$query->execute();
$result = $query->fetchAll();

```


