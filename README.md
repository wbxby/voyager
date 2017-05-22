<p align="center"><a href="https://the-control-group.github.io/voyager/" target="_blank"><img width="400" src="https://s3.amazonaws.com/thecontrolgroup/voyager.png"></a></p>

<p align="center">
<a href="https://travis-ci.org/the-control-group/voyager"><img src="https://travis-ci.org/the-control-group/voyager.svg?branch=master" alt="Build Status"></a>
<a href="https://styleci.io/repos/72069409/shield?style=flat"><img src="https://styleci.io/repos/72069409/shield?style=flat" alt="Build Status"></a>
<a href="https://packagist.org/packages/tcg/voyager"><img src="https://poser.pugx.org/tcg/voyager/downloads.svg?format=flat" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/tcg/voyager"><img src="https://poser.pugx.org/tcg/voyager/v/stable.svg?format=flat" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/tcg/voyager"><img src="https://poser.pugx.org/tcg/voyager/license.svg?format=flat" alt="License"></a>
<a href="https://github.com/larapack/awesome-voyager"><img src="https://cdn.rawgit.com/sindresorhus/awesome/d7305f38d29fed78fa85652e3a63e154dd8e8829/media/badge.svg" alt="Awesome Voyager"></a>
</p>

# **V**oyager - The Missing Laravel Admin
Made with ❤️ by [The Control Group](https://www.thecontrolgroup.com)

![Voyager Screenshot](https://laravelvoyager.com/images/screenshot.png)

Website & Documentation: https://laravelvoyager.com

Video Demo Here: https://devdojo.com/series/laravel-voyager-010/

Join our Slack chat: https://voyager-slack-invitation.herokuapp.com/

View the Voyager Cheat Sheet: https://voyager-cheatsheet.ulties.com/

<hr>

Laravel Admin & BREAD System (Browse, Read, Edit, Add, & Delete), made for Laravel 5.3.

After creating your new Laravel application you can include the Voyager package with the following command: 

```bash
composer require tcg/voyager
```

Next make sure to create a new database and add your database credentials to your .env file:

```
DB_HOST=localhost
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
```

You will also want to update your website URL inside of the `APP_URL` variable inside the .env file:

```
APP_URL=http://localhost:8000
```

Add the Voyager service provider to the `config/app.php` file in the `providers` array:

```php
'providers' => [
    // Laravel Framework Service Providers...
    //...
    
    // Package Service Providers
    TCG\Voyager\VoyagerServiceProvider::class,
    // ...
    
    // Application Service Providers
    // ...
],
```

Lastly, we can install voyager. You can do this either with or without dummy data.
The dummy data will include 1 admin account (if no users already exists), 1 demo page, 4 demo posts, 2 categories and 7 settings.

To install Voyager without dummy simply run

```bash
php artisan voyager:install
```

If you prefer installing it with dummy run

```bash
php artisan voyager:install --with-dummy
```

> Troubleshooting: **Specified key was too long error**. If you see this error message you have an outdated version of MySQL, use the following solution: https://laravel-news.com/laravel-5-4-key-too-long-error

And we're all good to go!

Start up a local development server with `php artisan serve` And, visit [http://localhost:8000/admin](http://localhost:8000/admin).

If you did go ahead with the dummy data, a user should have been created for you with the following login credentials:

>**email:** `admin@admin.com`   
>**password:** `password`

NOTE: Please note that a dummy user is **only** created if there are no current users in your database.

If you did not go with the dummy user, you may wish to assign admin priveleges to an existing user.
This can easily be done by running this command:

```bash
php artisan voyager:admin your@email.com
```

If you did not install the dummy data and you wish to create a new admin user you can pass the `--create` flag, like so:

```bash
php artisan voyager:admin your@email.com --create
```

And you will be prompted for the users name and password.

**Сделано: 22.05.17**
**views:**
- Удален блок c google ads со страницы: resources/index.blade.php
- Частичный перевод: resources/views/login.blade.php
- Частичный перевод: resources/views/profile.blade.php

- Частичный перевод: resources/views/bread/browse.blade.php
- Добавлен поиск: resources/views/bread/browse.blade.php
- Добавлен по умолчанию ресайз: resources/views/bread/browse.blade.php, поэтому надо ставить хелпером ресайзер

- Частичный перевод: resources/views/bread/edit-add.blade.php
- ДОбавлена форма для сео: resources/views/bread/edit-add.blade.php, поэтому нужно создать таблицу и модель

- Частичный перевод: resources/views/bread/read.blade.php

- Частичный перевод: resources/views/dashboard/nav.blade.php
- Частичный перевод: resources/views/dashboard/sidebar.blade.php

**src**

- Загрузка Ресайзера: src/Http/Controller/Controller.php
- Убрал даты из пути загрузки картинок: src/Http/Controller/Controller.php
- Исправил косяк с загрузкой multiply image: src/Http/Controller/Controller.php

- Добавление поиска в index: src/Http/Controller/VoyagerBreadController.php
- Добавление SEO в update: src/Http/Controller/VoyagerBreadController.php
- Частичный перевод: src/Http/Controller/VoyagerBreadController.php

**assets**
- Частивный перевод: resources/views/bread/jquery.dataTables.min.js



**Todo**:
- Сделать перевод в файле resources/index.blade.php
- Сделать перевод в файле resources/views/settings/index.blade.php
- Можно вынести поиск в другое место: resources/views/bread/browse.blade.php
- Включить ресайзер или по умолчанию в бандл или отдельным бандлом: resources/views/bread/browse.blade.php
- Добавить автосоздание таблицы для сео resources/views/bread/edit-add.blade.php
- Вынести в отдельный паршал блок для сео resources/views/bread/edit-add.blade.php
- Перевод: src/Http/Controller/VoyagerMediaController.php
- Переделать: src/Http/Wiggets, можно взять с libefolle_2