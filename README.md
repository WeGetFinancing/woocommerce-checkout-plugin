# WeGetFinancing Payment Checkout for WooCommerce

This is a WooCommerce module which integrates WeGetFinancing payment gateway with the WordPress / WooCommerce application.

## Installation 

Install a fresh version of WordPress:

1. Configure your .env file
2. Ensure that all the container are down
   ```
   docker-compose down -v --remove-orphans
   ```
3. Delete any content inside the folders "db" and "wp" but not the folders itself
   ```
   rm -rf ./db/*
   rm -rf ./wp/*
   ```
4. Start docker-compose
   ```
   docker-compose up -d
   ```

