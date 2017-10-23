# Decision Database Browser

This is a small web page that can be used to browse and search the Decisions the [iFSR](https://ifsr.de) made over the years.


## Configuration

The file `config.php` contains the path to the SQLite database, which you should set beforehand.

## Dummy Data

If you want to work on this little project, you are going to need a database with some dummy data to test if everything works as expected. Therefore, simply execute `init.php?dummydata` - it inserts three examples into the database (which is created if it does not exist).
