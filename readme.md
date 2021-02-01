## Зависисмости

### Основные
* PHP 7.1.*
* Nginx
* Composer
* PosgreSQL Server >=9.5
* Redis server
* Memcached server
* NodeJS >= v6.14
* NPM >= 3.10

### Модули PHP
* curl
* date
* dom
* exif
* fileinfo
* filter
* ftp
* gd
* gettext
* hash
* iconv
* igbinary
* intl
* json
* ldap
* libxml
* mbstring
* mcrypt
* memcached
* msgpack
* mysqli
* mysqlnd
* openssl
* pcntl
* pcre
* PDO
* pdo_mysql
* pdo_pgsql
* pdo_sqlsrv
* pgsql
* Phar
* posix
* readline
* session
* shmop
* soap
* sockets
* sqlsrv
* zip
* xml
* zlib

## Локальное развретывание

Выполнить в папке проекта: 
* "composer install"
* "cp .env.example .env" (внести изменения в настройки соеденея с БД и URL)
* "php artisan key:generate"
* "php artisan migrate"
* "npm install --no-optional"
* "npm install -g cross-env"
* "npm run dev"
