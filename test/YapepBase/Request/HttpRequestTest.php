<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Request
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Request;
use YapepBase\Request\HttpRequest;

/**
 * HttpRequestTest class
 *
 * @package    YapepBase
 * @subpackage Test\Request
 */
class HttpRequestTest extends \YapepBase\BaseTest {

	/**
	 * The request
	 *
	 * @var \YapepBase\Request\HttpRequest
	 */
	protected $request;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {

		parent::setUp();

		$this->request = $this->getRequest();
	}

	/**
	 * Returns a request instance
	 *
	 * @param array $additionalServerFields   Any additional fields for the server array
	 *
	 * @return \YapepBase\Request\HttpRequest
	 */
	protected function getRequest(array $additionalServerFields = array()) {
		$server = array_merge(array(
			'REQUEST_URI'    => '/target',
			'REQUEST_METHOD' => 'post',
		), $additionalServerFields);

		$get = array(
			'param' 	=> 'get_param',
			'username' 	=> 'username_value',
			'password' 	=> 'password_value',
		);
		$post = array(
			'param' => 'post_param',
			'post_param' => 1,
		);
		$cookie = array(
			'param' => 'cookie_param',
			'cookie' => 1,
		);
		$env = array(
			'envParam' => 'env_value',
		);
		$files = array(
			'uploadedFile' => array(
				'name' => 'test',
				'type' => 'text/plain',
				'tmp_name' => '/tmp/test',
				'error' => UPLOAD_ERR_OK,
				'size' => 10,
			),
			'noUploadedFile' => array(
				'name' => '',
				'type' => '',
				'tmp_name' => '',
				'error' => UPLOAD_ERR_NO_FILE,
				'size' => 0,
			),
		);

		return new HttpRequest($get, $post, $cookie, $server, $env, $files, true);
	}

	/**
	 * Test if the getGet responses with the correct values
	 */
	public function testGetGetWithValue() {
		$this->assertSame('username_value', $this->request->getGet('username'));
		$this->assertSame('password_value', $this->request->getGet('password'));
	}

	/**
	 * Test ig the getPost responses with the correct value
	 */
	public function testGetPostWithValue() {
		$this->assertSame('post_param', $this->request->getPost('param'));
	}

	/**
	 * Test if the getCookie responses with the correct value
	 */
	public function testGetCookieWithValue() {
		$this->assertSame('cookie_param', $this->request->getCookie('param'));
	}

