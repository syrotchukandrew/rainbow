#!/bin/bash

clear
echo "Choose clue :"
echo "1 - installing project"
echo "2 - updating project"
echo "3 - updating database"
echo "4 - exit"

read Clue

case "$Clue" in
1) echo "start installing project..."
    npm install
    npm install pgwslideshow
    composer install --verbose
    ./node_modules/.bin/bower install
    ./node_modules/.bin/gulp
    php app/console doctrine:database:create
    php app/console doctrine:schema:update --force
    php app/console doctrine:fixtures:load --append --purge-with-truncate

;;
2) echo "start updating project..."
    composer update --verbose
    php app/console doctrine:database:drop --force
    php app/console doctrine:database:create
    php app/console doctrine:schema:update --force
    php app/console doctrine:fixtures:load --append --purge-with-truncate

;;

3) echo "start updating database..."
    php app/console doctrine:database:drop --force
    php app/console doctrine:database:create
    php app/console doctrine:schema:update --force
    php app/console doctrine:fixtures:load --fixtures=src/AppBundle/DataFixtures/ORM/Dev --append --purge-with-truncate

;;
4) exit 0
;;
esac

exit 0