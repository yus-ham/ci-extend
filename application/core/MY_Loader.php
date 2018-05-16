<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
	function __construct() {
		parent::__construct();
		get_instance()->hooks->call_hook('post_loader_init');
		$this->set_view_paths();
	}

	protected function set_view_paths() {
		if (!isset(get_instance()->view_path)) {
			return;
		}

		if ($path = get_instance()->view_path) {
			$this->_ci_view_paths = array(
				$path => true,
				VIEWPATH => true,
			);
		}
	}

}
