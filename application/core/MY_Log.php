<?php
/**
 * This file is part of CI Extend (https://github.com/suphm/ci-extend)
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Log extends CI_Log
{
	function __construct() {
		parent::__construct();

		if (ENVIRONMENT === 'development') {
			$ajax = strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ? ' (AJAX)' : null;
			$message = "\n================================================================================================\n";
			$message .= sprintf('Request%s: %s %s', $ajax, $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
			$this->write_log('debug', $message);
		}
	}

	function _format_line($level, $date, $message) {
		return str_pad("[$level]", 7) . " $date --- $message\n";
	}
}
