# SQL MIG

# Command-Line API

###### Note: The command-line parameters will always override the settings in the `.dbdiff` config file

-   **--server1=user:password@host1:port** - Specify the source db connection details. If there is only one server the --server1 flag can be omitted
-   **--server2=user:password@host2:port** - Specify the target db connection details (if it’s different to server1)
-   **--template=templates/simple-db-migrate.tmpl** - Specifies the output template, if any. By default will be plain SQL
-   **--type=schema** or **data** or **all** - Specifies the type of diff to do either on the schema, data or both. schema is the default
-   **--include=up** or **down** or **all** - Specified whether to include the up, down or both data in the output. up is the default
-   **--nocomments=true** - By default automated comments starting with the hash (\#) character are included in the output file, which can be removed with this parameter
-   **--config=config.yaml** - By default, DBDiff will look for a `.dbdiff` file in the current directory which is valid YAML, which may also be overridden with a config file that lists the database host, user, port and password of the source and target DBs in YAML format (instead of using the command line for it), or any of the other settings e.g. the format, template, type, include, nocomments. Please note: a command-line parameter will always override any config file.
-   **server1.db1.table1:server2.db2.table3** or **server1.db1:server2.db2** - The penultimate parameter is what to compare. This tool can compare just one table or all tables (entire db) from the database
-   **--output=./output-dir/today-up-schema.sql** - The last parameter is an output file and/or directory to output the diff to, which by default will output to the same directory the command is run in if no directory is specified. If a directory is specified, it should exist, otherwise an error will be thrown. If this path is not specified, the default file name becomes migration.sql in the current directory

# Usage Examples

## Example 1
`$ ./dbdiff server1.db1:server2.db2`

This would by default look for the `.dbdiff` config file for the DB connection details, if it’s not there the tool would return an error. If it’s there, the connection details would be used to compare the SQL of only the schema and output a commented migration.sql file inside the current directory which includes only the up SQL as per default

## Example 2
`$ ./dbdiff server1.development.table1:server2.production.table1 --nocomments=true --type=data`

This would by default look for the `.dbdiff` config file for the DB connection details, if it’s not there the tool would return an error. If it’s there, the connection details would be used to compare the SQL of only the data of the specified table1 inside each database and output a .sql file which has no comments inside the current directory which includes only the up SQL as per default

## Example 3
`$ ./dbdiff --config=config.conf --template=templates/simple-db-migrate.tmpl --include=all server1.db1:server2.db2 --output=./sql/simple-schema.sql`

Instead of looking for `.dbdiff`, this would look for `config.conf` (which should be valid YAML) for the settings, and then override any of those settings from `config.conf` for the --template and --include parameters given in the command-line parameters - thus comparing the source with the target database and outputting an SQL file called simple-schema.sql to the ./sql folder, which should already exist otherwise the program will throw an error, and which includes only the schema as an up and down SQL diff in the simple-db-migrate format (as specified by the template). This example would work perfectly alongside the simple-db-migrate tool

# File Examples

## .dbdiff

	server1:
		user: user
		password: password
		port: port # for MySQL this is 3306
		host: host1 # usually localhost or 127.0.0.1
	server2:
		user: user
		password: password
		port: port # for MySQL this is 3306
		host: host1 # usually localhost or 127.0.0.1
	template: templates/simple-db-migrate.tmpl
	type: all
	include: all
	nocomments: true
	tablesToIgnore:
	- table1
	- table2
	- table3
	fieldsToIgnore:
		table1:
			- field1
			- field2
			- field3
		table4:
			- field1
			- field4


## simple-db-migrate.tmpl

	SQL_UP = u"""
	{{ $up }}
	"""
	SQL_DOWN = u"""
	{{ $down }}
	"""

# How Does the Diff Actually Work?

The following comparisons run in exactly the following order:

-   When comparing multiple tables: all comparisons should be run
-   When comparing just one table with another: only run the schema and data comparisons

## Overall Comparison
-   Check both databases exist and are accessible, if not, throw an error
-   The database collation is then compared between the source and the target and any differences noted for the output

## Schema Comparison
-   Looks to see if there are any differences in column numbers, name, type, collation or attributes
-   Any new columns in the source, which are not found in the target, are added

## Data Comparison
-   And then for each table, the table storage type (e.g. MyISAM, CSV), the collation (e.g. utf8\_general\_ci), and number of rows are compared, in that order. If there are any differences they are noted before moving onto the next test
-   Next, both changed rows as well as missing rows from each table are recorded
