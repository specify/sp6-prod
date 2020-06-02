<?php

const CSS = 'index';
const BOOTSTRAP = FALSE;

require_once('components/header.php'); ?>


<a href="<?=LINK?>user_stats/">User stats browser</a>
<a href="<?=LINK?>exceptions/">Exceptions</a>
<a href="<?=LINK?>feedback/">Feedback</a>
<a href="<?=LINK?>registrations/">Registrations</a>
<a href="<?=LINK?>tracking/?dmp=1">Tracking</a>


<?php footer();