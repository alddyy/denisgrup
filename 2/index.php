<?php 
	session_start();
	if(isset($_SESSION['user'])){
		header('Location: ./admin');
	}else header('Location: ./login');
?>