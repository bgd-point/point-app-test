FOR FULL DOCUMENTATION PLEASE SEE [DOCS](http://developer.point.red)

# Point Basic Purchasing

## Install

add this to your `composer.json` file

```
"require": {
    "point/point-purchasing": "dev-master"
},
```

```
"repositories": [
  {
    "type" : "git",
    "url" : "git@gitlab.com:bgd-point/point-purchasing.git"  
  }
],
```

and run from your terminal

```bash
$ composer update
```

add new provider in `config/app.php`

```
Point\PointPurchasing\PointPurchasingServiceProvider::class,
```

or use this for spesific providers

```bash
php artisan vendor:publish --provider="Point\PointPurchasing\PointPurchasingServiceProvider" --tag=migrations
php artisan vendor:publish --provider="Point\PointPurchasing\PointPurchasingServiceProvider" --tag=seeds
```

 seeding your database from core

```bash
php artisan db:seed --class=PointPurchasingDatabaseSeeder
```

## Security

If you discover any security related issues, please email martien@point.red instead of using the issue tracker.
