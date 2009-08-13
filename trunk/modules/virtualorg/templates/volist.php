<?php

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/metaedit/resources/style.css" />' . "\n";
// $this->data['head'] .= '<script type="text/javascript">
// $(document).ready(function() {
// 	$("#tabdiv").tabs();
// });
// </script>';

$this->includeAtTemplateBase('includes/header.php');


echo('<h1>Virtual Organizations</h1>');

echo('<p>Here you can create virtual organizations. You are successfully logged in as ' . $this->data['userid'] . '</p>');

echo('<h2>Organizations where you are the owner</h2>');
echo('<table class="metalist" style="width: 100%">');
$i = 0; $rows = array('odd', 'even');
foreach($this->data['volist'] AS $vo ) {
	$i++; 
	echo('<tr class="' . $rows[$i % 2] . '">
		<td><tt>' . $vo['id'] . '</tt></td>
		<td>' . $vo['name'] . '</td>
		<td>
			<a href="vo.php?id=' . urlencode($vo['id']) . '">edit</a>
			<a href="index.php?delete=' . urlencode($vo['id']) . '">delete</a>
		</td></tr>');
}
if ($i == 0) {
	echo('<tr><td colspan="3">No organisations registered</td></tr>');
}
echo('</table>');


echo('<h2>Add new organization</h2>');
echo('
<form action="vo.php" method="post">
	<input type="hidden" name="type" value="createnew" />
	<table style="width: 90%">
		<tr>
			<td>Identifier</td>
			<td><input type="text" name="id" size="20" /> 
				<span style="color: #999">Unique identifier [a-z] and [0-9]</span></td>
		</tr>
		<tr>
			<td>Name</td>
			<td><input type="text" name="name" size="40" /> 
				<span style="color: #999">Human readable name</span></td>
		</tr>
		<tr>
			<td>Description</td>
			<td><textarea rows="4" style="width: 100%" name="descr"></textarea></td>
		</tr>
	</table>
	<p><input type="submit" name="submit" value="Create new organization" />
</form>

');

$this->includeAtTemplateBase('includes/footer.php');

