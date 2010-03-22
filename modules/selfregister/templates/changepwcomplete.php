<?php

$this->data['head'] = '<link rel="stylesheet" href="resources/error.css" type="text/css">';

$this->includeAtTemplateBase('includes/header.php'); ?>

<p><?php echo $this->t('cpwc_para1'); ?></p>

<p><a href="reviewUser.php" ><?php echo $this->t('cpwc_login'); ?></a></p>

<?php $this->includeAtTemplateBase('includes/footer.php'); ?>