uploading
=========

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

Full file uploading and handling example project based on official http://symfony.com/doc/current/controller/upload_file.html tutorial

To run this example please follow the next instructions:

- $ composer update
- $ php bin/console doctrine:database:create
- $ php bin/console doctrine:schema:update --force

Do not forget to set permissions for var/cache/, var/logs/, var/sessions/, web/uploads/images to run symfony ;)