<?php

	require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php';

	$mysqli = new mysqli( $hostname, $username, $password, $database );

	$mysqli-> set_charset( $charset );

	$alert = '';

	if( count( $_POST ) > 0 ) {

		if( isset( $_POST[ 'subscription_id' ] ) ) { // DELETE

			if( $mysqli-> query( "DELETE FROM `" . $prefix . "subscriptions` WHERE `subscription_id`=" . $_POST[ 'subscription_id' ] ) === TRUE ) {

				$class = 'alert-success';

				$text = 'Subscription deleted successfully.';

			} else {

				$class = 'alert-danger';

				$text = 'Error deleting subscription: ' . $mysqli-> error;

			}

		}

		if( isset( $_POST[ 'uri' ] ) ) { // ADD

			$uri = $_POST[ 'uri' ];

			if( strlen( $uri ) > 0 ) {

				if( preg_match( '/^(?<id>.*?)\/(?<uri>.*?)$/', $uri, $matches ) ) { // ?
					$url = 'https://www.manyvids.com/Profile/' . $uri . '/Store/Videos/';
					$id = $matches[ 'id' ];
					$uri = $matches[ 'uri' ];

					if( ( $content = @file_get_contents( $url ) )===FALSE ) {

						$class = 'alert-danger';

						$error = error_get_last();
						$text = 'ManyVids error(1): ' . $error[ 'message' ];

					} else {

						if( preg_match( '/twitter\:title\" content\=\"(?<stage_name>.*?) \- Store/', $content, $matches ) ) {

							$stage_name = $matches[ 'stage_name' ];

							$result = $mysqli-> query( "SELECT * FROM `" . $prefix . "subscriptions` WHERE `id`=" . $id );

							if( $result-> num_rows > 0 ) {

								$class = 'alert-danger';

								$text = 'Exists.';

							} else {

								if( $mysqli-> query( "INSERT INTO `" . $prefix . "subscriptions` ( `id`, `stage_name`, `url`, `uri` ) VALUES ( " . $id . ", '" . $stage_name . "', '" . $url . "', '" . $uri . "' )" ) === TRUE ) {

									$class = 'alert-success';

									$text = 'Subscription added successfully.';

								} else {

									$class = 'alert-danger';

									$text = 'MySQL error: ' . $mysqli-> error;

								}

							}

						} else {

							$class = 'alert-danger';

							$text = 'ManyVids error(2).';

						}

					}

				} else {

					$class = 'alert-danger';

					$text = 'Wrong format: ' . $_POST[ 'uri' ];

				}

			} else {

				$class = 'alert-danger';

				$text = 'Empty!';

			}

		}

		$alert = '<div class="alert ' . $class . ' m-2 mb-0" role="alert">' . $text . '</div>';

	}

	$result = $mysqli-> query( "SELECT * FROM `" . $prefix . "subscriptions` ORDER BY `stage_name` ASC" );

	$subscriptions_html = '<table class="table table-dark table-striped">';
	$subscriptions_html.= '<tr><th></th><th>ID</th><th>Name (Stage Name)</th><th>URL</th><th></th></tr>';
	$subscriptions_html.= '<tr><form method="post"><td colspan="3"></td><td><div class="input-group"><span class="input-group-text" id="basic-addon3">https://www.manyvids.com/Profile/</span><input name="uri" type="text" class="form-control" id="basic-url" aria-describedby="basic-addon3" placeholder="683542/lilcanadiangirl"><span class="input-group-text" id="basic-addon3">/Store/Videos/</span></div></td><td class="text-end"><button class="btn btn-primary" type="submit">Add</button></td></form></tr>';

	$i = 1;

	while( $subscription = $result-> fetch_assoc() ) {

		$subscriptions_html.= '<tr><form method="post"><td class="align-middle">' . $i . '.</td><td class="align-middle">' . $subscription[ 'id' ] . '</td><td class="align-middle">' . $subscription[ 'stage_name' ] . ' <div style="width: 1em; height: 1em;" id="spinner-' . $subscription[ 'id' ] . '" class="spinner-border d-none" role="status"><span class="visually-hidden">Loading...</span></div><i style="cursor: pointer;" id="redo-' . $subscription[ 'id' ] . '" data-profile-id="' . $subscription[ 'id' ] . '" class="redo bi bi-arrow-clockwise"></i></td><td><div class="input-group"><span class="w-100 input-group-text" id="basic-addon2">' . $subscription[ 'url' ] . '</span></div></td><td class="text-end"><input type="hidden" name="subscription_id" value="' . $subscription[ 'subscription_id' ] . '" /><button class="btn btn-secondary" type="submit"><i class="bi bi-trash"></i></button></td></form></tr>';

		$i++;

	}

	$subscriptions_html.= '</table>';

	$result = $mysqli-> query( "SELECT * FROM `" . $prefix . "profiles` ORDER BY `profile_id` DESC" );

	$row = $result-> fetch_assoc();

	$json = $row[ 'json' ];

	$profiles = json_decode( $json, true );

	$profiles_html = '<h1 class="text-center text-light m-2 mb-0">Current Scraper Session</h1><div class="text-center"><ul class="d-inline-block list-group p-2">';

	$id = 0;
	$i = 1;

	foreach( $profiles as $profile ) {

		$class = $profile[ 'done' ] > 0 ? ' list-group-item-success' : ' list-group-item-light';

		if( $profile[ 'done' ] == 1 && $profiles[ ($id+1) ][ 'done' ] == 0 ) {
			$class = ' list-group-item-warning';
		}

		$profiles_html.= '<li class="text-start list-group-item' . $class . '">' . $i . '/' . count( $profiles ) . '. ' . $profile[ 'name' ] . '</li>';

		$id++;
		$i++;

	}

	$profiles_html.= '</ul></div>';

?>
<!DOCTYPE html>
<html lang="en">

	<head>

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Admin</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" />

		<style>

			body { background-color: black }

		</style>

	</head>

	<body>
