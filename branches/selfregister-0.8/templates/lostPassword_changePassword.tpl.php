<?php

$this->data['head'] = '<link rel="stylesheet" href="resources/umesg.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<?php if(isset($this->data['error'])){ ?>
	  <div class="error"><?php echo $this->data['error']; ?></div>
<?php }?>
<h1><?php echo $this->t('lpw_head'); ?></h1>
<p><?php echo $this->t('lpw_reg_para1', array('%UID%' => $this->data['uid']) ); ?></p>
<?php print $this->data['formHtml']; ?>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>