<?php 

$this->data['jquery'] = array('version' => '1.6', 'core' => TRUE, 'ui' => TRUE, 'css' => TRUE);
$this->includeAtTemplateBase('includes/header.php'); 



function listMetadata($t, $metadata, $extended = FALSE) {
	
	$now = time();
	echo '<ul>';
	foreach($metadata AS $entry) {
		
		$flag = NULL;
		if (array_key_exists('tags', $entry)) { 
			if(in_array('norway', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/no.png');
			if(in_array('denmark', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/dk.png');
			if(in_array('finland', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/fi.png');
			if(in_array('sweden', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/se.png');
			if(in_array('switzerland', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/ch.png');
			if(in_array('france', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/fr.png');
			if(in_array('poland', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/pl.png');
			if(in_array('germany', $entry['tags'])) $flag = SimpleSAML_Module::getModuleURL('metalisting/flags/de.png');
		}

		
		echo '<li>';
		
		if (isset($flag)) echo(' <img style="display: inline; margin-right: 5px" src="' . $flag . '" alt="Flag" />');
		
		if (array_key_exists('name', $entry)) {
			echo $t->getTranslation(SimpleSAML_Utilities::arrayize($entry['name'], 'en'));
		} else {
			echo $entry['entityid'];
		}
		
		// echo('<pre>'); print_r($entry); echo('</pre>');
		
		if ($extended) {
			if (array_key_exists('expire', $entry)) {
				if ($entry['expire'] < $now) {
					echo('<span style="color: #500; font-weight: bold"> (expired ' . number_format(($now - $entry['expire'])/3600, 1) . ' hours ago)</span>');
				} else {
					echo('<span style="color: #ccc; "> (expires in ' . number_format(($entry['expire'] - $now)/3600, 1) . ' hours)</span>');
				}
			}
		}

		
		if (array_key_exists('url', $entry)) {
			echo(' [ <a href="' . 
				$t->getTranslation(SimpleSAML_Utilities::arrayize($entry['url'], 'en')) .
				'">more</a> ]');
		}
		
		echo '</li>';
	}
	echo '</ul>';
	echo '</fieldset>';
	
}

echo('<h2>Identity Providers</h2>');
listMetadata($this, $this->data['metaentries']['remote']['saml20-idp-remote'], $this->data['extended']);

echo('<h2>Service Providers</h2>');
listMetadata($this, $this->data['metaentries']['remote']['saml20-sp-remote'], $this->data['extended']);




$this->includeAtTemplateBase('includes/footer.php'); 


