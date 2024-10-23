<?php

  $id = $_GET['id'];

	header( 'Content-Type: application/xml; charset=utf-8' );

	require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php';

	$mysqli = new mysqli( $hostname, $username, $password, $database );

	$mysqli-> set_charset( $charset );

	$items = '';

		$result = $mysqli-> query( "SELECT * FROM `" . $prefix . "subscriptions` WHERE `scraper_id`=" . $id );

		$subscriptions = array();

		while( $subscription = $result-> fetch_assoc() ) {

			$subscriptions[] = $subscription[ 'id' ];

		}

    if ( isset( $_GET['profile_id'] ) ) {
      $subscriptions = array( $_GET['profile_id'] );
    }

		$subscriptions = implode( ',', $subscriptions );

		// $query = "SELECT * FROM `posts` GROUP BY `post_id` ORDER BY `post_id` DESC";
		// $query = "SELECT * FROM `" . $prefix . "posts` WHERE `id` IN ( SELECT MAX(id) FROM `" . $prefix . "posts` GROUP BY `post_id` ) ORDER BY `post_id` DESC";
		$query = "SELECT * FROM `" . $prefix . "posts` WHERE `id` IN ( SELECT MAX(id) FROM `" . $prefix . "posts` GROUP BY `post_id` ) AND `profile_id` IN (" . $subscriptions . ") ORDER BY `post_id` DESC";

		$result = $mysqli-> query( $query );

    if ( $result ) {

      while ($item = $result->fetch_assoc()) {

        $separated = '* ';

        if ($item ['separated']) {

          $separated = '';

        } else {
        }

        $items .= "\t\t<item>\n\t\t\t<title><![CDATA[" . $separated . $item['profile_name'] . " - " . $item['title'] . "]]></title>\n\t\t\t<description><![CDATA[<img src=\"" . $item['thumb'] . "\" /><ul><li>" . $item['title'] . "</li><li>" . $item['profile_name'] . "</li><li><a href=\"" . $item['link'] . "\">" . $item['link'] . "</a></li><li>$" . $item['price'] . "</li><li>" . $item['duration'] . "</li></ul>]]></description>\n\t\t\t<link>" . $item['link'] . "</link>\n\t\t</item>\n";

      }

    }

	echo '<?xml version="1.0" encoding="UTF-8" ?>';

?>
<rss version="2.0">
	<channel>
		<title>RSS</title>
		<language>en-us</language><?php print "\n" . $items; ?>
   </channel>
</rss>
