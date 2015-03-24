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
	* [Route Arguments](#route-arguments)
	* [POST and GET Routing](#post-and-get-routing)
	* [Error Routing](#error-routing)
	* [Route Pre-Parsing](#route-pre-parsing)
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
* [Multiple Languages](#multiple-languages)
* [Error Logging](#error-logging)
* [Helpers](#helpers)
	* [File Helper](#helpers-file)
	* [Image Helper](#helpers-image)
		* [Image Helper Write Method](#helpers-image-write)
		* [Image Helper Effects Filters](#helpers-image-effects)
* [Licensing](#licensing)

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

##<a name="routing"></a> Routing
The routing in MaVeriCk is fairly flexible, and allows you to route different types of request to different controllers or controller methods.

A point to note is that if the config setting <code>config.xss_protection</code> is enabled then the routes that are requested are filtered. This can prevent against some URL injection attacks.

A typical route to the URL <code>/form</code> would be setup like this:

```php
route::get('form', 'main_controller->form');
```

The first argument is the URL path you're trying to match against (without the leading forward slash). This does not have to be a plain string either, and you can pass it a string regex, for example:

```php
route::any('^te[st]{2}', 'main_controller->regex_route_test_controller');
```

This would match the following requests made to the application:

* /test
* /tess
* /tets
* /tett

Regular expression matching in the routes can be a very powerful and flexible way of building your application.

###<a name="route-arguments"></a>Route Arguments
You can pass in arguments to your controller from matched parts of the route, and even construct strings that allow you to include multiple parts of a match by using the third argument to the <code>get()</code> method:

```php
route::get('articles/([a-z]{2})/([a-z]{2})', 'main_controller->articles', '$1-$2');
```

This would take the two matched arguments from the regular expression (each one matches 2 letters) and pass them in as a string in the form <code>'aa-bb'</code> to your controller method.

If you wish to pass the matched URL parameters in as separate arguments, then you can do that by passing them in with an array:

```php
route::get('articles/([a-z]{2})/([a-z]{2})', 'main_controller->articles', array('$1', '$2') );
```

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

###<a name="route-pre-parsing"></a>Route Pre-Parsing
At times it may be necessary to pre-parse the requested URLs before the routing happens, such as when you want to automatically set the language of the application based on URL segments.

In order to do this, you need to set a controller and method to call in your main config file, like this:

```php
'route_preparser' => 'route_controller->lang_filter',
```

This instructs the framework to call the <code>lang_filter()</code> method of the <code>route_controller</code> class before routing begins. <em>Note, it's not required that the route pre-parse method be in its own controller, but that can keep things a little tidier.</em>

The pre-parser has no requirements of what it must do. An example of how one might detect language culture parameters in the URL and set the application logic would be like this:

```php
function lang_filter()
{
	// any URL pre-parsing logic goes here
	// for example, this method will look for language culture segments in the URL, and return the default language of the app
	$regex = '/^\/?([a-z]{2})\/([a-z]{2})\b/';
		
	if(preg_match($regex, $_SERVER['REQUEST_URI'], $matches) )
	{
		// remove those matched segments from the URL so that regular routing can take place
		$_SERVER['REQUEST_URI'] = '/' . substr($_SERVER['REQUEST_URI'], 6);
			
		// as the app will use this if it exists - we should also strip the same parts out of this if they exist there
		if(isset($_SERVER['REDIRECT_URL']) && preg_match($regex, $_SERVER['REDIRECT_URL']) )
			$_SERVER['REDIRECT_URL'] = '/' . substr($_SERVER['REDIRECT_URL'], 6);
			
		// return the language culture member variable on main app object
		$app = maverick::getInstance();
		$app->language_culture = "{$matches[1]}_" . strtoupper($matches[2]);
	}
}
```

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

The example above uses a plain <code>get()</code> call, which is the equivalent to:

```sql
SELECT *
```

If you want to specify individual fields you want to fetch, or even use functions like <code>COUNT()</code> then you would pass in an array of values to <code>get()</code> like so:

```php
->get(array('t.*', 'COUNT(t.id) AS total'));
```

This is particularly useful if you're using aggregate functions in your queries.

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

##<a name="form-validation"></a>Form Validation
Form validation is very important, and MaVeriCk takes advantage of the new validation features built into the latest versions of PHP. This means that validation is solid and doesn't rely on a half-baked regular expression. As [this quote by Jamie Zawinski](http://blog.codinghorror.com/regular-expressions-now-you-have-two-problems/) points out:

> Some people, when confronted with a problem, think 
> "I know, I'll use regular expressions."
> Now they have two problems.

At the heart of the validator class is the <code>make()</code> method which accepts an array of rules, one element per form element that you wish to validate.

There are two different ways to set up rules:

###<a name="multiple-vs-single-rules"></a>Multiple vs Single Rules
The most basic form of rule binding is a single rule per form element:

```php
$rules = array(
	'web_address' => 'url',
	'phone' => 'phone',
);
```

This is fine if you only want one rule to be applied, but often you want more, such as making a field both required and checking if the value is a valid email:

```php
$rules = array(
	'email' => array('required', 'email'),
);
```

This allows you to couple a lot of simple rules to create some quite useful validation. The rules you can use are:

###<a name="rules"></a>Rules
There are many types of validation rules:

* [accepted](#rule-accepted)
* [after](#rule-after)
* [alpha](#rule-alpha)
* [alpha_apos](#rule-alpha_apos)
* [alpha_dash](#rule-alpha_dash)
* [alpha_numeric](#rule-alpha_numeric)
* [before](#rule-before)
* [between](#rule-between)
* [confirmed](#rule-confirmed)
* [email](#rule-email)
* [in](#rule-in)
* [ip](#rule-ip)
* [min](#rule-min)
* [max](#rule-max)
* [numeric](#rule-numeric)
* [phone](#rule-phone)
* [regex](#rule-regex)
* [required](#rule-required)
* [url](#rule-url)

####<a name="rule-accepted"></a>Accepted
This checks to see that a field has a value that is one of '1' (the string 1), 'y', 'yes', or 'checked'

####<a name="rule-after"></a>After
The <code>after</code> rule is given a date and the value is checked to see if it contains a date after the one in the rule. Note that dates are parsed by the <code>strtotime()</code> function so they should be in a format that PHP can understand.
The usage is:

```php
'birth' => array('after:2000-01-01'),
```

####<a name="rule-alpha"></a>Alpha
This rule checks that a fields value consists only of letters or spaces, using the regular expression <code>\p{L}</code> to check for letters instead of the older and incorrect <code>[a-z]</code>

####<a name="rule-alpha_apos"></a>Alpha_Apos
This rule is like the alpha rule but also allows apostrophes, to account for names like *O'Reilly*, for example

####<a name="rule-alpha_dash"></a>Alpha_Dash
Like the alpha rule, but it allows for hyphens and underscores also.

####<a name="rule-alpha_numeric"></a>Alpha_Numeric
Like the alpha rule but allows for numbers, including the characters <code>. - +</code>

####<a name="rule-before"></a>Before
The before rule behaves the same as the <code>[after](#rule-after)</code> rule, but checks that the field date is before the one specified in the rule

####<a name="rule-between"></a>Between
The between rule checks that the given fields value is a number (floating point values are allowed too) between two given values:

```php
'age' => array('between:18:100'),
```

Note that the field value will be converted to a number by the normal PHP parsing rules, so a value of <code>123abc</code> becomes <code>(int)123</code>

####<a name="rule-confirmed"></a>Confirmed
The Confirmed rule can be used to check that one field matches another, e.g. for passwords or email checking. The usage is:

```php
'email' => array('confirmed:name_of_field_to_match'),
```

This rule will fail if either the named field does not exist or its value does not match the field this rule is being applied to.

####<a name="rule-email"></a>Email
The email rule uses the internal <code>filter_var()</code> functionality to determine if the specified value is a valid email address. This is always preferred over [attempting to validate an email address with a regular expression](https://www.google.co.uk/search?q=why+not+to+validate+an+email+with+a+regex&oq=why+not+to+validate+an+email+with+a+regex).

####<a name="rule-in"></a>In
This rule checks that the given field value is within a finite range of values:

```php
'gender' => array('in:male:female:other'),
```

####<a name="rule-ip"></a>IP
The ip rule checks that the field value is a valid IPv4 or IPv6 formatted address, using the internal <code>filter_var()</code> function of PHP

####<a name="rule-min"></a>Min
This rule checks a given field value is ≥ the value specified in the rule:

```php
'age' = array('min:18'),
```

####<a name="rule-max"></a>Max
This is like the min rule but checks that a field value is ≤ the one specified in the rule.

```php
'age' = array('max:100'),
```

####<a name="rule-numeric"></a>Numeric
The numeric rule just checks that the field value is recognised by PHP as being a number.

####<a name="rule-phone"></a>Phone
The phone rule checks that the field is at least 8 characters long and is made up of digits, spaces, or plus and minus sign.

####<a name="rule-regex"></a>Regex
The regex rule is fairly complicated, and allows you to create a regular expression by which you validate the user input. So, for example, to validate a valid UK postcode:

```php
'postcode' => array('required', 'regex:/^([a-pr-uwyz][a-hk-y]{0,1}\d[\da-hjkst]{0,1} \d[abd-hjlnp-uw-z]{2})$/i'),
```
####<a name="rule-required"></a>Required
Probably the most necessary rule, this checks that a field exists in the posted data and has a non-empty value.

####<a name="rule-url"></a>URL
This rule uses the internal PHP <code>filter_var()</code> function to check that the field value is a valid URL.

###<a name="failure-messages"></a>Failure Messages
The message that gets returned and made available in your view when a field fails the validation process is set out in the <code>/maverick/config/validator.php</code> file.

The format of these uses <code>%s</code> as placeholders. The first one is the name of the field, the second is the first argument of the rule, the third is the second argument, and so on. So, for example, the between rule uses 3 <code>%s</code> placeholders because it accepts two arguments:

```php
'between' => 'The %s field must be between %s and %s',
```

You can change these messages as your application requires. Currently there is no provision to allow for custom validation messages per form.

###<a name="displaying-errors"></a>Displaying Errors
There are a few different ways to display or return error messages in your view. The simplest and probably the most useful is to just show the first error for a given field:

```php
echo validator::get_first_error('name');
```

If you wish to get all errors for a given field, then you would use this syntax:

```php
echo validator::get_all_errors('name');
```


*Note that the returned value is an array of errors, so you can't just echo is straight out into your view, you will need to loop through it*

If instead you wish to get all errors for the entire form, then you can pass no field name to the <code>get_all_errors()</code> method.

Lastly, if you just want the number of fields with errors (if a field has more than one error it will only count once in this value) then you can use this method of the validator class:

```php
validator::get_error_count();
```

You could use this in your view to determine how best to display errors if there are a lot of them, for example.

####<a name="wrapping-tags-around-errors"></a>Wrapping Tags Around Errors
Displaying an error as-is might not be what you always want, and it may be beneficial to wrap your individual errors with custom HTML tags or other text. For this, both of the error methods mentioned above can take an optional second argument that is an array containing the opening and closing tags:

```php
validator::get_first_error('name', array('<span class="error">', '</span>'));

validator::get_all_errors(null, array('<span class="error">', '</span>'));
```

When using the latter method, each error for the every field is wrapped with the given tags. You can still pass in the name of a field to this method to only retrieve errors for a specific field.

##<a name="multiple-languages"></a>Multiple Languages
MaVeriCk supports i18n language cultures using <code>gettext()</code> and <code>.po</code> files. Firstly, you need a config file called <code>lang.php</code> in your main config directory. The contents of the file should look like this:

```php
<?php
return array(
	
	// see https://msdn.microsoft.com/en-gb/library/ee825488%28v=cs.20%29.aspx for a full list of language cultures
	// this *is* case-sensitive and match the culture exactly for best interoperability with translating tools
	// this will be used as the defaul language culture
	// if this is set to empty, then it will default to en-GB from within the base maverick class itself, you should not specify the .utf8 ending for this!
	// finally, you can use either hyphens or the underscore here, as hyphens will be replaced by underscores internally
	'default' => 'en-GB',
	
	'active' => true,	// set this to true if you want to make use of i18n translation features in your application
);
```

This sets the default language to use for the application, and instructs MaVeriCk to use multiple languages. If <code>active</code> is missing or not set to true then the framework will not properly set the language environment settings, so you may get unexpected output from your <code>gettext()</code> calls.

That's it set up. From within your views you can then call any of the <code>gettext()</code> functions, such as:

```php
echo _('test');
```

The <code>.po</code> and <code>.mo</code> files should be kept within the <code>/maverick/locale/</code> directory, in the normal gettext structure, e.g.:

* /maverick/locale
	* en_GB
		* LC_MESSAGES
	* de_DE
		* LC_MESSAGE

Each <code>.po</code> file should be called <code>maverick.po</code> unless you change the gettext message space in any of your calls (such as with <code>dcgettext()</code> ).

##<a name="error-logging"></a>Error Logging
MaVeriCk comes with a basic class for logging errors in your application, aptly named <code>error</code>, which are saved to the <code>/logs</code> directory (outside of the web root for security).

There are two static methods available:

* <code>log</code>
* <code>show</code>

The first method just displays a given message with a <code>die()</code> call (I have plans to turn this into something nicer later) with an optional argument to specify the HTTP error code (defaults to 500 - Internal Server Error). This is probably not ideal for your application as it won't contain any more detail than you pass in the <code>$message</code> argument.

If you require more detail, you can use the <code>log()</code> method:

```php
error::log('Table not specified', true, 501);
```

The first argument is the error message, just like the <code>show()</code> method, and the second and third are optional arguments to determine whether to show the message (using the aforementioned method) and what error code to use (defaults to 500).

Note that logging to a file only occurs if the <code>log_errors</code> option is set to <code>true</code> in the config.

You can also specify the level of detail which gets logged by changing the value of the <code>log_detail</code> config option. When set to <code>true</code>, a stack trace is generated and added to the log message. Bear in mind that this can potentially generate large logs if you have unresolved errors in your code, so it is not advised to leave this set to <code>true</code> on a production environment.

## <a name="helpers"></a>Helpers
MaVeriCk includes serveral helper classes that are kept within the <code>/maverick/vendors/helpers</code> directory. All helpers are within the <code>helpers</code> namespace.

### <a name="helpers-file"></a>File Helper
The file helper contains methods for dealing with files (obviously!) and are used like this:

```php
$file = new \helpers\file();
echo $file->type();
```

The constructor can accept an optional string path, but this can be set later:

```php
$file = new \helpers\file('/path/to/file');
```

The available methods for this class are:

```php
type($no_symbolic_check = false); // determine what a path points to, either a file or directory. The optional argument highlights if it's also a symbolic link.

size($human_size = false); // determines the size in bytes of a file (or directory on some file systems). The optional argument returns the size in a human-readable string

tree($dir=false); // creates a multi-dimensional array representing the directory/file tree of a given path

info(); // returns a stdClass object containing information on a file, such as mime type, creation dates, and permissions
```

### <a name="helpers-image"></a> Image Helper
The image helper contains methods for dealing with images. This is still in progress so the helper methods are limited. The constructor works in one of two ways:

```php
$image = new \helpersimage('path/to/existing/image');
$image2 = new \helpers\image(null, 100, 100, 'jpg');
```

The second method accepts as arguments the width and height of your new image and the format you wish it to be. Currently only jpg, gif, and png are supported.

The available methods are:

```php
resize($width, $height, $type='regular');
/* the $width and $height arguments can be any of the following:
* # auto - will force $width to depend on the value of $height and vice-versa
* # nochange - force this dimension to remain unchanged
* # nn - a value of the pixel size for this dimension
* # nn% - a value of a percentage increase (above 100%) or decrease (below 100%) in size of the existing dimension value
*
* the $type is either 'regular' which allows the image to become distorted if no dimension is set to auto, or 'crop' which allows the image to be smart-cropped if necessary
*
* note that if both dimensions are set to 'auto' or 'nochange' then this method returns false and does nothing
*/

output($filename=null); // this outputs the image to the browser. Passing in a path and filename as a string argument will instead save the image to that location

write($text='', $x=0, $y=0, $width=0); // adds text to an image using the existing font, font size, and foreground colour

effect($filter, $params = array(), $repeat = 1 ); // this applies a filter effect to the image, the list of effects and their parameters is below
```

####<a name="helpers-image-write"></a>Image Helper Write Method
The <code>write()</code> method is a little more complicated than some of the other image methods. First, you can set many of the font details by setting the member variables directly:

```php
$image->font = MAVERICK_BASEDIR.'views/LiberationSerif-Regular.ttf';
$image->foreground = '#fff';
$image->font_size = 18;
$image->line_height = 18;
```

The only requirements are:

* <code>font</code> must be a path to a valid ttf file. Some otf fonts can be used, but only if they contain ttf outlines
* <code>font_size</code> and <code>line_height</code> must be valid integers, representing the pixel sizes respectively
* <code>foreground</code> can be any valid hex colour value in the form:
	* &#35;rgb
	* &#35;rgba
	* &#35;rrggbb
	* &#35;rrggbbaa

The <code>write()</code> method itself takes 4 arguments (all optional):

* <code>$text</code> - the string to use, if empty the method does nothing and returns false
* <code>$x</code> and <code>$y</code> - the coordinates of the bottom left position for the line of text (or first line of text when forcing multi-line text)
* <code>$width</code> - the width of the text block to fit the text into. If this is 0 or not specified, the text will be all on one line. If it is a positive value, the text will wrap onto multiple lines in order to fit the width of the bounding box

####<a name="helpers-image-effects"></a>Image Helper Effects Filter

Below are the filter effects available and the parameters you will need to pass in, and a reference image:

![Reference image of earth](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth.jpg)

* <code>brightness</code> - this accepts a single number between -255 and 255 which is the brightness to apply
* <code>contrast</code> - this accepts a single number between -100 and 100 which adjusts the contrast
* <code>colorize</code> - this accepts 3 values in an array, each between -255 and 255 which is the colour adjustment you wish to apply. For example, passing in <code>array(-255, 0, 0)</code> will remove the red from the image
* <code>multiply</code> - this behaves like the multiply colour effect filter in Photoshop and accepts the same array of colour values as the <code>colorize</code> filter
<br/>![Multiply filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_multiply.jpg)
* <code>pixelate</code> - this pixellates the image, and accepts two parameters in an array. The first is the pixel block size, the second is a boolean that determines whether ot not to use advanced pixellation or not
<br/>![Pixelate filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_pixelate.jpg)
* <code>negate</code> - inverts the colours of the image
* <code>grayscale</code> - turns the image greyscale
* <code>edgedetect</code> - applies the edgedetect filter
* <code>emboss</code> - applies the GD emboss filter
<br/>![Emboss filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_emboss.jpg)
* <code>gaussian_blur</code> - applies the GD gaussian_blur filter
* <code>selective_blur</code> - applies the GD selective_blur filter
* <code>mean_removal</code> - applies the GD mean_removal filter
* <code>emboss2</code> & <code>emboss3</code> - different emboss methods which utilise the <code>imageconvolution()</code> matrix function
* <code>edgedetect2</code> & <code>edgedetect3</code> - different edgedetect methods which utilise the <code>imageconvolution()</code> matrix function
<br/>![Edge Detect 3 filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_edgedetect3.jpg)
* <code>gaussian_blur2</code> - a different blur method which utilises the <code>imageconvolution()</code> matrix function
<br/>![Gaussian Blur 2 filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_gaussian_blur2.jpg)
* <code>sharpen</code> - sharpens the image
* <code>rotate</code> - rotates the image byetween -360 or 360 degrees. This can alter the dimensions of the image
<br/>![Rotate filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_rotate.jpg)
* <code>round_pixelate</code> - a pixellation method which accepts a value between 0 and 255 which is the size of the pixels. The effect is a pixelised image which uses round instead of square pixels
<br/>![Round Pixelate filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_round_pixelate.jpg)
* <code>scatter</code> - an effect which scatters the pixels of the image. It accepts a parameter value between 0 and 50, which affects how much pixels can be scattered
<br/>![Scatter filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_scatter.jpg)
* <code>noise</code> - accepts a value between 0 and 255 which is how much noise is added to the image
<br/>![Noise filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_noise.jpg)
* <code>oil</code> - creates an oil painting effect. This effect accepts an array of 3 numbers: strength (0-255), difference (0-20), and brush size (0-50). The difference parameter affects the change in colour before it's reapplied as a brush
<br/>![Oil filter](https://raw.githubusercontent.com/AshleyJSheridan/maverick/master/docs/img/earth_oil.jpg)

## <a name="licensing"></a>Licensing
MaVeriCk is available under the MIT License

The MIT License (MIT)

Copyright (c) [year] [fullname]

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions: 

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.