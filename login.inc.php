<?php

session_start();

if (isset($_POST['submit'])) {

	include_once 'dbh.inc.php';

	$uid = mysqli_real_escape_string($conn, $_POST['uid']);
	$pwd = mysqli_real_escape_string($conn, $_POST['pwd']);

	//Error handling
	//Check for empty inputs

	if (empty($uid) || empty($pwd)){
		header("Location: ../investors.html?login=failed");
		exit();
	} else {
		$sql = "SELECT * FROM users WHERE user_uid='$uid'";
		$result = mysqli_query($conn, $sql);
		$check = mysqli_num_rows($result);

		if($check < 1) {
			header("Location: ../investors.html?login=failed");
			exit();		
		} else {
			if ($row = mysqli_fetch_assoc($result)) {
				//Hash pwd input
				$hashedPwdCheck = password_verify($pwd, $row['user_pwd']);
				if ($hashedPwdCheck == false) {
					header("Location: ../investors.html?login=failed");
					exit();
				} elseif ($hashedPwdCheck == true) {
					//Log user in
					$_SESSION['u_id'] = $row['user_id'];
					$_SESSION['u_first'] = $row['user_first'];
					$_SESSION['u_last'] = $row['user_last'];
					$_SESSION['u_email'] = $row['user_email'];
					$_SESSION['u_uid'] = $row['user_uid'];
					$_SESSION['u_balance'] = $row['user_balance']
					header("Location: ../home.php?login=success");
					exit();
				}
			}
		}
	}

} else {
	header("Location: ../investors.html?login=failed");
	exit();
}