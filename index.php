<?php

require 'meetup.php';

if( !isset($_GET['code']) )
{
    //authorize and go back to URI w/ code
    $meetup = new Meetup();
    $meetup->authorize(array(
        'client_id'     => 't97l196jncae6ljsgvejukp5b8',
        'redirect_uri'  => 'http://meetup.myeonglee.com',     
        )
    );
}
else
{
    //assuming we came back here...
    $meetup = new Meetup(
        array(
            "client_id"     => 't97l196jncae6ljsgvejukp5b8',
            "client_secret" => 'ma98kbnuerp7fkhpcapo2eg56q',
            "redirect_uri"  => 'http://meetup.myeonglee.com',
            "code"          => $_GET['code'], //passed back to us from meetup
        )
    );

    //get an access token
    $response = $meetup->access();

    //now we can re-use this object for several requests using our access
    //token
    $meetup = new Meetup(
        array(
            "access_token"  => $response->access_token,
        )
     );

     //store details for later in case we need to do requests elsewhere
     //or refresh token
     $_SESSION['access_token'] = $response->access_token;
     $_SESSION['refresh_token'] = $response->refresh_token;
     $_SESSION['expires'] = time() + intval($response->expires_in); //use if >= intval($_SESSION['expires']) to check

     //get all groups for this member
     //$response = $meetup->getGroups('member_id' => 'Myeong');

     //get all events for this member
     
     $response = $meetup->getOpenEvents(array(
    	'state' => 'PA',
    	'city' => 'Pittsburgh',
    	'country' => 'US',
    	'status' => 'past',
	));

	// total number of items matching the get request
	$total_count = $response->meta->total_count;
	echo $total_count . '<br>';

	foreach ($response->results as $event) 
	{
		$eventname = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', ' ', $event->name);
		$venuename =  preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', ' ', $event->venue->name);
		$address =  preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', ' ', $event->venue->address_1);

	    echo '"' . $eventname . '","' . $event->venue->lat . '","' . $event->venue->lon . '","' . $venuename . '","' . $address . '","' . date('Y-m-d H:i', $event->time / 1000) . '":newline';
	}

}

?>
