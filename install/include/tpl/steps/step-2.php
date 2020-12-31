<?php

if(($_POST))
{
	createAdmin();	echo '<div class="col-12"><div class="message blue">Admin created!<span class="message-close" aria-hidden="true">&times;</span></div></div>';
	$next = true;
}

?>

<div class="col-12 text-justify p-4">

	<form method="post" class="mx-auto" style="width: 200px;" action="?step=2">
	  email:<br>
	  <input type="text" name="email" value="<?php if(isset($_POST['email']))echo $_POST['email']; ?>">
	  <br>
	  username:<br>
	  <input type="text" name="username" value="<?php if(isset($_POST['username']))echo $_POST['username']; ?>">
	  	  <br>
	  password:<br>
	  <input type="password" name="password" value="<?php if(isset($_POST['password']))echo $_POST['password']; ?>">
	  	  <br>
	  password validation:<br>
	  <input type="password" name="passwordv" value="<?php if(isset($_POST['passwordv']))echo $_POST['passwordv']; ?>">
	  <?php if(!$next){ echo'
				<br><br>
				<input class="mx-auto btn" type="submit" value="Submit">
			'; }?>
	</form> 


</div>