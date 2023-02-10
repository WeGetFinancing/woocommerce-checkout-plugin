# WeGetFinancing Payment Checkout for WooCommerce

WeGetFinancing payment gateway integration plugin for WooCommerce - Alpha Version

## How to install

1. Log in into your WordPress installation
2. Go to the plugin management page
3. Upload the file plugin.zip provided in the root directory of this GitHub repository

Important: this version is compiled with vendor made by a composer with php 8.2
In case this would not be supported by your installation you have to go inside the plugin folder, run composer install
and create a zip file of the plugin folder, then deploy it on your installation.

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
