<?php

if(array_key_exists('ip', $_GET))
        echo file_get_contents("http://".$_GET['ip'].".ipaddress.com");