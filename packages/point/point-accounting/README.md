# Point Accounting

## Install as a vendor

add this to your `composer.json` file

```
"require": {
    "point/point-accounting": "dev-master"
},
```

```
"repositories": [
  {
    "type" : "git",
    "url"  : "git@gitlab.com:bgd-point/point-accounting.git"  
  }
],
```

and update the packages from your terminal

```bash
composer update
```

## Install as a development package

go to directory `packages/point`, or create it first if no exist `packages/point` directory there.

add package from repository

```
git clone git@gitlab.com:bgd-point/point-accounting.git
```

add new provider in `config/app.php`

```
Point\PointAccounting\PointAccountingServiceProvider::class,
```

add new package to `composer.json` file

```
"autoload": {
    "psr-4": {
        "Point\\PointAccounting\\": "packages/point/point-accounting/src"
    }
},
```

autoload your class 
```bash
composer dump-autoload
```

publish package

```bash
php artisan vendor:publish --provider="Point\PointAccounting\PointAccountingServiceProvider" --tag=setup
```

migrate your database and seeding default data

```
php artisan migrate
php artisan db:seed --class=PointAccountingDatabaseSeeder
```

## Security

If you discover any security related issues, please email martien@point.red instead of using the issue tracker.
