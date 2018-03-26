#!/bin/bash
MODULE_CACHE_DIR=${TRAVIS_BUILD_DIR}/travis_cache/`php-config --vernum`
INI_DIR=${TRAVIS_BUILD_DIR}/travis_cache/ini
PHP_TARGET_DIR=`php-config --extension-dir`
PHP_INI_FILE=`php -r "echo php_ini_loaded_file();"`

if [ -d ${MODULE_CACHE_DIR} ]
then
  cp ${MODULE_CACHE_DIR}/* ${PHP_TARGET_DIR}
fi

mkdir -p ${INI_DIR}
mkdir -p ${MODULE_CACHE_DIR}

for module in $MODULES
do
  FILENAME=`echo $module|cut -d : -f 1`
  PRIORITY=`echo $module|cut -d : -f 2`
  PACKAGE=`echo $module|cut -d : -f 3`
  if [ ! -f ${PHP_TARGET_DIR}/${FILENAME} ]
  then
    echo "$FILENAME not found in extension dir, compiling"
    printf "yes\n" | pecl install ${PACKAGE}
    sed -i '1d' ${PHP_INI_FILE}
  fi

  echo "Adding $FILENAME to php config"
  echo "extension = $FILENAME" > ${INI_DIR}/${PRIORITY}-${FILENAME}.ini
  phpenv config-add ${INI_DIR}/${PRIORITY}-${FILENAME}.ini

  cp ${PHP_TARGET_DIR}/${FILENAME} ${MODULE_CACHE_DIR}
done
