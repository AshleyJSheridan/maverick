# MaVeriCk
MaVeriCk is a lightweight PHP MVC framework, that comes with the typical features required for small websites:


* Versatile config across multiple files - so you can split the DB config from your validation error messages, etc.
* Class autoloading - don't worry about everything being included at the beginning of your code, and load only what you use
* Versatile routing based on the type of request. You don't want to load in the validator the first time you display a form page, only once it's been submitted, so you can split your GET and POST requests right at the start
* Powerful DB abstraction layer based loosely on the ActiveRecord pattern - allowing for complicated queries to be built in an object orientated manner, and queries will be properly parameterised
* Powerful form validation based on arrays of rules, based a little on the concept behind triggering validation rules in Laravel.
* Simple views that you can chain as many extra parameters to as you want

This documentation will instruct on how to get it set up on a server and how to use it.

* [Installing](#installing)
* [Configuration](#configuration)
	* [Retrieving Config Settings](#retrieving-config-settings)
* [Routing](#routing)
	* [POST and GET Routing](#post-and-get-routing)
	* [Error Routing](#error-routing)
* [Controllers](#controllers)
* [Models](#models)
	* [Select Queries](#select-queries)
		* [WHERE Clauses](#where-clauses)
		* [JOIN Clauses](#join-clauses)
		* [GROUP BY Clause](#group-by-clause)
		* [ORDER BY Clauses](#order-by-clauses)
	* [Insert Queries](#insert-queries)
	* [Update Queries](#update-queries)
	* [Delete Queries](#delete-queries)
* [Views](#views)
* [Form Validation](#form-validation)
	* [Multiple vs Single Rules](#multiple-vs-single-rules)
	* [Rules](#rules)
	* [Failure Messages](#failure-messages)
	* [Displaying Errors](#displaying-errors)
		* [Wrapping Tags Around Errors](#wrapping-tags-around-errors)

##<a name="installing"></a>Installing

Installation of MaVeriCk is simple, and requires nothing more than to clone the repository and point your virtual host configuration to the <code>httpdocs</code> directory:

```Apache
#Listen 80
#NameVirtualHost *:80

<VirtualHost *:80>
    DocumentRoot /var/www/html/maverick/httpdocs
    ServerName maverick.local

    <Directory "/var/www/html/maverick/httpdocs">
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

The <code>maverick</code> directory is kept out of the web root for security reasons, as this prevents certain attacks from viewing your configuration settings, for example.

##<a name="configuration"></a>Configuration

All configuration is handled by files within the <code>maverick/config</code> directory. Each config file is processed at the beginning once the framework loads up. You can add your own custom config files here, just remember that each one must return an associative array.

One advantage to keeping the config settings in separate files is that it allows you to have custom config settings for different environments. For example, your DB settings may be different on various server deployments, so you can just swap out that config file as necessary.

###<a name="retrieving-config-settings"></a>Retrieving Config Settings

At various points in your code, you may need to retrieve a specific config value. This can be done with the <code>get_config()</code> method of a <code>maverick</code> instance:

```php
$maverick = maverick::getInstance();

$db = $maverick->get_config('db.database');
```

Don't worry about creating many calls to the <code>getInstance</code> method, as the class is a singleton and only initiates one instance of itself.

At the moment, the <code>get_config()</code> method can only return values one level deep in the config array, but there are plans to extend this to be more flexible.

##<a name="routing"></a> Routing
The routing in MaVeriCk is fairly flexible, and allows you to route different types of request to different controllers or controller methods.

A point to note is that if the config setting <code>config.xss_protection</code> is enabled then the routes that are requested are filtered. This can prevent against some URL injection attacks.

###<a name="post-and-get-routing"></a>POST and GET Routing
So, for example, imagine a form that posts to itself. You can have one route set up that just displays the form and does nothing more, and a second route that handles submitted data and processes it:

```php
route::get('form', 'main_controller->form');
route::post('form', 'main_controller->form_post');
```

This means that you can avoid loading in any extra validation classes when it's not necessary, and keep your requests as quick or complex as they actually need to be.

If you want a route that responds to both GET and POST requests, then you can use the <code>any</code> type:

```php
route::any('', 'main_controller->home');
```

###<a name="error-routing"></a>Error Routing
You can capture (404 only at the moment) errors and push them to a specific controller with a route like this:

```php
route::error('404', 'main_controller->error');
```

This allows you to set up a default action when there is a request to a route that does not yet exist.

##<a name="controllers"></a>Controllers
Controllers in your application should handle the business logic, and all controllers should inherit from the <code>base_controller</code> class:

```php
class main_controller extends base_controller
{}
```

Within the class, all methods that will be called by a route should be public. You can use private methods internally where you may need to share parts of logic across various route methods.

There are no requirements on what you do or return within a controller, but typically you would create a <code>view</code>

## <a name="models"></a>Models
The models are where you handle the data in your application, whether that be retrieving or storing data in a database, saving to a file, or calling an API. To aid in querying a database, there is an ORM based loosely on the ActiveRecord pattern that allows you to build DB queries in an OOP manner.

To create a model, just make an empty class in the <code>maverick/models</code> directory. This will be autoloaded as required when you call it, e.g.:

```php
$data = content::get_all_from_test_table();
```

One thing to note is that all methods in a model class should be static.

###<a name="select-queries"></a>Select Queries
Selects are generally the most typical types of queries in a website, and a simple one to retrieve all records in a given table would look like this:

```php
$data = db::table('table_name')->get();

return $data->fetch();
```

All queries start with a line like <code>db::table()</code> as this instructs the <code>db</code> class what table in the database you will be operating on. From there, you can chain on various methods to generate more complex queries.

####<a name="where-clauses"></a>WHERE Clauses
To add a <code>WHERE</code> clause to your query, you could chain on the <code>where()</code> method:

```php
$data = db::table('table_name')
	->where('somefield', '=', db::raw('value')
	->where('otherfield', '!=', db::raw('another value')
	->get();
```

You will note that you can chain on multiple <code>where()</code> calls as required. Each one accepts three parameters:

1. the field you're operating on
1. the condition
1. the value

Note that 2 and 3 can be interchanged, as it doesn't matter much where you put them in the resulting SQL query. Also note the used of the <code>db::raw()</code>call, which triggers the query builder to pass this to the PDO call as a parameter and not a literal. This is to ensure that your queries are properly sanitised.

The condition part can be one of the following:

* <code>=</code>
* <code>!=</code>
* <code>&lt;</code>
* <code>&gt;</code>
* <code>&lt;=</code>
* <code>&gt;=</code>

Any other conditions mean the whole <code>where()</code> call is ignored.

There are two more types of <code>WHERE</code> clauses, and these have additional chain methods by which to call them:

```php
->whereIn('t.id', array(1,3))
->whereNotIn('t.id', array(1,3))
```

These accept a field as the first parameter and an array of values as the second. The array will be passed to PDO as a series of parameters.

####<a name="join-clauses"></a>JOIN Clauses
When you need to join additional tables it will be necessary to chain on a <code>join()</code> call. This takes on the following syntax:

```php
->leftJoin('table2 AS t2', array('t2.id', '=', 't.id') )
```

The latter part is similar to the <code>where()</code> call, in that you specify the fields and condition of the join. The first argument is just referring to the table you're joining and giving it a reference name.

If you wish to join a table based on more than one condition, you can pass a multi-dimensional array as the second argument:

```php
->leftJoin('table2 AS t2', array(
	array('t2.id', '=', 't.id'),
	array('t2.display', '=', db::raw('yes')),
))
```

This also demonstrates using escaped parameters within the <code>JOIN</code> clause.

Joins can be any of the following type:

* <code>left</code>
* <code>right</code>
* <code>outer</code>

And the method calls are named as after the type of join.

####<a name="group-by-clause"></a>GROUP BY Clause
When you want to group the results, you would use this clause. It takes the following syntax:

```php
->groupBy('field')
```

####<a name="order-by-clauses"></a>ORDER BY Clause
When you need to order the results, you would add an <code>orderBy()</code> to your query chain. You can specify as many of these as you need, and the query builder will add them to the resulting query.

```php
->orderBy('field', 'direction')
```

The direction is one of either <code>asc</code> or <code>desc</code> and specifies how this field is organised.

###<a name="insert-queries"></a>INSERT Queries
Insert calls take a simple form, and will only use the <code>table()</code> and <code>insert()</code> calls:

```php
$insert = db::table('table_name')
	->insert(array('field1'=>'some value', 'field2'=>date("ymd His") ) );
```

The array that is passed to <code>insert()</code> is associative, and each array key should correspond with the name of a field in your table.

A bulk insert takes the same form, but the array passed is a multidimensional, like so:

```php
$insert = db::table('table_name')
	->insert(array(
		array('field1'=>'some value', 'field2'=>'value2' ),
		array('field1'=>'some value', 'field2'=>'value2' ),
		array('field1'=>'some value', 'field2'=>'value2' ),
));
```

The query builder will look at the type of array you've passed it and determine how to deal with the data.

###<a name="update-queries"></a>UPDATE Queries
An update query is like an insert, with the exception of a <code>WHERE</code> clause which limits the scope of the updates:

```php
$update = db::table('test')
	->where('field', '=', db::raw('some value') )
	->update( array('other_field'=>db::raw('different value') ) );
```

The main difference is that the array passed to <code>update()</code> is a single dimension only.

###<a name="delete-queries"></a>DELETE Queries
These are even simplere, and just accept a <code>table()</code>, a <code>where()</code> (or many), and a <code>delete()</code> call:

```php
$delete = db::table('table')
	->where('id', '=', db::raw(10))
	->delete();
```

##<a name="views"></a>Views
Within your controllers you can call a view like this:

```php
$view = view::make('view_file')->render();
```

The <code>make()</code> call references the file of the view (without the <code>.php</code> file extension) and the <code>render()</code> call creates the HTML output.

To pass additional data along to the view you can chain on a <code>with()</code> call:

```php
$view = view::make('includes/template')->with('page', 'form')->render();
```

You can chain on as many <code>with()</code> calls as you need, and put whatever want into them. From within the view, you can get at your data with a call like this:

```php
data::get('page');
```

If you wish to suppress the automatic output of your rendered page, pass in a <code>false</code> parameter to the <code>render()</code> call like so:

```php
->render(false);
```

This causes the method to return the HTML (or other content) instead of echoing it to the output stream.