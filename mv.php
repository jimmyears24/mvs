<?php

	class MV {

		public $apikey = NULL; // string
		public $cookie_data = NULL; // string
		public $userId = NULL; // string
		public $user_agent = NULL; // string

		public $cookie_path = NULL; // string
		public $cookie_file = NULL; // string
		public $mvtoken = NULL; // string

		public $accept = 'application/json, text/plain, */*';
		public $url = 'https://www.manyvids.com';

		function __construct( $data = NULL ) {

			if( ! is_null( $data ) && is_array( $data ) ) {

				foreach( array( 'apikey', 'cookie_data', 'userId', 'user_agent' ) as $var ) {

					if( array_key_exists( $var, $data ) ) {

						$this-> $var = $data [ $var ];

					} else {}

				}

			} else {}

			$this-> browser(); // $this-> mvtoken

		}

		function following( $page = 1 ) {

			//     user_id = $MV-> following() [ $i ] [ 'user' ] [ 'id' ]
			// profile_url = $MV-> following() [ $i ] [ 'user' ] [ 'profileUrl' ]
			//        name = $MV-> following() [ $i ] [ 'user' ] [ 'stageName' ]

			$response = $this-> browser( '/Feed/api/follow/fetch/following?userId=' . $this-> userId . '&filter=-1&sort=-1&page=' . $page . '&limit=25&apikey=' . $this-> apikey );

			if( is_array( $response ) && array_key_exists( 'data', $response ) && count( $response [ 'data' ] ) > 0 ) {

				return $response [ 'data' ];

			} else {

				return array();

			}

		}

		function following_all( $sleep = 2 ) {

			$following_all = array();

			$page = 1;

			$num_following = 1;

			while( $num_following > 0 ) {

				$following = $this-> following( $page );

				foreach( $following as $data ) {

					if( ! array_key_exists( $data [ 'user' ] [ 'id' ], $following_all ) ) {

						$following_all [ $data [ 'user' ] [ 'id' ] ] = $data;

					} else {}

				}

				$num_following = count( $following );

				$page++;

				sleep( $sleep );

			}

			return $following_all;

		}

		function vids( $profile_id = null, $page = 1, $bundle = false ) {

			// $page = $page - 1;

			// $offset = $page * 30;

            // https://www.manyvids.com/bff/store/videos/1001356544/?page=2
            // https://www.manyvids.com/bff/store/videos/1001356544/?bundle=true&page=4

			// return $this-> browser( '/api/model/' . $profile_id . '/videos?category=all&offset=' . $offset . '&sort=1&limit=30&mvtoken=' . $this-> mvtoken );
			
			$_bundle = $bundle ? 'bundle=true&' : '';
			
			return $this-> browser( '/bff/store/videos/' . $profile_id . '/?' . $_bundle . 'page=' . $page );

		}

		function vids_all( $profile_id = null, $sleep = 2, $bundle = false ) {

			$vids_all = array();

			$page = 1;
			
			$have_next_page = true;
			
			while ( $have_next_page ) {
			    
			    $vids = $this-> vids( $profile_id, $page, $bundle );
			    
			    foreach( $vids [ 'data' ] as $vid ) {

					if( ! array_key_exists( $vid [ 'id' ], $vids_all ) ) {

						$vids_all [ $vid [ 'id' ] ] = $vid;

					} else {}

				}
			    
			    $have_next_page = array_key_exists( 'nextPage', $vids['pagination'] ) && $vids['pagination']['nextPage'] > 0 ? true : false;
			    
			    if ( $have_next_page ) {
			        
			        $page++;
			        
			        sleep( $sleep );
			        
			    }
			    
			}
			
			/*

			$newOffset = 0;
			$totalCount = 1;

			while( $newOffset != $totalCount ) {

				$vids = $this-> vids( $profile_id, $page ); // var_dump($vids);

				foreach( $vids [ 'result' ] [ 'content' ] [ 'items' ] as $vid ) {

					if( ! array_key_exists( $vid [ 'id' ], $vids_all ) ) {

						$vids_all [ $vid [ 'id' ] ] = $vid;

					} else {}

				}

				$newOffset = $vids [ 'result' ] [ 'content' ] [ 'newOffset' ];
				$totalCount = $vids [ 'result' ] [ 'content' ] [ 'totalCount' ];

				$page++;

				sleep( $sleep );

			}
			
			*/

			return $vids_all;

		}

		function browser( $uri = '' ) {

			$request_headers = array(

				'cookie'=> $this-> cookie_data,
				'user-agent'=> $this-> user_agent,
				'accept'=> $this-> accept

			);

			$ex = new Exception();
			$trace = $ex-> getTrace();

			$this-> cookie_path = __DIR__ . DIRECTORY_SEPARATOR . 'cookie' . DIRECTORY_SEPARATOR;

			if( file_exists( $this-> cookie_path ) && is_dir( $this-> cookie_path ) ) {} else {

				mkdir( $this-> cookie_path );

			}

			$this-> cookie_file = $this-> userId . '_' . $trace [ 1 ] [ 'function' ];

			if( $trace [ 1 ] [ 'function' ] == '__construct' ) {

				$this-> cookie_file = $this-> userId . '_vids';
				$uri = '/Vids/';

			} else {}

			if( $trace [ 1 ] [ 'function' ] == '__construct' ) {

				unset( $request_headers [ 'cookie' ] );
				unset( $request_headers [ 'accept' ] );

			} else {}

			if( $trace [ 1 ] [ 'function' ] == 'vids' ) {

				unset( $request_headers [ 'cookie' ] );
				
				$request_headers [ 'cache-control' ] = 'no-cache';
				$request_headers [ 'pragma' ] = 'no-cache';
				$request_headers [ 'x-requested-with' ] = 'XMLHttpRequest';

			} else {}

			$httpheader = array();

			foreach( $request_headers as $key=> $value ) {

				$httpheader [] = $key . ': ' . $value;

			}

			$cu = curl_init();

			curl_setopt( $cu, CURLOPT_URL, $this-> url . $uri );
			curl_setopt( $cu, CURLOPT_HTTPHEADER, $httpheader );
			curl_setopt( $cu, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $cu, CURLOPT_COOKIEFILE, $this-> cookie_path . $this-> cookie_file );
			curl_setopt( $cu, CURLOPT_COOKIEJAR, $this-> cookie_path . $this-> cookie_file );

			$response = curl_exec( $cu );

			curl_close( $cu );

			if( $trace [ 1 ] [ 'function' ] == '__construct' ) {

				preg_match( '/<html.*data-mvtoken="(?<mvtoken>.*?)".*>/', $response, $data );
				$this-> mvtoken = $data [ 'mvtoken' ];

			} else {}

			if( $trace [ 1 ] [ 'function' ] == 'following' || $trace [ 1 ] [ 'function' ] == 'vids' ) {

				return json_decode( $response, true );

			} else {}

		}

	}

?>
