Clown Schedule
============

This webapp automatically creates monthly plans for clinic clown visits.
It lets you manage clowns, venues and play dates. When clicking the "create schedule" button
the app tries to assign the clowns automatically so that every regular play date has 2 clowns
and every clown has the same number of plays.

Installation
======

This is a standard symfony application. You need php8.1 and npm.

- git clone this repository
- copy `.env.example` to `.env` and adjust it to your needs
- `composer install`
- `npm install`
- build assets (optional - the /build directory is included in git repo)
    - `./node_modules/.bin/encore dev --watch`
    - `./node_modules/.bin/encore production`
- run database migrations:    
    - `php bin/console doctrine:migrations:migrate`
- starting server 
    - for dev: symfony server:start
    - for prod: use /public as document root - a .htaccess file is included for apache

