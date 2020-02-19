# Catalyst php task
This is the repo for Catalyst php coding task, which reads from a csv file and store user information into a dedicated PostgreSQL database.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

Thses instructions will get a copy of the project up and running on your local machine or deployment environment. Please see the necaccially requirements and available options.

### Requirement

Please make sure the following technologies are installed

```
- PHP 7.2+
- PostgreSQL database server version 9.5+ installed and running
- A PostgreSQL database named: catalyst_task
```
### CSV file format

The default csv file that the script reading from is set to users.csv if not specified. The table format should be as below:

| name  | surname | email |
| --- | --- | --- |
| Samuel | Chen | samuelyimingchen@gmail.com |
| John | Doe  | john.doe@test.com |


## Running the script

Before able to run the script, please make sure a CSV file is in the project root directory. 

### Creating user table

Before able to read the csv file, a user table must be created first, please run:

```
$ php user_upload.php -u=user_name -p=password -h=127.0.0.1 --create_table

```
user must provide PostgreSQL database username, password and host to be able to connect to database, options order does not matter. If a user table is already exists, it will be replaced.

### Read CSV and store

If the csv file is named users.csv, --file option is not needed, please run:

```
$ php user_upload.php -u=user_name -p=password -h=127.0.0.1
```

You could also specify the file by using the --file option and follow with a [file_name] after, please run:

```
$ php user_upload.php -u=user_name -p=password -h=127.0.0.1 --file [some_users.csv]
```

## Options

| Option  | Description |
| --- | --- |
| `--file [file_name]` | Specify a file name to be parsed, if `--file` is presented,  a`[file_name]` must be given right after (There is a space between) |
| `--create_table` | This will create/replace a user table (No further action) |
| `dry_run` | This will force the program to read the csv file but not inserting data into database |
| `-u` | PostgreSQL username |
| `-p` | PostgreSQL password |
| `-h` | PostgreSQL host |
| `--help` | This will output the list of directives with details |

## Author

* **Samuel Yi Ming Chen** - samuelyimingchen@gmail.com
