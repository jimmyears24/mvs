		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/jquery-3.6.2.min.js" integrity="sha256-2krYZKh//PcchRtd+H+VyyQoZ/e3EcrkxhM8ycwASPA=" crossorigin="anonymous"></script>
		<script>
			$( document ) . ready( function() {
				$( document ) . on( 'click', '.redo', function() {
					var profile_id = $( this ) . attr( 'data-profile-id' );
					$.ajax( {
						url: "http://<?php echo $_SERVER['HTTP_HOST']; ?>/mvs_cj.php?profile_id=" + profile_id,
						beforeSend: function() {
							$( '#spinner-' + profile_id ) . removeClass( 'd-none' );
							$( '#redo-' + profile_id ) . addClass( 'd-none' );
						}
					} ) . done( function( data ) {
						$( '#spinner-' + profile_id ) . addClass( 'd-none' );
						$( '#redo-' + profile_id ) . removeClass( 'd-none' );
					});
				} );
			} );
		</script>
	</body>

</html>
