<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
auth_logout();
redirect('index.php');
