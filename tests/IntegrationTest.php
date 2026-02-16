<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test that the plugin constants are defined.
	 */
	public function test_constants_defined() {
		// Since these are defined at the top level of the file, 
		// they should be available after inclusion.
		$this->assertTrue( defined( 'PMPRO_WFLS_2FA_VERSION' ) );
	}

	/**
	 * Test dependency check function.
	 */
	public function test_pmpro_wfls_check_dependencies() {
		// Mock WordfenceLS\Controller_WordfenceLS class exists
		Functions\expect('class_exists')
			->with('WordfenceLS\Controller_WordfenceLS')
			->andReturn(true);

		// Mock pmpro_is_login_page function exists
		Functions\expect('function_exists')
			->with('pmpro_is_login_page')
			->andReturn(true);

		$this->assertTrue( pmpro_wfls_check_dependencies() );
	}
}
