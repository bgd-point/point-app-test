# Point Finance

## Install

add this to your `composer.json` file

```
"require": {
    "point/point-finance": "dev-master"
},
```

```
"repositories": [
  {
    "type" : "git",
    "url"  : "git@gitlab.com:bgd-point/point-finance.git"  
  }
],
```

and update the packages from your terminal

```bash
composer update
```

add new provider in `config/app.php`

```
Point\PointFinance\PointFinanceServiceProvider::class,
```

add new package to `composer.json` file

```
"autoload": {
    "psr-4": {
        "Point\\PointFinance\\": "packages/point/point-finance/src"
    }
},
```

autoload your class 
```bash
composer dump-autoload
```

publish package

```bash
php artisan vendor:publish --provider="Point\PointFinance\PointFinanceServiceProvider" --tag=setup
```

migrate your database and seeding default data

```
php artisan migrate
php artisan db:seed --class=PointFinanceDatabaseSeeder
```

## Security

If you discover any security related issues, please email martien@point.red instead of using the issue tracker.
