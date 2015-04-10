<?php
require_once '../vendor/autoload.php';

use \Laracasts\Integrated\Extensions\Goutte as IntegrationTest;

class example_test extends IntegrationTest
{
	protected $baseUrl = 'http://maverick.local';
	
	public function test_load_home()
	{
		$this->visit('/')
			->andSee('another test');
	}
}