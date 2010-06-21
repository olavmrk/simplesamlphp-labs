<?php

$this->data['head'] = '<link rel="stylesheet" href="resources/umesg.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])){ ?>
	<div class="error"><?php echo $this->data['error']; ?></div>
<?php }?>
<?php if(isset($this->data['userMessage'])){ ?>
	<div class="umesg"><?php echo $this->t($this->data['userMessage']); ?></div>
<?php }?>

<h1><?php echo $this->t('cpw_head'); ?></h1>
<p><?php echo $this->t('cpw_para1', array('%UID%' => $this->data['uid']) ); ?>
<ul>
<li><a href="changePassword.php?logout=true">Logout</a></li>
<li><a href="reviewUser.php"><?php echo $this->t('link_review'); ?></a></li>
</ul></p>

<?php echo $this->data['formHtml']; ?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>