# Route

ROUTE IS A DYNAMIC ROUTING PACKAGE - i wrote that in capital because it looks cooler

## Installation

```cmd
composer require abdelrhman-saeed/route
```

## Prerequisites

- First you need to have a file that works as a starting point in your application (e.g., index.php).
- Your server should redirect all the requests to this file ( a simple php server should do the job ).

```cmd
php -S 127.0.0.1:8000
```

- Another file for writing the routes you need (e.g., routes.php) - store it where every you like.

## How to use

```php

<?php

// index.php

use AbdelrhmanSaeed\Route\Api\Route;
use Symfony\Component\HttpFoundation\{Request, Response};


/**
 * the route path can be a path to a directory contains your defined routes
 * or to a simple file that has your routes
 */
Route::setup('routes.php', Request::createFromGlobals(), new Response);
```

> next: the routes.php file

```php
use AbdelrhmanSaeed\Route\Api\Route;
use App\Controllers\SomeKindOfController;

/**
 * you can pass route segments to your handle ( and clouse or a Controller )
 * by wrapping the segment in a pair of a curly brackets "{segment}"
 *
 */ 
Route::get('users/{user}/posts/{posts}', function (int $user, int $post) {
        var_dump($user, $post);
});

// handle the request by defining a Controller (normal class), and its method
Route::get('posts/{post}/comments/{comment}', [SomeKindOfController::class, 'someMethod']);

/**
 * you can pass an optional argument by wrapping the route segment
 * with a pair of curly brackets and a '?' inside it "{segment?}"
 * 
 * Note: an optional argument and only be in the end of the route
 */
Route::get('search/users/{user}/{filter?}', function (int $user, ?string $filter = null) {
    // do stuff
});

/**
 * Supported Http Methods are: ['get', 'post', 'put', 'patch', 'delete']
 */

// get
Route::get('test', function () {

        });

// post
Route::post('test', function () {

        });

// put
Route::put('test', function () {

        });

// patch
Route::patch('test', function () {

        });

// delete
Route::delete('test', function () {

        });

/**
 * match method defines multiple http method to a route
 */
Route::match(['put', 'patch', 'delete'],'test', function () {

        });

/**
 * any method defines all the supported http methods to a route
 */
Route::any('test', function () {

        });

```

> Route constraints

```php

use AbdelrhmanSaeed\Route\Endpoints\Rest\Constraints\ConstraintsInterface;
use AbdelrhmanSaeed\Route\Api\Route;


/**
 * you can define constraints for the route segments
 */
Route::get('users/{user}', fn (int $user) => var_dump($user))
        ->where('user', '[A-z]+');

Route::get('users/{slug}', fn (mixed $slug) => var_dump($slug))
        ->where('slug', '\w+');


/**
 * or you can just use the defined constatins in the ConstraintsInterface
 * 
 * ConstraintsInterface::NUM - for numerics only
 * ConstraintsInterface::ALPHA - for letters only
 * ConstraintsInterface::ALPHANUM - for numerics and letters
 */

Route::get('users/{user}/posts/{post}', function (mixed $user, string $post) {
    // do stuff
})
->where('user', ConstraintsInterface::NUM)
->where('post', ConstraintsInterface::ALPHANUM);

/**
 * specify the values that a route segment can be
 * 
 * oauthcallback/facebook
 * oauthcallback/google
 */
Route::get('oauthcallback/{server}', function (string $server) {
    // do some oauth stuff
})
->whereIn('server', ['facebook', 'google']);

// constraints for an optional arguments is done like this:
Route::get('search/{users}/{filter?}', function (mixed $user, mixed $filter = null) {
    // some filter stuff, idk
})
->whereOptional(ConstraintsInterface::ALPHA);
// or we can set specific values for optional argmunts by passing an array with values instead a REGEX
->whereOptional(['value-1', 'value-2']);

```

> Middlewares

```php

/**
 * to add middleware handlers to your routes you will need to extends the
 * \AbdelrhmanSaeed\Route\Middleware class
 */

use AbdelrhmanSaeed\Route\Middleware;
use Symfony\Component\HttpFoundation\Request;

/**
 * don't worry about instantiating the middleware, the package does this for you
 * store it where ever you want
 */
class RedirectIfAuthenticated extends Middleware
{
    // you need to implement the handle(Request $request): void method

    public function handle(Request $request): void
    {
        /**
         * handle request, if things went well you call the parent::handle($request) method
         * to pass the request to the other middlewares so they can handle it too
         */

        parent::handle($request);
    }
}


```

