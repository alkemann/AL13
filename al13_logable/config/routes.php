<?php
use \lithium\http\Router;

Router::connect('/logs', array(
	'plugin' => 'al13_logable',	'controller' => 'logs', 'action' => 'index'
));

Router::connect('/logs/page:{:page:[0-9]+}', array(
	'plugin' => 'al13_logable',	'controller' => 'logs', 'action' => 'index', 'page' => 1
));

Router::connect('/logs/limit:{:limit:[0-9]+}', array(
	'plugin' => 'al13_logable',	'controller' => 'logs', 'action' => 'index', 'limit' => 10
));

Router::connect('/logs/page:{:page:[0-9]+}/limit:{:limit:[0-9]+}', array(
	'plugin' => 'al13_logable',	'controller' => 'logs', 'action' => 'index', 'page' => 1, 'limit' => 10
));
?>