<div id="lib-container" class="lib-page-section">
	<div id="lib-main-resources">

	<?php
		
		echo getIntroHTML();

		echo getGuidesHTML();

		echo getDiscoveryHTML();
		
		echo getDatabasesHTML();

		echo getCourseMaterialHTML();

	?>

	</div>
	<div id="lib-librarian-profile">

		<?php echo getLibrarianHTML(); ?>

	</div>

</div>

<?php echo getChatHTML(); ?>