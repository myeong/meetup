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

         
/* Get events from each city */
    echo getRSVPs($meetup, 'PA', 'Pittsburgh', 'US');
    echo getRSVPs($meetup, 'MD', 'Baltimore', 'US');

/*
	foreach ($response->results as $event) 
	{
		$eventname = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', ' ', $event->name);
		$venuename =  preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', ' ', $event->venue->name);
		$address =  preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', ' ', $event->venue->address_1);

	    echo '"' . $eventname . '","' . $event->venue->lat . '","' . $event->venue->lon . '","' . $venuename . '","' . $address . '","' . date('Y-m-d H:i', $event->time / 1000) . '":newline';
	}
*/
}

function getTopicCategory($meetup){
    $response = $meetup->getTopicCategories(array());

    // total number of items matching the get request
    $total_count = $response->meta->total_count;
    $json_format = json_encode($response);

    $fp = fopen('TopicsCategory_' . date('Y-m-d') . '.json', 'w');
    fwrite($fp, $json_format);
    fclose($fp);
    
    return $total_count . ' topic categories <br>';
}

function getTopic($meetup){
    $response = $meetup->getTopics(array());

    // total number of items matching the get request
    $total_count = $response->meta->total_count;
    $json_format = json_encode($response);

    $fp = fopen('Topics_' . date('Y-m-d') . '.json', 'w');
    fwrite($fp, $json_format);
    fclose($fp);
    
    return $total_count . ' topics <br>';
}

function getEvents($meetup, $state, $city, $country){
    $response = $meetup->getOpenEvents(array(
        'state' => $state,
        'city' => $city,
        'country' => $country,
        'status' => 'past',
    ));
    $file_index = 0;

    // total number of items matching the get request
    $total_count = $response->meta->total_count;
    $json_format = json_encode($response);

    $fp = fopen('Events_' . date('Y-m-d') . '_' . $city . '_' . $file_index .'.json', 'w');
    fwrite($fp, $json_format);  
    fclose($fp);
    sleep(1);

    while ($meetup->hasNext() != null){
        if ($response->meta->next == '') break;
        $response = $meetup->getNext($response); 
        $json_format = json_encode($response);       
        $file_index += 1;
        $fp = fopen('Events_' . date('Y-m-d') . '_' . $city . '_' . $file_index .'.json', 'w');
        fwrite($fp, $json_format);
        fclose($fp);
        sleep(1);
    }
    
    return $total_count . 'events for ' . $city .' <br>';
}


function getGroups($meetup, $state, $city, $country){
    $group_response = $meetup->getGroups(array(
        'state' => $state,
        'city' => $city,
        'country' => $country,
        'status' => 'past',
    ));
    $file_index = 0;

    $total_count = $group_response->meta->total_count;
    $json_format = json_encode($group_response);
       
    $fp2 = fopen('Group_' . date('Y-m-d') . '_' . $city . '_' . $file_index . '.json', 'w');
    fwrite($fp2, $json_format);
    fclose($fp2);
    sleep(1);

    while ($meetup->hasNext() != null){
        if ($group_response->meta->next == '') break;
        $group_response = $meetup->getNext($group_response); 
        $json_format = json_encode($group_response); 
        $file_index += 1;
        $fp2 = fopen('Group_' . date('Y-m-d') . '_' . $city . '_' . $file_index . '.json', 'w');
        fwrite($fp2, $json_format);
        fclose($fp2);
        sleep(1);
    }

    return $total_count . 'groups for ' . $city .' <br>';
}

?>