	/**
	 * Test if the getGet responses with the default setted value if the parameter doesn't exist.
	 */
	public function testNotExistedGetParamWithDefaultValue() {
		$this->assertSame($this->request->getGet('not_existed_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getPost responses with the default setted value if the parameter doesn't exist.
	 */
	public function testNotExistedPostParamWithDefaultValue() {
		$this->assertSame($this->request->getPost('not_existed_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getCookie responses with the default setted value if the parameter doesn't exist.
	 */
	public function testNotExistedCookieWithDefaultValue() {
		$this->assertSame($this->request->getCookie('not_existed_cookie_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getParam response null if the parameter doesn't exist and no default value was given.
	 */
	public function testNotExistedParamWithoutDefaultValue() {
		$this->assertNull($this->request->getParam('not_existed_param'));
	}

	/**
	 * Tests if the getParam and setParam methods work
	 */
	public function testRouteParam() {
		$this->request->setParam('test', 'test');
		$this->assertEquals('test', $this->request->getParam('test'));
	}

	/**
	 * Test if the getMethod responses the right value
	 */
	public function testServerMethod() {
		$this->assertSame($this->request->getMethod(), 'post');
	}

	/**
	 * Test if the getTarget responses with the right value
	 */
	public function testTargetName() {
		$this->assertSame($this->request->getTarget(), '/target');
	}

	/**
	 * Test if it figures out if the request is ajax or not
	 */
	public function testIsAjaxRequest() {
		$this->assertFalse($this->request->isAjaxRequest());
		$request = $this->getRequest(array('HTTP_X_REQUESTED_WITH' => 'xmlhttprequest'));
		$this->assertTrue($request->isAjaxRequest());
	}

	/**
	 * Test the parameter sequence
	 */
	public function testParamSequence() {
		$this->assertSame($this->request->get('param', null, 'G'), 'get_param', 'Generic getter fails for GET');
		$this->assertSame($this->request->get('param', null, 'P'), 'post_param', 'Generic getter fails for POST');
		$this->assertSame($this->request->get('param', null, 'C'), 'cookie_param', 'Generic getter fails for COOKIE');
		$this->assertNull($this->request->get('nonexistent'), 'Generic getter fails for nonexistent value');
	}

	public function testGetServer() {
		$this->assertSame('/target', $this->request->getServer('REQUEST_URI'), 'Get from server fails');
		$this->assertNull($this->request->getServer('nonexistent'), 'Get nonexistent key from server fails');
	}

	public function testGetEnv() {
		$this->assertSame('env_value', $this->request->getEnv('envParam'), 'Get from env fails');
		$this->assertNull($this->request->getEnv('nonexistent'), 'Get nonexistent key from env fails');
	}

	protected function getOriginalsFromAcceptedTypes(array $types) {
		$result = array();
		foreach ($types as $type) {
			$result[] = $type['original'];
		}
		return $result;
	}

	protected function checkPreferredValuesAreValid($acceptHeader, array $validArrays) {
		$request = $this->getRequest(array(
			'HTTP_ACCEPT' => $acceptHeader,
		));
		$preferredTypes = $this->getOriginalsFromAcceptedTypes($request->getAcceptedContentTypesByPreference());
		return in_array($preferredTypes, $validArrays);
	}

	public function testGetAcceptedContentTypes() {
		$this->assertSame(array(), $this->request->getAcceptedContentTypes(),
			'Request without an accept setHeader should return an empty array');
		$request = $this->getRequest(array(
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		));
		$expected = array(
			array(
				'type' => 'text',
				'subType' => 'html',
				'mimeType' => 'text/html',
				'original' => 'text/html',
				'params' => array(
					'q' => 1.0,
				),
			),
			array(
				'type' => 'application',
				'subType' => 'xhtml+xml',
				'mimeType' => 'application/xhtml+xml',
				'original' => 'application/xhtml+xml',
				'params' => array(
					'q' => 1.0,
				),
			),
			array(
				'type' => 'application',
				'subType' => 'xml',
				'mimeType' => 'application/xml',
				'original' => 'application/xml;q=0.9',
				'params' => array(
					'q' => 0.9,
				),
			),
			array(
				'type' => '*',
				'subType' => '*',
				'mimeType' => '*/*',
				'original' => '*/*;q=0.8',
				'params' => array(
					'q' => 0.8,
				),
			),
		);
		$this->assertEquals($expected, $request->getAcceptedContentTypes());
		$request = $this->getRequest(array(
			'HTTP_ACCEPT' => ',text/html;q=0.6;level=1;test',
		));
		$expected = array(
			array(
				'type' => 'text',
				'subType' => 'html',
				'mimeType' => 'text/html',
				'original' => 'text/html;q=0.6;level=1;test',
				'params' => array(
					'q' => 0.6,
					'level' => 1
				),
			),
		);
		$this->assertEquals($expected, $request->getAcceptedContentTypes());
	}

	public function testGetAcceptedTypesByPreference() {
		$this->assertTrue($this->checkPreferredValuesAreValid(
			'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', array(
				array('text/html', 'application/xhtml+xml', 'application/xml;q=0.9', '*/*;q=0.8'),
				array('application/xhtml+xml', 'text/html', 'application/xml;q=0.9', '*/*;q=0.8'),
			)
		));
		$this->assertTrue($this->checkPreferredValuesAreValid(
			'text/plain; q=0.5, text/html,
			   text/x-dvi; q=0.8, text/x-c', array(
				array('text/html', 'text/x-c', 'text/x-dvi; q=0.8', 'text/plain; q=0.5'),
				array('text/x-c', 'text/html', 'text/x-dvi; q=0.8', 'text/plain; q=0.5'),
			)
		));
		$this->assertTrue($this->checkPreferredValuesAreValid(
			'text/*, text/html, text/html;level=1, */*', array(
				array('text/html;level=1', 'text/html', 'text/*', '*/*'),
			)
		));
		$this->assertTrue($this->checkPreferredValuesAreValid(
			'text/*;q=0.3, text/html;q=0.7, text/html;level=1,
			   text/html;level=2;q=0.4, */*;q=0.5', array(
				array('text/html;level=1', 'text/html;q=0.7', '*/*;q=0.5', 'text/html;level=2;q=0.4', 'text/*;q=0.3'),
			)
		));
		$this->assertTrue($this->checkPreferredValuesAreValid(
			'text/html,*/*,*/*', array(
				array('text/html', '*/*', '*/*'),
			)
		));
		$this->assertTrue($this->checkPreferredValuesAreValid(
			'text/html,text/*,application/*', array(
				array('text/html', 'text/*', 'application/*'),
				array('text/html', 'application/*', 'text/*'),
				)
		));
	}

	public function testGetContentTypePreferenceValue() {
		$this->assertSame(1.0, $this->request->getContentTypePreferenceValue('image/jpeg'),
			'Request without an accept setHeader should accept any content type with a value of 1.0');
		$request = $this->getRequest(array(
			'HTTP_ACCEPT' => 'text/*;q=0.3, text/html;q=0.7, text/html;level=1,
			   text/html;level=2;q=0.4, */*;q=0.5',
		));
		$this->assertSame(0.0, $request->getContentTypePreferenceValue('invalid'), 'Invalid content type check fails');
		$this->assertSame(1.0, $request->getContentTypePreferenceValue('text/html; level=1'),
			'text/html; level=1 fails');
		$this->assertSame(0.7, $request->getContentTypePreferenceValue('text/html'),
			'text/html fails');
		$this->assertSame(0.3, $request->getContentTypePreferenceValue('text/plain'),
			'text/plain fails');
		$this->assertSame(0.5, $request->getContentTypePreferenceValue('image/jpeg'),
			'image/jpeg fails');
		$this->assertSame(0.4, $request->getContentTypePreferenceValue('text/html; level=2'),
			'text/html; level=2 fails');
		$this->assertSame(0.7, $request->getContentTypePreferenceValue('text/html; level=3'),
			'text/html; level=3 fails');
	}

	public function testCheckIfContentTypeIsPreferred() {
		$this->assertTrue($this->request->checkIfContentTypeIsPreferred('image/jpeg'),
			'Request without an accept setHeader should accept any content type');
		$request = $this->getRequest(array(
			'HTTP_ACCEPT' => 'text/*;q=0.3, text/html;q=0.7, text/html;level=1,
			   text/html;level=2;q=0.4',
		));
		$this->assertFalse($request->checkIfContentTypeIsPreferred('invalid'), 'Invalid content type check fails');
		$this->assertTrue($request->checkIfContentTypeIsPreferred('text/html; level=1'),
			'text/html; level=1 fails');
		$this->assertTrue($request->checkIfContentTypeIsPreferred('text/html'),
			'text/html fails');
		$this->assertTrue($request->checkIfContentTypeIsPreferred('text/plain'),
			'text/plain fails');
		$this->assertFalse($request->checkIfContentTypeIsPreferred('image/jpeg'),
			'image/jpeg fails');
		$this->assertTrue($request->checkIfContentTypeIsPreferred('text/html; level=2'),
			'text/html; level=2 fails');
		$this->assertTrue($request->checkIfContentTypeIsPreferred('text/html; level=3'),
			'text/html; level=3 fails');
	}

	public function testHas() {
		$this->assertFalse($this->request->has('username', 'PC'));
		$this->assertTrue($this->request->has('username', 'G'));

		$this->assertTrue($this->request->hasGet('username'));
		$this->assertFalse($this->request->hasGet('post_param'));

		$this->assertTrue($this->request->hasPost('post_param'));
		$this->assertFalse($this->request->hasPost('cookie'));

		$this->assertTrue($this->request->hasCookie('cookie'));
		$this->assertFalse($this->request->hasCookie('post_param'));
	}

	/**
	 * Tests getting the data for an uploaded file.
	 *
	 * @return void
	 */
	public function testGetFile() {
		$this->assertFalse($this->request->getFile('nonExistent'),
			'FALSE should be returned for a non-existing file upload');

		$this->assertFalse($this->request->getFile('noUploadedFile'),
			'FALSE should be returned if the upload field was specified, but no file was uploaded');

		$result = $this->request->getFile('uploadedFile');

		$this->assertInstanceOf('\YapepBase\DataObject\UploadedFileDo', $result,
			'The result should be an UploadedFileDo for a completed upload');

		$this->assertSame(UPLOAD_ERR_OK, $result->getError(), 'Invalid uploaded file status code');
	}

	/**
	 * Tests checking if there the data for an uploaded file.
	 *
	 * @return void
	 */
	public function testHasFile() {
		$this->assertFalse($this->request->hasFile('nonExistent'),
			'FALSE should be returned for a non-existing file upload');

		$this->assertFalse($this->request->hasFile('noUploadedFile'),
			'FALSE should be returned if the upload is specified, but no file is uploaded');

		$this->assertTrue($this->request->hasFile('uploadedFile'),
			'TRUE should be returned for completed uploads.');
	}
}
