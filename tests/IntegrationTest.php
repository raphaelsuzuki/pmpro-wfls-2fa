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

	/**
	 * Test pmpro_wfls_init adds hooks.
	 */
	public function test_pmpro_wfls_init() {
		Monkey\Functions\expect('class_exists')->andReturn(true);
		Monkey\Functions\expect('function_exists')->andReturn(true);
		Monkey\Functions\expect('load_plugin_textdomain')->once();

		pmpro_wfls_init();

		$this->assertGreaterThan( 0, has_action( 'wp_enqueue_scripts', 'pmpro_wfls_enqueue_scripts' ) );
		$this->assertGreaterThan( 0, has_action( 'wp_enqueue_scripts', 'pmpro_wfls_extend_form_detection' ) );
		$this->assertGreaterThan( 0, has_filter( 'wfls_is_custom_login', 'pmpro_wfls_is_pmpro_login' ) );
	}

	/**
	 * Test pmpro_wfls_is_pmpro_login detection logic.
	 */
	public function test_pmpro_wfls_is_pmpro_login() {
		// Case 1: Already true
		$this->assertTrue( pmpro_wfls_is_pmpro_login( true ) );

		// Case 2: PMPro login page with credentials
		Functions\expect('pmpro_is_login_page')->andReturn(true);
		Functions\expect('function_exists')->with('pmpro_is_login_page')->andReturn(true);
		$_POST['username'] = 'testuser';
		$_POST['password'] = 'password';
		$this->assertTrue( pmpro_wfls_is_pmpro_login( false ) );

		// Case 3: PMPro login page WITHOUT credentials
		unset($_POST['username'], $_POST['password']);
		$this->assertFalse( pmpro_wfls_is_pmpro_login( false ) );

		// Case 4: Not a PMPro login page
		Functions\expect('pmpro_is_login_page')->andReturn(false);
		$this->assertFalse( pmpro_wfls_is_pmpro_login( false ) );
	}

	/**
	 * Test script enqueuing logic.
	 */
	public function test_pmpro_wfls_enqueue_scripts() {
		// Mock Wordfence controller
		$wfls = Mockery::mock('overload:WordfenceLS\Controller_WordfenceLS');
		$wfls->shouldReceive('shared->_login_enqueue_scripts')->once();
		
		Functions\expect('pmpro_is_login_page')->andReturn(true);
		Functions\expect('function_exists')->with('pmpro_is_login_page')->andReturn(true);
		Functions\expect('class_exists')->with('WordfenceLS\Controller_WordfenceLS')->andReturn(true);

		pmpro_wfls_enqueue_scripts();
	}
}
