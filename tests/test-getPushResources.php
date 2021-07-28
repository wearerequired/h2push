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

	/**
	 *  An instance of the previous WP_Scripts.
	 *
	 * @var WP_Scripts|null
	 */
	private $old_wp_scripts;

	/**
	 *  An instance of the previous WP_Styles.
	 *
	 * @var WP_Styles|null
	 */
	private $old_wp_styles;

	/**
	 * Performs setup tasks for every test.
	 */
	public function setUp() {
		parent::setUp();

		$this->old_wp_scripts = isset( $GLOBALS['wp_scripts'] ) ? $GLOBALS['wp_scripts'] : null;
		$this->old_wp_styles  = isset( $GLOBALS['wp_styles'] ) ? $GLOBALS['wp_styles'] : null;

		remove_action( 'wp_default_scripts', 'wp_default_scripts' );
		remove_action( 'wp_default_styles', 'wp_default_styles' );

		$GLOBALS['wp_scripts']                  = new WP_Scripts();
		$GLOBALS['wp_scripts']->default_version = get_bloginfo( 'version' );
		$GLOBALS['wp_styles']                   = new WP_Styles();
		$GLOBALS['wp_styles']->default_version  = get_bloginfo( 'version' );
	}

	/**
	 * Performs setup tasks after every test.
	 */
	public function tearDown() {
		$GLOBALS['wp_scripts'] = $this->old_wp_scripts;
		$GLOBALS['wp_styles']  = $this->old_wp_styles;

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

	/**
	 * Test that absolute script URLs are pushed relative.
	 */
	public function test_get_push_resources_script_with_absolute_url() {
		wp_enqueue_script( 'my-script', home_url( '/script.js' ), [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_script = $resources[0];
		$this->assertSame( '/script.js?ver=1.0', $my_script['href'] );
		$this->assertSame( 'script', $my_script['as'] );
	}

	/**
	 * Test that absolute stylesheet URLs are pushed relative.
	 */
	public function test_get_push_resources_style_with_absolute_url() {
		wp_enqueue_style( 'my-style', home_url( '/style.css' ), [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css?ver=1.0', $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}

	/**
	 * Test that relative script URLs are pushed.
	 */
	public function test_get_push_resources_script_with_relative_url() {
		wp_enqueue_script( 'my-script', '/script.js', [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_script = $resources[0];
		$this->assertSame( '/script.js?ver=1.0', $my_script['href'] );
		$this->assertSame( 'script', $my_script['as'] );
	}

	/**
	 * Test that relative style URLs are pushed.
	 */
	public function test_get_push_resources_style_with_relative_url() {
		wp_enqueue_style( 'my-style', '/style.css', [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css?ver=1.0', $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}

	/**
	 * Test that scheme-less script URLs are pushed relative.
	 */
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

	/**
	 * Test that scheme-less stylesheet URLs are pushed relative.
	 */
	public function test_get_push_resources_style_with_schemeless_url() {
		$scheme_less_home_url = str_replace( 'http:', '', home_url( '', 'http' ) );
		wp_enqueue_style( 'my-style', $scheme_less_home_url . '/style.css', [], '1.0' );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css?ver=1.0', $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}

	/**
	 * Test that stylesheet URLs without a version are getting the default WordPress version appended.
	 */
	public function test_get_push_resources_style_with_default_version() {
		wp_enqueue_style( 'my-style', '/style.css', [] );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css?ver=' . get_bloginfo( 'version' ), $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}

	/**
	 * Test that stylesheet URLs with a version set to `null` are not getting a version appended.
	 */
	public function test_get_push_resources_style_with_no_version() {
		wp_enqueue_style( 'my-style', '/style.css', [], null );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_style = $resources[0];
		$this->assertSame( '/style.css', $my_style['href'] );
		$this->assertSame( 'style', $my_style['as'] );
	}

	/**
	 * Test that script URLs with a version set to `null` are not getting a version appended.
	 */
	public function test_get_push_resources_script_with_no_version() {
		wp_enqueue_script( 'my-script', '/script.js', [], null );

		$resources = H2Push\get_push_resources();
		$this->assertNotEmpty( $resources );
		$this->assertCount( 1, $resources );
		$my_script = $resources[0];
		$this->assertSame( '/script.js', $my_script['href'] );
		$this->assertSame( 'script', $my_script['as'] );
	}

	/**
	 * Test that external script URLs are not included.
	 */
	public function test_get_push_resources_script_with_external_file() {
		wp_enqueue_script( 'my-script', 'https://example.com/script.js', [], null );

		$resources = H2Push\get_push_resources();
		$this->assertCount( 0, $resources );
	}

	/**
	 * Test that external script URLs from an allowed host are included.
	 */
	public function test_get_push_resources_script_with_external_file_from_allowed_host() {
		wp_enqueue_script( 'my-script', 'https://example.com/script.js', [], null );

		$filter = static function( $allowed, $host ) {
			return $allowed || 'example.com' === $host;
		};

		add_filter( 'h2push.is_allowed_push_host', $filter, 10, 2 );
		$resources = H2Push\get_push_resources();
		remove_filter( 'h2push.is_allowed_push_host', $filter );

		$this->assertCount( 1, $resources );
		$my_script = $resources[0];
		$this->assertSame( 'https://example.com/script.js', $my_script['href'] );
		$this->assertSame( 'script', $my_script['as'] );
	}
}
