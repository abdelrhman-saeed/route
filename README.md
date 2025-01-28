# Route

`Route` is a **dynamic routing package** for PHP applications, designed to simplify the creation of RESTful routes.

---

## Installation

```bash
composer require abdelrhman-saeed/route
```

---

## Prerequisites

1. **Entry Point File**: Create an entry point file for your application (e.g., `index.php`).
2. **Server Configuration**: Redirect all requests to this entry point file.

   Example using PHP's built-in server:

   ```bash
   php -S 127.0.0.1:8000
   ```

3. **Routes File**: Create a separate file to define your routes (e.g., `routes.php`). You can store it anywhere in your project.

---

## Usage Example

**index.php:**

```php
<?php

use AbdelrhmanSaeed\Route\Api\Route;
use Symfony\Component\HttpFoundation\{Request, Response};

// Initialize the routing system
Route::setup('routes.php', Request::createFromGlobals(), new Response);
```

**routes.php:**

```php
use AbdelrhmanSaeed\Route\Api\Route;
use App\Controllers\SomeKindOfController;

// Define routes with dynamic segments
Route::get('users/{user}/posts/{post}', function (int $user, int $post) {
    var_dump($user, $post);
});

// Use a controller to handle requests
Route::get('posts/{post}/comments/{comment}', [SomeKindOfController::class, 'someMethod']);

// Define routes with optional segments
Route::get('search/users/{user}/{filter?}', function (int $user, ?string $filter = null) {
    // Handle search logic
});

// Supported HTTP methods: ['get', 'post', 'put', 'patch', 'delete']
Route::post('test', function () {
    // Handle POST request
});

Route::put('test', function () {
    // Handle PUT request
});

// Define multiple methods for a single route
Route::match(['put', 'patch', 'delete'], 'test', function () {
    // Handle multiple request types
});

// Define a route for all HTTP methods
Route::any('test', function () {
    // Handle any HTTP method
});
```

---

## Route Constraints

You can apply constraints to route segments to validate their format.

```php
use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsInterface;
use AbdelrhmanSaeed\Route\Api\Route;

// Define a constraint using regex
Route::get('users/{user}', fn (int $user) => var_dump($user))
     ->where('user', '[A-z]+');

// Use predefined constraints from ConstraintsInterface
Route::get('users/{user}/posts/{post}', function (int $user, string $post) {
    // Handle request
})
->where('user', ConstraintsInterface::NUM)
->where('post', ConstraintsInterface::ALPHANUM);

// Specify allowed values for a segment
Route::get('oauthcallback/{server}', function (string $server) {
    // Handle OAuth callback
})
->whereIn('server', ['facebook', 'google']);
```

---

## Middleware

To add middleware to your routes, extend the `AbdelrhmanSaeed\Route\Middleware` class and implement the `handle()` method.

```php
use AbdelrhmanSaeed\Route\Middleware;
use Symfony\Component\HttpFoundation\Request;

class RedirectIfAuthenticated extends Middleware
{
    public function handle(Request $request): void
    {
        // Perform checks before proceeding
        parent::handle($request);
    }
}
```

Assign middleware to a route:

```php
Route::get('login', function () {
    // Login logic
})->setMiddlewares(RedirectIfAuthenticated::class);
```

---

## Resource Routing

The `Route::resource()` method automatically generates RESTful routes for a given controller.

```php
Route::resource('users', UserController::class);
```

This will generate:

- `GET /users` → `UserController::index()`
- `POST /users` → `UserController::save()`
- `GET /users/{user}` → `UserController::show(mixed $user)`
- `PUT, PATCH /users/{user}` → `UserController::update(mixed $user)`
- `DELETE /users/{user}` → `UserController::delete(mixed $user)`

If you set the `api` parameter to `false`, two additional routes will be generated:

- `GET /users/create` → `UserController::create()`
- `GET /users/{user}/edit` → `UserController::edit(mixed $user)`

```php
Route::resource('users', UserController::class, false);
```

---

### Nested Resource Routing

You can define nested resource routes using dot notation:

```php
Route::resource('users.posts', PostController::class);
```

This will generate routes like:

- `GET /users/{user}/posts` → `PostController::index()`
- `POST /users/{user}/posts` → `PostController::save()`
- `GET /users/{user}/posts/{post}` → `PostController::show(mixed $post)`

Apply constraints to nested routes:

```php
Route::resource('users.posts', PostController::class)
     ->where('users', ConstraintsInterface::NUM)
     ->where('posts', ConstraintsInterface::ALPHANUM);
```

---

### Shallow Nesting

Shallow nesting removes the parent ID from child routes when it's unnecessary.

```php
Route::resource('users.posts', PostController::class, shallow: true);
```

This will generate routes like:

- `GET /users/posts` → `PostController::index()`
- `POST /users/posts` → `PostController::save()`
- `GET /users/posts/{post}` → `PostController::show(mixed $post)`

---

## Middleware Grouping

You can group routes and assign a middleware to all of them:

```php
Route::setMiddlewares(RedirectIfAuthenticated::class)
     ->group(function () {
         Route::get('test', function () {
             // Handle request
         });

         Route::resource('posts', PostController::class);
     });
```

---

## Controller Grouping

Group routes by a common controller:

```php
Route::controller(SomeController::class)
     ->group(function () {
         Route::get('someroute', 'methodName');
         Route::post('posts', 'store');
     });
```

You can also combine controller and middleware grouping:

```php
Route::controller(SomeController::class)
     ->setMiddlewares(SomeMiddleware::class)
     ->group(function () {
         Route::get('route', 'method');
         Route::post('posts', 'store');
     });
```

---

## Handling 404 Requests

Define a custom action for 404 responses:

```php
Route::notFound(function () {
    // Handle 404 error
    echo 'Page not found';
});
```

If not defined, a simple 404 HTTP message will be returned.

---

## License

This package is licensed under the MIT License.
