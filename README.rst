Wordpress CLI Installer
=======================

Description
-----------

A script to install Wordpress completely from the CLI, which makes automation that much easier.

The idea is to have a PHP script that just does what the regular Wordpress installer
does, but done through the CLI. However, since using PHP's CLI binary is somewhat awkward, you
can instead use the bash shell script which is generated (using update-shell-script.sh) and contains
the complete PHP script. Thus you can do something like this::

    $ curl -s http://wordpress.org/latest.tar.gz | tar xzf -
    $ wget -q http://goo.gl/OokgX && chmod +x wordpress-cli-installer.sh
    # edit wordpress/wp-config.php to change db settings, or add them to the next command
    $ ./wordpress-cli-installer.sh -T 'My New Blog' -e 'my-email@example.com' -b 'http://blog.example.com/' wordpress/

Or you can just use bits of the PHP in your own installing script!

Usage
-----

You can use the shell script like so::

    Usage: wordpress-cli-installer.sh [-hPv] -b base-url -e email-address [-p admin-password]
        [-T blog-title] [-u admin-user] [--dbuser=database-user] [--dbpass=database-pass]
        [--dbname=database-name] [--dbhost=database-host] path/to/wp/files/

Or you can use the PHP script directly with the same options like this::

    $ php -f wordpress-cli-installer.php -- [options] path/to/wp/files/

General Options
~~~~~~~~~~~~~~~

-b base-url
    Base URL for the blog since wordpress can't detect it from a CLI install,
    should be a fully qualified URL (ex: http://example.com/)

    **REQUIRED**

-e email-address
    Admin user's email address

    **REQUIRED**

-h
    Display this help text

-p admin-password
    Admin users's password

    *default:* randomly generated

-P
    Toggle whether the blog is public or not (visible to search engines, etc)

    *default:* public (on)

-T blog-title
    Set the blog's title, this should probably be short (and quoted)

    *default:* Change Me

-u admin-user
    Admin user's username

    *default:* admin
-l lang
    Language of this wordpress blog

    *default:* <empty> (en-US)

-v
    Verbose flag, enable more output

wp-config Options
~~~~~~~~~~~~~~~~~

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

    *default:* localhost
