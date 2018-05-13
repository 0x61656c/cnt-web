<?php

if (isset($_POST['button'])) {
	$first = $_POST['fname'];
	$last = $_POST['lname'];
	$email = $_POST['email'];

	$mailto = "alebel@andrew.cmu.edu"
	$subject = "New Email Form Entry for CNT"
	$headers = "From: ".$first
	$txt = "New email form user: ".$email

	mail($mailto, $subject, $txt, $headers);
	header("Location: index.php?mailsend");
}