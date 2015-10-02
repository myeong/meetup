<?php

require 'meetup.php';

if( !isset($_GET['code']) )
{
    //authorize and go back to URI w/ code
    $meetup = new Meetup();
    $meetup->authorize(array(
        'client_id'     => 't97l196jncae6ljsgvejukp5b8',
        'redirect_uri'  => 'http://meetup.myeonglee.com',  
        // "client_id"     => '27rl5urk95fgd40ehavhp4jhid',
        // "redirect_uri"  => 'http://myeonglee.com/meetup2',    
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
            // "client_id"     => '27rl5urk95fgd40ehavhp4jhid',
            // "client_secret" => 'l6mr9ga1u62mgk8emfn3u8s0k9',
            // "redirect_uri"  => 'http://myeonglee.com/meetup2',
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
    echo getEvents($meetup, 'PA', 'Pittsburgh', 'US');
    echo getEvents($meetup, 'MD', 'Baltimore', 'US');
    echo getEvents($meetup, 'NY', 'New York', 'US');
    echo getEvents($meetup, 'MA', 'Boston', 'US');


 // Searching for Groups in each city 
    echo getGroups($meetup, 'PA', 'Pittsburgh', 'US');
    echo getGroups($meetup, 'MD', 'Baltimore', 'US');
    echo getGroups($meetup, 'NY', 'New York', 'US');
    echo getGroups($meetup, 'MA', 'Boston', 'US');
    
    echo getTopicCategory($meetup);
    echo getTopic($meetup);

/* Pulling RSVP data from Event IDs */
//     $event_ids = array();
//     $file = fopen("data/neigh_id.csv","r");
//     //$file = fopen("data/test.csv","r");
//     $i = 0;

//     while(!feof($file))
//     {
//         $event_ids[$i] = fgetcsv($file)[0];
//         $i += 1;
//     }

//     fclose($file);    
//     $i = 0;
//     $round = 0;
//     $chunk = '';
//     print('Size: ' . strval(sizeof($event_ids)) . '</br>');
//     // print_r($event_ids);

// /* Get RSVP data for a give event ID */
//     for ($i=0; $i<sizeof($event_ids); $i++) {
//         $chunk .= strval($event_ids[$i]);

//         if ($i!=0 && $i % 100 == 0) {            
//             echo getRSVPs($meetup, $chunk, $round);            
//             $chunk = '';
//             $round += 1;
            
//         } elseif ($i == sizeof($event_ids) - 1) {            
//             echo getRSVPs($meetup, $chunk, $round);            
//             $chunk = '';
//             $round += 1;
                      
//         } else {
//             $chunk .= ',';             
//         }        
//     } 
    
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

function getRSVPs($meetup, $eids, $round){
    $output = array();    
    $total_count = 0;
            
    $response = $meetup->getRSVPs(array(
        'event_id' => $eids,  
        'rsvp' => 'yes',          
    ));    

    foreach ($response->results as $item){       
        $str = $item->event->id . ',' . $item->member->member_id;        
        $output[$item->rsvp_id][] = $str;            
    }

    $total_count = $response->meta->total_count;
    print (strval($round) . ' round: ');

    while ($meetup->hasNext() != null){
        if ($response->meta->next == '') break;
        $response = $meetup->getNext($response); 
        // print ('  ...next response..<br>');
        foreach ($response->results as $item){
            $str = $item->event->id . ',' . $item->member->member_id;
            $output[$item->rsvp_id][] = $str;   
        }        
        sleep(1);
    }        
    
    $file_index += 1;
    sleep(1);

    getMembersReady($meetup, $output, $round);
        
    $fp = fopen('RSVP_' . date('Y-m-d') . '_' . strval($round) . '.csv', 'w');
    foreach ($output as $row){        
        fputcsv($fp, $row);  
    }
    fclose($fp);    
    
    return $total_count . ' RSVPs pulled <br>';
}

function getMembersReady($meetup, $mids, $rsvpround){
    $unique = array();

    foreach ($mids as $key => $value) {        
        $mid = explode(',', $value[0])[1];
        $unique[$mid] = $mid;
    }
    print (strval(sizeof($unique)) . ' unique member IDs... <br>');

    $chunk = '';
    $i = 0;
    $round = 0;

    foreach ($unique as $item) {
        $chunk .= strval($item);

        if ($i!=0 && $i % 400 == 0) {            
            echo getMembers($meetup, $chunk, $round, $rsvpround);            
            $chunk = '';
            $round += 1;
            
        } elseif ($i == sizeof($unique) - 1) {            
            echo getMembers($meetup, $chunk, $round, $rsvpround);            
            $chunk = '';
            $round += 1;
                      
        } else {
            $chunk .= ',';             
        }        
        $i += 1;
    } 

    
}

function getMembers($meetup, $mids, $round, $rsvpround){
    $output = array();    
    $total_count = 0;
            
    $response = $meetup->getMembers(array(
        'member_id' => $mids,                   
    ));  
    $total_count = $response->meta->total_count;

    foreach ($response->results as $item){       
        $str = $item->id . ',' . $item->city . ',' . $item->state . ',' . $item->lat . ',' . 
                        $item->lon;        
        $output[$item->id][] = $str;            
    }

    $fp = fopen('Members_' . date('Y-m-d') . '_' . strval($rsvpround) . '_' . strval($round) . 
            '.csv', 'w');

    foreach ($output as $row){        
        fputcsv($fp, $row);  
    }
    fclose($fp);   
    return $total_count . ' Members pulled from ' . $rsvpround . '-' . $round. ' round<br>';

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

