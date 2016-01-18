set -ev

INI_PATH=`php -r "echo php_ini_loaded_file();"`

curl -LS https://pecl.php.net/get/ev | tar -xz;
pushd ev-*;
phpize;
./configure;
make;
make install;
popd;
echo "extension=ev.so" >> INI_PATH
