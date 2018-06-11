<?php
/**
 * Class GetPushResources
 *
 * @package H2push
 */

/**
 *  Test cases for \Required\H2Push\get_push_resources().
 */
class GetPushResources extends WP_UnitTestCase {

	/**
	 * Test that \Required\H2Push\get_push_resources() exists.
	 */
	public function test_get_push_resources_exists() {
		$this->assertTrue( function_exists( '\Required\H2Push\get_push_resources' ) );
	}
}
