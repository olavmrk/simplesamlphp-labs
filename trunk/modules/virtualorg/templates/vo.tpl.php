<?php

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/metaedit/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
	$("#tabdiv").tabs();
});
</script>';

$this->includeAtTemplateBase('includes/header.php');


echo('<h1>Virtual Organization: ' . $this->data['vometa']['name'] . '</h1>');
echo('
<form action="vo.php" method="post">
	<input type="hidden" name="type" value="edit" />
	<table style="width: 90%">
		<tr>
			<td>Identifier</td>
			<td><input type="text" name="id" size="20" value="' . $this->data['vometa']['id'] . '"/> 
				<span style="color: #999">Unique identifier [a-z] and [0-9]</span></td>
		</tr>
		<tr>
			<td>Name</td>
			<td><input type="text" name="name" size="40"  value="' . $this->data['vometa']['name'] . '" /> 
				<span style="color: #999">Human readable name</span></td>
		</tr>
		<tr>
			<td>Description</td>
			<td><textarea rows="4" style="width: 100%" name="descr">' . $this->data['vometa']['descr'] . '</textarea></td>
		</tr>
	</table>
	<p><input type="submit" name="submit" value="Save changes" />
</form>

');

echo('<h2>Registering new members</h2>');

echo('<p>Give the following secret URL to potenial members of this organization, and they will be automatically signed up. Later you can edit their attributes.</p><p><input style="width: 100%" type="text" name="_" value="' . $this->data['registerURL'] . '" /></p>');

echo('<h2>Members</h2>');
echo('<table class="metalist" style="width: 100%">
<tr><td>User ID</td><td>Name</td><td>E-mail</td><td>Operations</td></tr>
');
$i = 0; $rows = array('odd', 'even');
foreach($this->data['vomembers'] AS $member ) {
	$att = json_decode($member['attributes'], TRUE);
	$i++; 
	echo('<tr class="' . $rows[$i % 2] . '">
		<td><tt>' . $member['userid'] . '</tt></td>
		<td>' . $att['displayName'][0] . '</td>
		<td><tt>' . $att['mail'][0] . '</tt></td>
		<td>
			<a href="attributes.php?id=' . 
				urlencode($this->data['vometa']['id']) . '&amp;userid=' . $member['userid'] . '">attributes</a>
			<a href="index.php?delete=' . urlencode($this->data['vometa']['id']) . '">unregister</a>
		</td></tr>');
}
if ($i == 0) {
	echo('<tr><td colspan="3">No organisations registered</td></tr>');
}
echo('</table>');



echo('<p style="float: right"><a href="index.php">Return to organization listing <strong>without saving...</strong></a></p>');

$this->includeAtTemplateBase('includes/footer.php');

