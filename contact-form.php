<?php

if (isset($_POST['button'])) {
	$first = $_POST['firstname'];
	$last = $_POST['lastname'];
	$email = $_POST['email'];

	$mailto = "alebel@andrew.cmu.edu";
	$subject = "New Email Form Entry for CNT";
	$headers = "From: ".$first;
	$txt = "New email form user: ".$email;

	mail($mailto, $subject, $txt, $headers);
	header("Location: index.html?=success");
} else {
	header("Location: index.html?form=failed");
}
