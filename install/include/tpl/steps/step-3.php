<?php

if(($_POST))
{
createCFG();echo '<div class="col-12"><div class="message blue">CFG created! <span class="message-close" aria-hidden="true">&times;</span></div></div>';
	$next = true;
}

?>

<div class="col-12 text-justify p-4">

	<form method="post" class="mx-auto" style="width: 200px;" action="?step=3">
	<input type="hidden" name="post" value="1">
	  Nazwa forum<br>
	  <input type="text" name="main_page_name" value="<?php if(isset($_POST['main_page_name']))echo $_POST['main_page_name']; ?>">
	  <br>
	  adres panelu admina:<br>
	  <input type="text" name="admin" value="<?php if(isset($_POST['admin']))echo $_POST['admin']; ?>">
	 <small>zostaw puste aby logować się domena.pl/acp</small>
	  <?php if(!$next){ echo'
				<br><br>
				<input class="mx-auto btn" type="submit" value="Submit">
			'; }?>
	</form> 


</div>