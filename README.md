# WeGetFinancing Payment Checkout for WooCommerce

WeGetFinancing payment gateway integration plugin for WooCommerce

## How to install

1. Log in into your WordPress installation
2. Go to the plugin management page
3. Upload the file plugin.zip provided in the root directory of this GitHub repository

Important: this version is compiled with vendor made by a composer with php 8.0
In case you would like to benefit by the advantages of a newer version of php, you can compile your vendor folder
using the composer.json file.

## Development environment 

Install a fresh version of WordPress:

1. Configure your .env file
2. Ensure that all the container are down
   ```
   docker-compose down -v --remove-orphans
   ```
3. Delete any content inside the folders "./var/wp" but not the folders itself
   ```
   rm -rf ./wp/*
   ```
4. Start docker-compose
   ```
   docker-compose up -d
   ```
5. Install vendor
      ```
   docker-compose run --rm composer install
   ```
