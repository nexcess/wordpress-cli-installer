#!/bin/bash

BASE_FILENAME='wordpress-cli-installer'
SHELL_SCRIPT="${BASE_FILENAME}.sh"
PHP_SCRIPT="${BASE_FILENAME}.php"
PHP_OPTIONS=''

cat > $SHELL_SCRIPT <<- "EOF"
#!/bin/bash

#THIS FILE IS AUTOGENERATED, DO NOT EDIT IT
#EDIT ${PHP_SCRIPT}, THEN RUN $(basename $0) TO MAKE CHANGES

# manually specify the path to the PHP interpreter
# with the environmental variable PHP_BIN
if [ -n "$PHP_BIN" ]; then
    PHP="$PHP_BIN"
elif [ -x /usr/bin/php-cli ]; then
    PHP=/usr/bin/php-cli
elif [ -x /usr/bin/php ]; then
    PHP=/usr/bin/php
else
    echo "No suitable PHP interpreter found"
    exit 129
fi
read -r -d '' PHP_SCRIPT <<'EOF'
EOF

php -w $PHP_SCRIPT >> $SHELL_SCRIPT

echo >> $SHELL_SCRIPT
echo 'EOF' >> $SHELL_SCRIPT
cat >> $SHELL_SCRIPT <<EOF
\$PHP -f <(printf "%s" "\$PHP_SCRIPT") $PHP_OPTIONS -- "\$@"
if [ \$? -eq 128 ]; then
    #everything green
    exit 0
elif [ \$? -eq 255 ]; then
    #PHP internal error
    exit 1
elif [ \$? -gt 128 ]; then
    #wpi script error
    exit 2
elif [ \$? -eq 0 ]; then
    #error from wp_install
    exit 3
else
    #dunno
    exit 4
fi
EOF
