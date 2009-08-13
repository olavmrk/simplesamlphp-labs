<?php

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->data['head']  = '<link rel="stylesheet" type="text/css" href="/' . $this->data['baseurlpath'] . 'module.php/metaedit/resources/style.css" />' . "\n";
$this->data['head'] .= '<script type="text/javascript">
$(document).ready(function() {
	$("#tabdiv").tabs();
});
</script>';

$this->includeAtTemplateBase('includes/header.php');


echo('<h1>Authentication</h1>');

if (isset($this->data['userid'])) {
	echo('<p>You are successfuly authenticated as user <tt>' . $this->data['userid'] . '</tt></p>');
} else {
	echo('<p>You are not authenticated. [ <a href="index.php?login=1">login</a> ]</p>');
}


echo('<h1>Your VO Memberships</h1>');

if ($this->data['voreceived']) {
	echo('<p>You are member of the following Virtual Organizations:</p><ul>');
	foreach($this->data['vomemberships'] AS $vo) {
		echo('<li>' . $vo['vo'] . '</li>');
	}

	echo('</ul>');


	foreach($this->data['vomemberships'] AS $vo) {
		echo('<h2>Virtual Organization Attributes for <tt>' . $vo['vo'] . '</tt></h2>');

		$attr = json_decode($vo['attributes'], TRUE);
		echo('<p><table>');
		foreach($attr AS $k => $v) {
			echo('<tr><td style="vertical-align: top">' . $k . '</td><td style="vertical-align: top"><ul>');
			echo '<li>' . join('</li><li>', $v) . '</li>';
			echo('</ul></td></tr>');

		}
		echo('</table></p>');

	}



	echo('<h1>OAuth</h1>');
	echo('<p>To communicate securely with the Virtual Organization Authority, you have obtained the following Access Token Key, with a corresponding secret (not shown):</p><pre>' . $this->data['accessToken'] . '</pre>');
	
} else {
	echo('<p>We have not yet requested Virtual Organization Memberships for you at the Virtual Organization Authority. [ <a href="index.php?step=1">contact Virtual Organization Authority</a> ]</p>');
}




$this->includeAtTemplateBase('includes/footer.php');

