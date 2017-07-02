# Skeleton for an API Application

[![Build Status](https://img.shields.io/travis/cakephp/app/master.svg?style=flat-square)](https://travis-ci.org/cakephp/app)
[![License](https://img.shields.io/packagist/l/cakephp/app.svg?style=flat-square)](https://packagist.org/packages/cakephp/app)

A skeleton for creating API applications with [CakePHP](http://cakephp.org) 3.x.

## Description

This Application is a starting point for creating an API to be consumed from other apps. It includes a basic MySQL script for the User's table.

## Features
1. Includes login and logout endpoints to be used for authentication purposes.
2. Includes CRUD endpoints for Users based on the REST guidelines.
3. Authorization token is generated within the app to be used in the header's requests.
4. It is a basic application. Feel free to add or improve features.

## Installation

1. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar create-project --prefer-dist cakephp/app [app_name]`.

If Composer is installed globally, run

```bash
composer create-project --prefer-dist cakephp/app
```

In case you want to use a custom app dir name (e.g. `/myapp/`):

```bash
composer create-project --prefer-dist cakephp/app myapp
```

You can now either use your machine's webserver to view the default home page, or start
up the built-in webserver with:

```bash
bin/cake server -p 8765
```

Then you can start using the API calling these endpoints:

POST 	http://localhost:8765/api/users/login

POST 	http://localhost:8765/api/users

GET  	http://localhost:8765/api/users?ci_number=14&last_name=mo&first_name=u&sortField=last_name&sortDirection=desc&limit=2

GET  	http://localhost:8765/api/users/{id}

PUT  	http://localhost:8765/api/users/{id}

DELETE	http://localhost:8765/api/users/{id}