```php
/**
 * 
 * routes.php
 */

use Somewhere\Mymiddlewares\AreStored\RedirectIfAuthenticated; 

Route::get('login', function () {
    // some authenticated logic, idk !
})
->setMiddlewares(RedirectIfAuthenticated::class);

```

> next : Resource Routing

```php

/**
 * 
 * this will make the following routes
 * 
 * 'users' route with 'get' method will execute UserController::index()
 * 'users' route with post method will execute UserController::save() 
 * 'users/{users}' route with 'get' method will execute UserController::show(mixed $user)
 * 'users/{users}' route with 'put,patch' methods will execute UserController::update(mixed $user)
 * 'users/{users}' route with 'delete' method will execute UserController::delete(mixed $user)
 * 
 * note: the methods in the controller are not statics,
 * i am just using the '::' to demonstrate the methods
 */
Route::resource('users', UserController::class);

/**
 * the Route::resource() method takes another boolean paramters which is 'api' it's true by default
 * if set to false it will add other two route which are:
 * 
 * 'users/create' route with get method will execute UserController::create()
 * 'users/{user}/edit' route with get method will execute UserController::edit(mixed $user)
 */
Route::resource('users', UserController::class, false);

// constraints
Route::resource('users', UserController::class)
        ->where('users', ConstraintsInterface::ALPHA);

```

> Resource Routing : nested routes.

```php

/**
 * we can make a nested route
 * by writing the parent route segment and the child segment separated by a dot
 * 
 * example: 'users.posts' will make the following routes
 * 
 * 'users/{users}/posts' route with 'get' method will execute UserController::index()
 * 'users/{users}/posts' route with 'post' method will execute UserController::save()
 * 'users/{users}/posts/{posts}' route with 'get' method will execute UserController::show(mixed $post)
 * 'users/{users}/posts/{posts}' route with 'put, patch' methods will execute UserController::update()
 * 'users/{users}/posts/{posts}' route with 'delete' method will execute UserController::delete()
 */

Route::resource('users.posts', PostController::class);

/**
 * Constraints for nested routes can be like
 */

Route::resource('users.posts', PostController::class)
        ->where('users', ConstraintsInterface::NUM)
        ->where('posts', ConstraintsInterface::ALPHANUM);

```

> Nested Routing : shallow nesting

```php

/**
 * 
 * some times we don't need the parent id, only the child one for example
 * if we wan't a certain post we will get it by id, we don't need it's user's id for that
 * 
 * example: 'users.posts' with shallow nesting will make the following routes
 * 
 * 'users/posts' route with 'get' method will execute UserController::index()
 * 'users/posts' route with 'post' method will execute UserController::save()
 * 'users/posts/{posts}' route with 'get' method will execute UserController::show(mixed $post)
 * 'users/posts/{posts}' route with 'put, patchs' methods will execute UserController::update(mixed $post)
 * 'users/posts/{posts}' route with 'delete' method will execute UserController::delete(mixed $post)
 * 
 * shallow nesting is set to 'true' by default
 */

Route::resource(route: 'users.posts', action: PostController::class, api: true, shallow: true);
```

> Middleware Grouping

```php
/**
 * you can group multiple routes with a single middleware and group them with a controller
 * instead of assigning them to routes each time you define a route
 * 
 * for example:
 */

Route::setMiddlewares(RedirectIfAuthenticated::class)
        ->group(function () {
                    Route::get('test', function () {
                        // do something
                            });

                    Route::resource('posts', PostController::class);
                });

```

> Controller Grouping

```php


Route::controller(SomeController::class)
        ->group(
            function () {
                Route::get('someroute', 'theNameOfTheMethodInTheController');
                Route::get('images', 'getImages');

                /**
                 * if you pass something other than the controller method name
                 * a WrongRoutePatternException will be thrown
                 */
            }
        );

/**
 * you can group routes and both middlewares and controller
 */

Route::controller(SomeController::class)->setMiddlewares(SomeMiddleware::class)
        ->group(
            function () {
                Route::get('someroute', 'controllerMethod');
                Route::post('posts', 'store');

                Route::delete('users/{users}', 'deleteMethod');
            });
```

> Do some action on 404 request

```php

Route::notFound(function () {
    // some action that is done when the page your looking for is not found
});

/**
 * if you didn't define a notFound action a simple 404 http message
 * will be printed  
 */


```
