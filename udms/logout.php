<?php require 'config.php'; if(me()) log_act('User logged out',null,me()['username'].' logged out');
$_SESSION=[]; session_destroy(); session_start(); flash('success','You have been logged out.'); redirect('login.php');
