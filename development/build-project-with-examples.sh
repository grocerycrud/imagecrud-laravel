#!/bin/bash

# WARNING - This is only for a quick local installation. Please do
# not use this code at production
cd ../
composer install
composer create-project laravel/laravel --prefer-dist
mkdir app
mv laravel/app/* app
rmdir laravel/app
cp development/paths.php laravel/bootstrap/paths.php
cp development/routes.php app/routes.php
cp development/controllers/* app/controllers
cp development/config/* app/config
cp development/views/* app/views
cp development/composer.json laravel/composer.json
cd laravel
composer update
cd ../
mkdir laravel/public/assets && mkdir laravel/public/assets/image_crud
cp -rf assets/image_crud/* laravel/public/assets/image_crud/
mkdir laravel/public/assets/uploads
cp -rf assets/uploads/* laravel/public/assets/uploads/
