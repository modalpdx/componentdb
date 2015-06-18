# Component DB
A school project for a discrete electronics component inventory/tracking
database.

##Project info

A PDF document in the repository (componentDB.pdf) contains more details
than I could (or should) possibly replicate here. I advise anyone that is
interested to read the document instead of this README. 

For the TL;DR crowd, this is a database and basic admin/front-end (in PHP)
for a MySQL database that tracks a personal inventory of electronic
    components. I chose this as my final project in my database class
    because I need something like this for my own collection of parts. The
    system is nowhere near usable as an end-user product, but as a
    proof-of-concept for a database backend it works really well, at least
    in my opinion. All CRUD works as intended, changes to foreign keys
    cascade (or delete) correctly, etc. 

Now, go read the PDF.

##How to use this

Most of the files in this repo are for the PHP backend and HTML/CSS
frontend. No JavaScript, which is intentional. 

A .SQL file (database.sql) is included that should be capable of setting up a fresh
database with a few pieces of sample data included. To build the database,
first create a new, empty database in MySQL using whatever means you have at your
disposal (GUI program, mysql command line client, whatever) then run the
following command at a command prompt to build the database:

```
mysql -u [dbuser] -p[dbpassword] database < database.sql
```

Note that there is no space between "-p" and the DB user password. Also
note that this is entered in plain text on a command line, not as a hashed
password. 

Next, you will need to edit the "db = ..." line in functions.php to include your
database host address, user name, password, and database name. A comment
above the line to be edited in functions.php explains this.

Perhaps this does not need to be explained, but you'll also need a web
server that can serve PHP websites. The website will need to be able to
connect to the database (they are usually on the same system so this is
rarely a problem).

##Build:

There is nothing to build except for the database tables, and the command
line that does that is listed above.

##Colophon:

This program was written with standards in mind but was only
developed and tested on Linux and Mac OS. Mostly Linux.

As stated, MySQL, PHP, and a web server that can access the MySQL database
are required.

##Disclaimer:

This is code from a school project. It satisfies assignment requirements
but is nowhere near as "scrubbed" as released software should be.
Security is only slightly addressed, only functionality and some input
validation (primarily declawing input to prevent SQL injection issues). If
you use this code for anything other than satisfying your curiosity,
please keep the following in mind:

- there is no warranty of any kind (you use the code as-is)
- there is no support offered, please do not ask
- there is no guarantee it'll work, although it's not complex so it should
  work
- please do not take credit for code you did not write, especially if you
  are a student. NO CHEATING.

Thanks!
