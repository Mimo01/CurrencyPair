## Currency Pair

Gets order book of selected currency pair (BTC - EUR).
Finds best trades and saves them.

### Usage
`php artisan serve`
- Run development server

`php artisan migrate`
- Installs required tables to database

`php artisan run-program {seconds}`
- Seconds argument defines interval between api calls - default 10s

### Installation
- Default Laravel install, no custom installation needed
- MySQL server, database connection can be defined inside `.env`
