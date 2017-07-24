# **WARNING !!!** THIS PACKAGE STILL IN ALPHA (UNSTABLE AND WILL CHANGE ALOT)

FOR FULL DOCUMENTATION PLEASE SEE [DOCS](http://developer.point.red)

# Point Framework

Point Framework is package for build application app.point.red with basic functionality

## Install

install laravel 5.1 inside your folder

```bash
composer create-project laravel/laravel . "5.1.*" --prefer-dist
```

add this to your `composer.json` file

```
"require": {
    "point/framework": "dev-master"
},
```

you need to load repository and all dependency repository

```
"repositories": [
    { "type" : "git", "url" : "git@gitlab.com:bgd-point/point-framework.git" },
    { "type" : "git", "url" : "git@gitlab.com:bgd-point/point-core.git" },
    { "type" : "git", "url" : "git@gitlab.com:bgd-point/core-dependencies.git" },
    { "type" : "git", "url" : "git@gitlab.com:bgd-point/tenant-database.git" }
],
```

and run from your terminal

```bash
composer update
```

add new provider in `config/app.php`

```
Point\Core\CoreServiceProvider::class,
Point\Framework\FrameworkServiceProvider::class,
```

publish assets from core and framework

add `--force` for publish migration file for first install to replace default migration user from laravel`

```bash
php artisan vendor:publish --provider="Point\Core\CoreServiceProvider" --tag=setup
php artisan vendor:publish --provider="Point\Framework\FrameworkServiceProvider" --tag=setup
```

publish your environment

`php artisan vendor:publish --provider="Point\Framework\FrameworkServiceProvider" --tag=env`

migrate your database

```bash
php artisan migrate
```

seeding your database from core

```bash
php artisan db:seed --class=CoreDatabaseSeeder
php artisan db:seed --class=FrameworkDatabaseSeeder

// ... another packages seeding
```

for development only (generate example data)
```bash
php artisan db:seed --class=CoreDevDatabaseSeeder
php artisan db:seed --class=FrameworkDevDatabaseSeeder
```

and then seed default admin after all addons package complete their seed

```bash
php artisan db:seed --class=CoreDefaultAdminDatabaseSeeder
```

set default account

```bash
php artisan framework:default-account
```

## Guide Create Form
```
1 validate the input
2 check if this form allowed to create
3 create new formulir
4 create child form
5 lock form (if had any reference)
6 add vesa
7 mark done reference form
8 add timeline
9 add notification
```
## Security

If you discover any security related issues, please email martien@point.red instead of using the issue tracker.

## Credits

- martiendt

## License

This is private project, you are restricted to use this project
