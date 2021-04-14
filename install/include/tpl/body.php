<div class="col-10 col-s-10">
    <div class="box mx-auto p-4">
		<div class="row">
		<div class="col-12"><strong>STEP: <?php echo $step; ?></strong>
		<hr /></div>
		
		<?php
            include 'steps/step-'.$step.'.php';
        ?>	

		</div>
	</div>
	
	<div class="box mx-auto p-4">	
		<div class="row">
			<div class="col-6 text-justify p-4">
				<p><a href="<?php echo paginator()['pre']; ?>" class="btn left">BACK</a>
			</div>
			<?php if ($next) {
            echo'
				<div id="next" class="col-6 text-justify p-4" style="display: none;">
			<p><a href="'. paginator()['next'].'" class="btn right">NEXT</a>
			</div>';
        }?>
		</div>
	</div>
 </div>