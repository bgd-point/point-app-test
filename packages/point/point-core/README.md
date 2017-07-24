# **WARNING !!!** THIS PACKAGE STILL IN ALPHA (UNSTABLE AND WILL CHANGE ALOT)

FOR FULL DOCUMENTATION PLEASE SEE [DOCS](http://developer.point.red)

# Point Core

Software Development Kit for point.red app based on Laravel 5.1 Framework

## Install

add this to your `composer.json` file

```
"require": {
    "point/core": "dev-master"
},
```

```
"repositories": [
  {
    "type" : "git",
    "url" : "git@gitlab.com:bgd-point/point-core.git"  
  }
],
```

and run from your terminal

```bash
composer update
```

add new provider in `config/app.php`

```
Point\Core\CoreServiceProvider::class,
```

change email preferences from `config/auth`

```php
'model' => Point\Core\Models\User::class,
```
```php
'password' => [
    'email'  => 'core::emails.password',
    'table'  => 'password_resets',
    'expire' => 60,
],
```

extend `User.php` class default from laravel to user core `Point\Core\Models\User`

publish assets

add `--force` for publish migration file for first install to replace default migration user from laravel`

```bash
php artisan vendor:publish --provider="Point\Core\CoreServiceProvider" --tag=setup
```

seeding your database

```bash
php artisan db:seed --class=CoreDatabaseSeeder
```

seeding default admin after all package permission seeding

```bash
php artisan db:seed --class=CoreDefaultAdminDatabaseSeeder
```

add this custom response in `app/exceptions/Handler.php`

```php
if ($e instanceof PointException) {
    return response()->view('core::errors.exceptions', ['messages' => $e->getMessage()]);
}

if ($e instanceof TokenMismatchException) {
    return response()->view('core::errors.exceptions', ['messages' => 'Your token expired']);
}
```

## Security

If you discover any security related issues, please email martien@point.red instead of using the issue tracker.

## Credits

- martiendt

## License

This is private project, you are restricted to use this project
