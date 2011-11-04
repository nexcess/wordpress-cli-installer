Wordpress CLI Installer
=======================

Usage
-----

    Usage: wordpress-cli-installer.sh [-hPv] -b base-url -e email-address [-p admin-password]
        [-T blog-title] [-u admin-user] [--dbuser=database-user] [--dbpass=database-pass]
        [--dbname=database-name] [--dbhost=database-host] path/to/wp/files/
    
General Options
---------------

-b base-url
    Base URL for the blog since wordpress can't detect it from a CLI
    install, should be a fully qualified URL (ex: http://example.com/)
    REQUIRED

-e email-address
    Admin user's email address
    REQUIRED

-h
    Display this help text

-p admin-password
    Admin users's password
    default: randomly generated

-P
    Toggle whether the blog is public or not (visible to search engines, etc)
    default: public (on)

-T blog-title
    Set the blog's title, this should probably be short (and quoted)
    default: Change Me

-u admin-user
    Admin user's username
    default: admin

-v
    Verbose flag, enable more output
    
wp-config Options
-----------------

These options are only used if wp-config.php isn't found, in which case they are required (except for dbhost which has a default):

--dbuser=database-user
    Database user's username

--dbpass=database-pass
    Database user's password

--dbname=database-name
    Database name

--dbhost=database-host
    Database hostname. Passing host:port or /path/to/socket.sock might also
    work
    default: localhost
