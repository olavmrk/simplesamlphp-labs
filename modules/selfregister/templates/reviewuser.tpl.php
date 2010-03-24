<?php

$this->data['head'] = '<link rel="stylesheet" href="resources/umesg.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])){ ?>
	  <div class="error"><?php echo $this->data['error']; ?></div>
<?php }?>
<?php if(isset($this->data['userMessage'])){ ?>
	<div class="umesg"><?php echo $this->t($this->data['userMessage']); ?></div>
<?php }?>

<h1><?php echo $this->t('review_head'); ?></h1>
<p>
<?php echo $this->t('review_intro', array('%UID%' => $this->data['uid']) ); ?>
<ul>
<li><a href="reviewUser.php?logout=true">Logout</a></li>
<li><a href="changePassword.php"><?php echo $this->t('link_changepw'); ?></a></li>
</ul>
</p>

<?php if(isset($this->data['userMessage'])){ ?>
	  <div class="mesg"><?php echo $this->data['userMessage']; ?></div>
<?php }?>
<?php print $this->data['formHtml']; ?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>