<?php
/**
 * This file is part of CI Extend (https://github.com/suphm/ci-extend)
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This class enhances CI's hooks.
 * This allows hooks to be registered anywhere and maybe without a config file.
 * The signature of function to hooked may define an additional $data parameter as an array.
 * If any hook returns false the rest registered hooks for the hook point will not be called.
 * example:
 * ~~~php
 * $CI->hooks->append('post_register', functuion($data) {
 *		$from = 'admin@webmaster.com';
 *		$email_body = 'some contents';
 *		$this->send_email($from, $data->user_email, $email_body);
 * });
 * 
 * # call hooks at where after user registration
 * $CI->hooks->call_hook('post_register');
 * ~~~
 *
 * @todo: Implement https://github.com/php-fig/fig-standards/pull/834#issuecomment-308317798
 */
class MY_Hooks extends CI_Hooks
{
	protected $_hooks;

	public function __construct() {
		parent::__construct();
		$this->_hooks = $this->_hooks ?: array();
		$this->_add_preserved_hooks();
		unset($this->hooks);
	}

	/**
	 * Prepends hook to the hook point.
	 * The signature of the function to hooked may defined with additional $data parameter.
	 * And it's argument will be passed by [[call_hook()]] as an OBJECT and the hook may alter that OBJECT then.
	 * 
	 * @param string	$name	Hook point name
	 * @param callable	$hook	callable or function name to prepended
	 * @see call_hook()
	 */
	public function prepend($name, $hook) {
		$this->_ensure_point($name);
		array_unshift($this->_hooks[$name], $hook);
	}

	/**
	 * Appends hook to the hook point.
	 * The signature of the function to hooked may defined with additional $data parameter.
	 * And it's argument will be passed by [[call_hook()]] as an OBJECT and the hook may alter that OBJECT then.
	 * 
	 * @param string	$name	Hook point name
	 * @param callable	$hook	callable or function name to appended
	 * @see call_hook()
	 */
	public function append($name, $hook) {
		$this->_ensure_point($name);
		$this->_hooks[$name][] = $hook;
	}

	private function _ensure_point($name) {
		if (!isset($this->_hooks[$name])) {
			$this->_hooks[$name] = array();
		} elseif (!is_array($this->_hooks[$name]) OR isset($this->_hooks[$name]['function'])) {
			$this->_hooks[$name] = array($this->_hooks[$name]);
		}
	}

	/**
	 * Remove specified hook from hook point.
	 * 
	 * @param string	$name	Hook point name
	 * @param callable	$hook	Hook which to removed
	 */
	public function remove($name, $hook) {
		if (isset($this->_hooks[$name])) {
			while (FALSE !== $index = array_search($hook, $this->_hooks[$name])) {
				unset($this->_hooks[$name][$index]);
			}
		}
	}

	/**
	 * Remove hook point
	 * 
	 * @param string $name Hook point name
	 */
	public function remove_point($name) {
		unset($this->_hooks[$name]);
	}

	/**
	 * Call all hooks for a hook point
	 * The hooks processed when these conditions are meet
	 * - hook config is enabled in application config
	 * - hook point must registered
	 * - hook point not in progress
	 * This function overrides parent implementation.
	 * Not like CI's hook, You can pass an array or object as second argument.
	 * It will converted to an object and then passed to hook which may alter that OBJECT then.
	 * This function can returns boolean value if an empty $data passed. The value is FALSE when last executed hook return FALSE otherwise it is TRUE.
	 * and also can returns an array contain two items.
	 * first item with key 'data' which it is $data which may be altered by hook.
	 * another item is boolean value with key 'stopped'. The value is TRUE when last executed hook return FALSE otherwise it is FALSE.
	 *
	 * @param	string	$name	Hook point name
	 * @param   mixed	$data	data to be passed to hook
	 * @return void|boolean|array
	 */
	public function call_hook($name = '', $data = null) {
		log_message('debug', PHP_EOL . '======= Run hook: ' . $name . ' =======');
		$result['stopped'] = false;

		switch(true) {
			case!$this->enabled:
			case!isset($this->_hooks[$name]):
			case $this->_in_progress[$name]:
				return;
		}

		is_array($data) && $data = (object)$data;
		$this->_in_progress[$name] = true;

		foreach ($this->_hooks[$name] as $hook) {
			if (false === $this->_run_hook($hook, $data)) {
				$result['stopped'] = true;
				break;
			}
		}

		$this->_in_progress = false;
		$result['data'] = $data;
		return $data ? $result : !$result['stopped'];
	}

	/**
	 * @internal
	 * @param callable $hook
	 * @param object $data
	 * @return mixed
	 */
	protected function _run_hook($hook, $data = null) {
		if (is_array($hook) && isset($hook['function'])) {
			if (isset($hook['filename']) && is_file($hook['filename'])) {
				require_once $hook['filename'];
			}

			$hook = isset($hook['class']) ? array($hook['class'], $hook['function']) : $hook['function'];
		}

		return call_user_func($hook, $data);
	}

	protected function _add_preserved_hooks() {
		$this->append('display_override', function () {
			return false; // no override
		});

		/** The rest are just for debugging */
		if (ENVIRONMENT !== 'development')
			return;

		$this->prepend('cache_override', function () {
			log_message('debug', "\n======= URI: " . $_SERVER['REQUEST_METHOD'] . ' /' . load_class('URI', 'core')->uri_string . ' ' . @$_SERVER['HTTP_X_REQUESTED_WITH'] . ' =======');
		});

		$this->prepend('post_controller_constructor', function () {
			log_message('debug', PHP_EOL . '======= $_SESSION ' . session_id() . ': ' . print_r($_SESSION, 1));
			log_message('debug', PHP_EOL . '======= $_REQUEST: ' . print_r($_REQUEST, 1));
		});

		$this->append('post_controller_constructor', function () {
			log_message('debug', PHP_EOL . '======= Run action: ' . implode('/', load_class('URI', 'core')->rsegments) . ' =======');
		});
	}
}
