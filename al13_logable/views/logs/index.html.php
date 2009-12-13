<table>
<tr><td>Time</td><th>Action</th><th>Record</th></tr>
<?php
foreach ($data as $act) {
	echo '<tr><td>';
	echo $act->created;
	echo '</td><td>';
	echo $act->action;
	echo '</td><td>';
	if ($act->action != 'delete') {
		if (isset($act->title)) {
			$title = $act->title;
		} else {
			$title = $act->pk;
		}
		echo $this->html->link($title, array(
			'plugin' => false,
			'controller' => 'pastes',
			'action' => 'view',
			'args' => array($act->pk)
		));
	} else {
		echo $act->pk;
	}
	echo '</td></tr>';
}
?>
</table>
<ul id="actions">
	<li><?php
		if ($total <= $limit || $page == 1) {
			echo '<<-First</li><li><-Previous';
		} else {
			echo $this->html->link('<<-First', array('plugin' => 'al13_logable',
				'controller' => 'logs', 'action' => 'index',
				'page' => 1, 'limit' => $limit
			));
			echo '</li><li>';
			echo $this->html->link('<-Previous', array('plugin' => 'al13_logable',
				'controller' => 'logs', 'action' => 'index',
				 'page' => $page - 1, 'limit' => $limit
			));

		} ?>
	</li>
	<?php

	$p = 0; $count = $total;
	while ($count > 0) {
		$p++; $count -= $limit;
		echo '<li>';
		if ($p == $page) {
			echo '['.$p.']';
		} else {
			echo $this->html->link('['.$p.']', array('plugin' => 'al13_logable',
				'controller' => 'logs', 'action' => 'index',
				'page' => $p, 'limit' => $limit
			));
		}
		echo '</li>';
	}
	?>
	<li><?php
		if ($total <= $limit || $page == $p) {
			echo 'Next-></li><li>Last->>';
		} else {
			echo $this->html->link('Next->', array('plugin' => 'al13_logable',
				'controller' => 'logs', 'action' => 'index',
				'page' => $page + 1, 'limit' => $limit
			));
			echo '</li><li>';
			echo $this->html->link('Last->>', array('plugin' => 'al13_logable',
				'controller' => 'logs', 'action' => 'index',
				'page' => $total, 'limit' => $limit
			));
		}?>
	</li>
</ul>