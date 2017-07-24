# Point Expedition

## Install as a vendor

add this to your `composer.json` file

```
"require": {
    "point/point-expedition": "dev-master"
},
```

```
"repositories": [
  {
    "type" : "git",
    "url"  : "git@gitlab.com:bgd-point/point-expedition.git"  
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
git clone git@gitlab.com:bgd-point/point-expedition.git
```

add new provider in `config/app.php`

```
Point\PointExpedition\PointExpeditionServiceProvider::class,
```

add new package to `composer.json` file

```
"autoload": {
    "psr-4": {
        "Point\\PointExpedition\\": "packages/point/point-expedition/src"
    }
},
```

autoload your class 
```bash
composer dump-autoload
```

publish package

```bash
php artisan vendor:publish --provider="Point\PointExpedition\PointExpeditionServiceProvider" --tag=setup
```

migrate your database and seeding default data

```
php artisan migrate
php artisan db:seed --class=PointExpeditionDatabaseSeeder
```

## Security

If you discover any security related issues, please email martien@point.red instead of using the issue tracker.
