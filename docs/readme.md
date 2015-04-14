# MaVeriCk
MaVeriCk is a lightweight PHP MVC framework, that comes with the typical features required for small websites:


* Versatile config across multiple files - so you can split the DB config from your validation error messages, etc.
* Class autoloading - don't worry about everything being included at the beginning of your code, and load only what you use
* Versatile routing based on the type of request. You don't want to load in the validator the first time you display a form page, only once it's been submitted, so you can split your GET and POST requests right at the start
* Powerful DB abstraction layer based loosely on the ActiveRecord pattern - allowing for complicated queries to be built in an object orientated manner, and queries will be properly parameterised
* Powerful form validation based on arrays of rules, based a little on the concept behind triggering validation rules in Laravel.
* Simple views that you can chain as many extra parameters to as you want

The full documentation for this can now be found on the [wiki for the project](https://github.com/AshleyJSheridan/maverick/wiki), as it became too much for a single `readme.md` file.