<?php

	set_time_limit( 0 );

	require_once __DIR__ . DIRECTORY_SEPARATOR . 'cj.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'mv.php';

	class MVS extends CJ {

		public $MV;
		public $scraper_id;
		public $where;

		function __construct( $test = false, $profile_id = null, $scraper_id = null ) {

			parent:: __construct();
			$this-> scraper_id = $scraper_id;
			if ( ! is_null($scraper_id) ) {
				$this->where = ' WHERE scraper_id=' . $scraper_id;
			}
			$this-> init( $test, $profile_id );

		}

		function init( $test, $profile_id ) {

			$this-> MV = new MV( array(

				'user_agent'=> $this-> option[ 'user_agent' ]

			) );

			if( $test ) {

				$this-> test();

			} else {

				$result = $this-> mysqli-> query( "SELECT * FROM `" . $this-> prefix . "profiles`" . $this->where . " ORDER BY `profile_id` DESC" );
				$row_cnt = $result-> num_rows;
				$row = $result-> fetch_assoc();

				if( ( $row_cnt == 0 || $row[ 'done' ] ) && is_null( $profile_id ) ) {

					$profiles = array();

					$result = $this-> mysqli-> query( "SELECT * FROM `" . $this-> prefix . "subscriptions`" . $this->where );

					while( $row = $result-> fetch_assoc() ) {

						$profiles[] = array(
							'id'=> $row[ 'id' ],
							'name'=> $row[ 'stage_name' ]
						);

					}

					$done = count( $profiles ) > 0 ? 0 : 1;

					$json = array();

					foreach( $profiles as $profile ) {

						$json[] = array(
							'id'=> $profile[ 'id' ],
							'name'=> $profile[ 'name' ],
							'done'=> 0
						);

					}

					$json = json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
					$json = $this-> mysqli-> real_escape_string( $json );

					$w = is_null( $this->scraper_id ) ? '' : ', `scraper_id`';
					$wh = is_null( $this->scraper_id ) ? '' : ', ' . $this->scraper_id;
					$sql = "INSERT INTO `" . $this-> prefix . "profiles` ( `json`, `done`" . $w . " ) VALUES ( '" . $json . "', " . $done . $wh . " )";

					if( $this-> mysqli-> query( $sql ) === TRUE ) {
						echo "New record created successfully";
					} else {
						echo "Error: " . $sql . "<br>" . $this-> mysqli-> error;
					}

				} else {

					$undone_profiles = array();

					if( is_null( $profile_id ) ) {

						$profiles = json_decode( $row[ 'json' ], true );

						foreach( $profiles as $profile ) {

							if( ! $profile[ 'done' ] ) {
								$undone_profiles[] = array(
									'id'=> $profile[ 'id' ],
									'name'=> $profile[ 'name' ]
								);
							}

						}
					
					} else {

						$result = $this-> mysqli-> query( "SELECT * FROM `" . $this-> prefix . "subscriptions` WHERE `id`=" . $profile_id );
						$row = $result-> fetch_assoc();

						$name = $row[ 'stage_name' ];

						$undone_profiles[] = array(
							'id'=> $profile_id,
							'name'=> $name
						);

					}

					if( count( $undone_profiles ) > 0 ) {

						$shifted_profile = array_shift( $undone_profiles );
						$profile_id = $shifted_profile[ 'id' ];
						$profile_name = $shifted_profile[ 'name' ];

						if( ! isset( $_GET[ 'profile_id' ] ) ) {

							$new_profiles = array();

							foreach( $profiles as $profile ) {

								$profile_done = $profile[ 'id' ] == $profile_id ? 1 : $profile[ 'done' ];

								$new_profiles[] = array(
									'id'=> $profile[ 'id' ],
									'name'=> $profile[ 'name' ],
									'done'=> $profile_done
								);

							}

							$new_json = json_encode( $new_profiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
							$new_json = $this-> mysqli-> real_escape_string( $new_json );

							$sql = "UPDATE `" . $this-> prefix . "profiles` SET `json`='" . $new_json . "' WHERE `profile_id`=" . $row[ 'profile_id' ];

							if( $this-> mysqli-> query( $sql ) === TRUE ) {
								echo "Updated successfully";
							} else {
								echo "Error: " . $sql . "<br>" . $this-> mysqli-> error;
							}
						
						}

						// $posts = $this-> MV-> vids_all( $profile_id, 1 );
						
						// $posts = $this-> MV-> vids_all( $profile_id, 1, false );
						
						$_posts = array();
						
						$not_bundle_posts = $this-> MV-> vids_all( $profile_id, 1, false );
						sleep(1);
						$bundle_posts = $this-> MV-> vids_all( $profile_id, 1, true );
						
						foreach ( $bundle_posts as $_id => $bundle_post ) {
						    $bundle_post['bundle'] = true;
						    $_posts[$_id] = $bundle_post;
						}
						
						foreach ( $not_bundle_posts as $_id => $not_bundle_post ) {
						    $not_bundle_post['bundle'] = false;
						    if ( ! array_key_exists( $_id, $_posts ) ) {
						        $_posts[$_id] = $not_bundle_post;
						    }
						}
						
						$posts = $_posts;

						if( count( $posts ) > 0 ) {

							// <old>

							$query = "";

							$gmdt = gmdate( 'Y-m-d H:i:s' );

							foreach( $posts as $id=> $post ) {

								if( ! $post[ 'price' ][ 'free' ] ) { // $post[ 'free' ]

									// id
									$posted = $gmdt;
									$post_id = $id;
									$title = $this-> mysqli-> real_escape_string( $post[ 'title' ] );
									$duration = $post[ 'duration' ]; // $duration = $post[ 'formattedLength' ];
									$separated = $post[ 'bundle' ]; // $separated = $post[ 'price' ][ 'onSale' ]; // $separated = $post[ 'preview' ][ 'soldSeparate' ];
									// $profile_id
									$profile_name = $this-> mysqli-> real_escape_string( $profile_name );
									$price = array_key_exists( 'discountedPrice', $post[ 'price' ] ) ? $post[ 'price' ][ 'discountedPrice' ] : $post[ 'price' ][ 'regular' ]; // $price = $post[ 'discountPrice' ];
									$link = 'https://www.manyvids.com/Video/' . $post[ 'id' ] . '/' . $post[ 'slug' ]; // $link = 'https://www.manyvids.com' . $post[ 'preview' ][ 'path' ];
									$thumb = $post[ 'thumbnail' ][ 'url' ]; // $thumb = $post[ 'videoThumb' ];
									// photos
									// videos

									if( $separated ) {
									    
									    $separated = 1;
									    
									} else {

										$separated = 0;

									}

									$query.= "INSERT INTO `" . $this-> prefix . "posts` ( `posted`, `post_id`, `title`, `duration`, `separated`, `profile_id`, `profile_name`, `price`, `link`, `thumb` ) VALUES ( '" . $posted . "', " . $post_id . ", '" . $title . "', '" . $duration . "', " . $separated . ", " . $profile_id . ", '" . $profile_name . "', " . $price . ", '" . $link . "', '" . $thumb . "' ); ";

								} else {}

							}

							$this-> mysqli-> multi_query( substr( $query, 0, -2 ) );

							// </old>

							if( isset( $_GET[ 'profile_id' ] ) ) {

								$this-> mysqli-> query( "DELETE t1 FROM " . $this-> prefix . "posts t1 INNER JOIN " . $this-> prefix . "posts t2 WHERE t1.id < t2.id AND t1.post_id = t2.post_id" );

							}

						}

					} else {

						$sql = "UPDATE `" . $this-> prefix . "profiles` SET `done`=1 WHERE `profile_id`=" . $row[ 'profile_id' ];

						if( $this-> mysqli-> query( $sql ) === TRUE ) {
							echo "Updated successfully";
						} else {
							echo "Error: " . $sql . "<br>" . $this-> mysqli-> error;
						}

						$this-> mysqli-> query( "DELETE t1 FROM " . $this-> prefix . "posts t1 INNER JOIN " . $this-> prefix . "posts t2 WHERE t1.id < t2.id AND t1.post_id = t2.post_id" );
						// $this-> mysqli-> query( "TRUNCATE TABLE `profiles`" );

					}

				}

			}

		}

		function test() {

			print '<pre>'; print_r( $this-> MV-> vids( 1005353117 ) );

		}

	}

	$test = isset( $_GET[ 'test' ] ) ? true : false;
	$profile_id = isset( $_GET[ 'profile_id' ] ) ? $_GET[ 'profile_id' ] : null;
	$scraper_id = null; // isset( $_GET[ 'scraper_id' ] ) ? $_GET[ 'scraper_id' ] : null;

	$MVS = new MVS( $test, $profile_id, $scraper_id );

?>
