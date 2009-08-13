<?php

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/metaedit/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
	$("#tabdiv").tabs();
});
</script>';

$this->includeAtTemplateBase('includes/header.php');


$fixedAttributes = array(
	'displayName' => 'Display Name',
	'mail' => 'E-Mail',
	'o' => 'Organization',
);
$moreAttributes = array(
	'voaffiliation' => 'VO Affiliations',
	'entitlements' => 'Entitlements',
);

echo('<h1>Edit user VO Attributes</h1>');
echo('
<form action="attributes.php" method="post">
	<input type="hidden" name="type" value="edit" />
	<input type="hidden" name="id" value="' . $this->data['id'] . '" />
	<input type="hidden" name="userid" value="' . $this->data['edituser'] . '" />
	<table style="width: 90%">');
	
foreach($fixedAttributes AS $k => $t) {
	echo('<tr>
		<td>' . $t . '</td>
		<td>' . join(',', $this->data['voattributes'][$k]) . '</td>
	</tr>');
}
foreach($moreAttributes AS $k => $t) {
	echo('<tr>
		<td>' . $t . '</td>
		<td><input type="text" name="attribute_' . $k . '" size="50" value="' . 
		(isset($this->data['voattributes'][$k]) ? join(',', $this->data['voattributes'][$k]) : '')  . 
		'"/></td>
	</tr>');
}
	
echo('</table>
	<p><input type="submit" name="submit" value="Save changes" />
</form>
');



echo('<p style="float: right"><a href="index.php">Return to organization listing <strong>without saving...</strong></a></p>');

$this->includeAtTemplateBase('includes/footer.php');

