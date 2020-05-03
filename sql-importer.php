<?php
$f3 = require_once('fatfree/lib/base.php');

if ((float)PCRE_VERSION < 7.9) {
    trigger_error('PCRE version is out of date');
}

// Read configs
$f3->config('configs/config.ini');

function importSqlFile($pdo, $sqlFile, $tablePrefix = null)
{
	try {
		
		// Enable LOAD LOCAL INFILE
		$pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
		
		$errorDetect = false;
		
		// Temporary variable, used to store current query
		$tmpLine = '';
		
		// Read in entire file
		$lines = file($sqlFile);
		
		// Loop through each line
		foreach ($lines as $line) {
			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || trim($line) == '') {
				continue;
			}
			
			// Read & replace prefix
			$line = str_replace(['<<__prefix__>>'], [$tablePrefix], $line);
			
			// Add this line to the current segment
			$tmpLine .= $line;
			
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';') {
				try {
					// Perform the Query
					$pdo->exec($tmpLine);
				} catch (\PDOException $e) {
					echo "<br><pre>Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage() . "</pre>\n";
					$errorDetect = true;
				}
				
				// Reset temp variable to empty
				$tmpLine = '';
			}
		}
		
		// Check if error is detected
		if ($errorDetect) {
			return false;
		}
		
	} catch (\Exception $e) {
		echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
		return false;
	}
	
	return true;
}

// Prepare to import
// PDO DSN
$dsn = 'mysql:host='.$f3->db_host.
    ';port='.$f3->db_port.
    ';dbname='.$f3->db_name.
    ';charset=utf8';
// PDO options
$options = [
    PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
];

// Start import
try {
	$pdo = new PDO($dsn, $f3->db_user, $f3->db_pass, $options);
	if (!empty($_GET['update'])) {
		$update_file = 'update-'.$_GET['update'];
		if (importSqlFile($pdo, './sqls/'.$update_file.'.sql', $f3->db_prefix)) {
			echo 'Update Done ;)';
		}
	} else if (!empty($_GET['extension']) && !empty($_GET['version'])) {
		$extension = $_GET['extension'];
		$version = $_GET['version'];
		if (importSqlFile($pdo, './extensions/'.$extension.'/sqls/'.$version.'.sql', $f3->db_prefix)) {
			echo 'Imported Extension: '.$extension.' Version: '.$version.' ;)';
		}
	} else {
		if (importSqlFile($pdo, './sqls/mysql_main.sql', $f3->db_prefix)) {
			echo 'Done ;)';
		}
	}
} catch (Exception $e) {
    error_log($e->getMessage());
    exit('Cannot connect to database server, check your config in configs/config.ini');
}
?>