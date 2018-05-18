# CI-Extend
Extended CodeIgniter Core and Libraries
Compatible with CodeIgniter 3.x

# Features
## Better output in logged messages
## Added new Hook point
- 'post_loader_init' This hook point called after call to CI_Loader constructor.

## Customized Hooks
You can register or remove hook without adding in ```hooks.php``` file.
~~~php
// Prepends new hook to 'post_controller' hook point
$CI->hooks->prepend('post_controller', function() {
	do_some_thing();
});

// Appends new hook
$CI->hooks->append(
	'post_user_register', // you can also add new hook point too
	function($data) { // new parameter $data will provided by call_hook()
		send_register_nortification();

		// All hook point can have multiple hook. These all hooks will called.
		// but you may stop next hook call by return false.
		return false;
	}
);

// Remove hook
$CI->hooks->remove('hook_point', $hook_to_removed);

// Remove all hooks
$CI->hooks->remove_all('hook_point');

// call to 'new_hook_point' hook with passed some data
$CI->hooks->call_hook('new_hook_point', array('some data'));
~~~

## Role Based Access Control
You can enable user access control to your CodeIgniter application based user role such ```Admin``` ```Author``` etc.
This librariy requires database migration. Follow this steps to setup RBAC.
- Add new tables on your database first.
You may just import the provided SQL file ```rbac-scheme.sql```.
- Add new field ```role``` with type ```int``` to your ```user``` table if not exist.
You can run this sql command ```ALTER TABLE `user` ADD `role` int NULL;```
- Add new ```post_controller_constructor``` hook
Copy this code and paste to your ```hook.php```
~~~php
$hook['post_controller_constructor'][] = function() {
	// when user logged in, you must set user role to $_SESION
	$config = array('role' => $_SESSION['role']);
	CI_Controller::get_instance()->load->library('rbac', $config);
	User_access_control::run();
};
~~~
The ```user_can()``` function provided to test whether user can do spesified permission or user has a role.
~~~php
user_can('controller/method');	// user can run a method in controller.
user_can('controller/*');		// all methods in controller
user_can('*');					// all actions
user_can('@admin');				// Role test, user can do 'admin' permissions
~~~
Example usage: You can control the button display for specified user role in your view.
~~~html
<?php if (user_can('@admin'): ?>
	<button>Delete</button>
<?php endif ?>
~~~


# License: BSD License 2.0

