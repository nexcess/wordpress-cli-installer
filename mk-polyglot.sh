#!/bin/bash

BASE_FILENAME='wordpress-cli-installer'
SHELL_SCRIPT="${BASE_FILENAME}.sh"
PHP_SCRIPT="${BASE_FILENAME}.php"
PHP_OPTIONS=''

cat > $SHELL_SCRIPT <<EOF
#!/bin/bash

php $PHP_OPTIONS -- \$* <<EOF
EOF

php -w $PHP_SCRIPT | sed 's/\$/\\$/g' >> $SHELL_SCRIPT
