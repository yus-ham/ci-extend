<?php

/**
 * @TODO: Implement https://github.com/php-fig/fig-standards/pull/834#issuecomment-308317798
 * 
 * This class allow to register hook like CI hooks.
 * The difference is the hooks can be registered anywhere.
 * The signature of function to hooked may defined with additional $data parameter as an array.
 * If hook returns false the rest registered hooks will not be called
 */
class MY_Hooks extends CI_Hooks
{
	/**
	 * Prepends hook to the hook list.
	 * 
	 * @param string	$name	Hook name
	 * @param callable	$hook	callable or function name to prepended
	 */
	public function prepend($name, $hook) {
		$this->register($name);
		array_unshift($this->hooks[$name], $hook);
	}

	/**
	 * Appends hook to the hook list.
	 * 
	 * @param string	$name	Hook name
	 * @param callable	$hook	callable or function name to appended
	 */
	public function append($name, $hook) {
		$this->register($name);
		$this->hooks[$name][] = $hook;
	}

	private function register($name) {
		if (!isset($this->hooks[$name])) {
			$this->hooks[$name] = array();
		}
	}

	/**
	 * Remove hook.
	 * 
	 * @param string	$name	Hook name
	 * @param callable	$hook	Hook which to removed
	 */
	public function remove($name, $hook) {
		if (isset($this->hooks[$name])) {
			$index = array_search($hook, $this->hooks[$name]);
			unset($this->hooks[$name][$index]);
		}
	}

	/**
	 * Remove all hooks
	 * 
	 * @param string $name Hook name
	 */
	public function remove_all($name) {
		unset($this->hooks[$name]);
	}

	/**
	 * Call all hooks for a hook point
	 * If a hook returns false the rest hooks will not be called
	 * The hooks processed when these conditions are meet
	 * - hook config is enabled in application config
	 * - hook point must registered
	 * - hook point not in progress
	 *
	 * @param	string	$which	Hook name
	 * @param   array	$data	data to be passed to hook
	 * @return	void
	 */
	public function call_hook($which = '', $data = null) {
		log_message('debug', '======= Run hook: ' . $which . ' =======');

		switch(true) {
			case!$this->enabled:
			case!isset($this->hooks[$which]):
			case $this->_in_progress:
				return;
		}

		$this->_in_progress = true;
		$hooks = $this->hooks[$which];

		if (is_array($hooks) && !isset($hooks['function'])) {
			foreach ($hooks as $hook) {
				if (false === $this->_run_hook($hook, $data)) {
					break;
				}
			}
		} else {
			$this->_run_hook($hook);
		}

		$this->_in_progress = false;
	}

	protected function _run_hook($hook, $data) {
		if (is_callable($hook)) {
			return call_user_func($hook, $data);
		} elseif (!is_array($hook)) {
			return;
		}

		if (!isset($hook['function'])) {
			return;
		}
		
		if (isset($hook['filename']) && file_exists($hook['filename'])) {
			require_once $hook['filename'];
		}

		if (is_callable($hook['function'])) {
			return call_user_func($hook, $data);
		}
	}

}
