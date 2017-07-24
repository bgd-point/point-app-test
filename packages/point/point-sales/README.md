# Point Sales

## Install

add this to your `composer.json` file

```
"require": {
    "point/point-sales": "dev-master"
},
```

```
"repositories": [
  {
    "type" : "git",
    "url" : "git@gitlab.com:bgd-point/point-sales.git"  
  }
],
```

and run from your terminal

```bash
$ composer update
```

add new provider in `config/app.php`

```
Point\BasicSales\PointSalesServiceProvider::class,
```

or use this for spesific providers

```bash
php artisan vendor:publish --provider="Point\PointSales\PointSalesServiceProvider" --tag=setup
```
Regenerates the list of all classes that need to be included in the project

```bash
$ composer dump-autoload
```

seeding your database from sales plugin

```bash
php artisan db:seed --class=PointSalesDatabaseSeeder
```

## Security

If you discover any security related issues, please email martien@point.red instead of using the issue tracker.

## Credits

- martiendt
