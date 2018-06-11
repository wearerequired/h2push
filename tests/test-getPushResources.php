<?php
/**
 * Class GetPushResources
 *
 * @package H2push
 */

use \Required\H2Push;

/**
 *  Test cases for \Required\H2Push\get_push_resources().
 */
class GetPushResources extends WP_UnitTestCase {

	public $old_wp_scripts;
	public $old_wp_styles;

	function setUp() {
		parent::setUp();

		$this->old_wp_scripts = isset( $GLOBALS['wp_scripts'] ) ? $GLOBALS['wp_scripts'] : null;
		remove_action( 'wp_default_scripts', 'wp_default_scripts' );
		remove_action( 'wp_default_styles', 'wp_default_styles' );
		$GLOBALS['wp_scripts']                  = new WP_Scripts();
		$GLOBALS['wp_scripts']->default_version = get_bloginfo( 'version' );
		$GLOBALS['wp_styles']                  = new WP_Styles();
		$GLOBALS['wp_styles']->default_version = get_bloginfo( 'version' );
	}

	function tearDown() {
		$GLOBALS['wp_scripts'] = $this->old_wp_scripts;
		$GLOBALS['wp_styles'] = $this->old_wp_styles;
		add_action( 'wp_default_scripts', 'wp_default_scripts' );
		add_action( 'wp_default_styles', 'wp_default_styles' );

		parent::tearDown();
	}

	/**
	 * Test that \Required\H2Push\get_push_resources() exists.
	 */
	public function test_get_push_resources_exists() {
		$this->assertTrue( function_exists( '\Required\H2Push\get_push_resources' ) );
	}

	public function test_get_push_resources_script_with_absolute_url() {
		wp_enqueue_script( 'my-script', home_url( '/script.js' ), [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_script = $resources[0];
		$this->assertSame( '/script.js?ver=1.0', $my_script['href'] );
		$this->assertSame( 'script', $my_script['as'] );
	}

	public function test_get_push_resources_style_with_absolute_url() {
		wp_enqueue_style( 'my-style', home_url( '/style.css' ), [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css?ver=1.0', $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}

	public function test_get_push_resources_script_with_relative_url() {
		wp_enqueue_script( 'my-script', '/script.js', [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_script = $resources[0];
		$this->assertSame( '/script.js?ver=1.0', $my_script['href'] );
		$this->assertSame( 'script', $my_script['as'] );
	}

	public function test_get_push_resources_style_with_relative_url() {
		wp_enqueue_style( 'my-style', '/style.css', [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css?ver=1.0', $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}

	public function test_get_push_resources_script_with_schemeless_url() {
		$scheme_less_home_url = str_replace( 'http:', '', home_url( '', 'http' ) );
		wp_enqueue_script( 'my-script', $scheme_less_home_url . '/script.js', [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_script = $resources[0];
		$this->assertSame( '/script.js?ver=1.0', $my_script['href'] );
		$this->assertSame( 'script', $my_script['as'] );
	}

	public function test_get_push_resources_style_with_schemeless_url() {
		$scheme_less_home_url = str_replace( 'http:', '', home_url( '', 'http' ) );
		wp_enqueue_script( 'my-style', $scheme_less_home_url . '/style.css', [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css?ver=1.0', $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}
}
