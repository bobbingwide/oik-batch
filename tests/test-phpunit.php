<?php // (C) Copyright Bobbing Wide 2016 

use PHPUnit\Framework\TestCase;
	
echo "There you go: " . __FILE__ . PHP_EOL;
	
class UnitTest extends TestCase  {
	
	public function test_case() {
		$this->assertTrue( true );
	}
		
		
	public function test_oik_path_available() {
		$this->assertTrue( function_exists( 'oik_path' ) );
	}

}
	
