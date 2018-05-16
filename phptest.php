<?php

$first = "aaron";
$last = "lebel";
$email = "alebml33@gmaill.com";

$mailto = "alebel@andrew.cmu.edu";
$subject = "New Email Form Entry for CNT";
$headers = "From: ".$first;
$txt = "New email form user: ".$email;

mail($mailto, $subject, $txt, $headers);
header("Location:index.html");

?>