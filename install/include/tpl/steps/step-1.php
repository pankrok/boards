<?php

if($_POST)
{
	createDBcfgFile();
	$status =  checkConnection();
	if($status == $_POST['host'] . ' via TCP/IP')
	{		createDB();
		$next = true;		echo '<div class="col-12"><div class="message blue">DB created! <span class="message-close" aria-hidden="true">&times;</span></div></div>';
	}	else	{		echo '<div class="message red">';		var_dump($status);		echo' <span class="message-close" aria-hidden="true">&times;</span></div>';			}
}

?>

<div class="col-12 text-justify p-4">

	<form method="post" class="mx-auto" style="width: 200px;" action="?step=1">
	  host:<br>
	  <input type="text" name="host" value="<?php if(isset($_POST['host']))echo $_POST['host']; ?>">
	  <br>
	  database:<br>
	  <input type="text" name="database" value="<?php if(isset($_POST['database']))echo $_POST['database']; ?>">
	  	  <br>
	  username:<br>
	  <input type="text" name="username" value="<?php if(isset($_POST['username']))echo $_POST['username']; ?>">
	  	  <br>
	  password:<br>
	  <input type="password" name="password" value="<?php if(isset($_POST['password']))echo $_POST['password']; ?>">
	  	  <br>
	  prefix:<br>
	  <input type="text" name="prefix" value="<?php if(isset($_POST['prefix']))echo $_POST['prefix']; ?>">
	  <?php if(!$next){ echo'
				<br><br>
				<input class="mx-auto btn" type="submit" value="Submit">
			'; }?>
	  
	</form> 


</div>
