# WeGetFinancing Payment Checkout for WooCommerce

WeGetFinancing payment gateway integration plugin for WooCommerce

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
