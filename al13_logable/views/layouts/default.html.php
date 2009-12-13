<?php
/**
 * Lithium: the most rad php framework
 * Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 *
 * Licensed under The BSD License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
?>
<!doctype html>
<html>
<head>
	<?php echo $this->html->charset(); ?>
	<title>Log</title>	
	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.0r4/build/reset-fonts-grids/reset-fonts-grids.css"> 
	<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.8.0r4/build/base/base-min.css">
	<style>
#actions li {
 display: inline;
 list-style: none;
 padding: 0 5px;
}	
	</style>
</head>
<body>
	<div id="container">
		<div id="header">
		</div>
		<div id="content">
			<?php echo $this->content; ?>
		</div>
		<div id="footer">
			@2009 Union of Rad
		</div>
	</div>
</body>
</html>