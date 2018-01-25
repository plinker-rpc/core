#!/bin/bash

if [ $(dpkg-query -W -f='${Status}' php7.0-xdebug 2>/dev/null | grep -c "ok installed") -eq 0 ];
then
  sudo apt-get install php7.0-xdebug
  sudo phpenmod xdebug
fi

./vendor/bin/phpunit --configuration phpunit.xml --coverage-text
