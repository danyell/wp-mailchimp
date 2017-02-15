<?php
/*
 Fetch MailChimp lists and drop them in files in a directory.
 Based on https://rudrastyh.com/mailchimp-api/get-lists.html
 
 By: DdSG daniel@hubrix.co
 Started: 14 Feb 2017 1:35am
 */

require_once('mcl-config.php');

/*
    CONTENTS of mcl-config.php:
  
    $api_key = 'MC API KEY HERE';       // Your MailChimp API Key
    $data_dir = "/var/tmp/mclists";     // <-- MUST be readable by group specific in $webgroup!
    $webgroup = 'mygroup';              // For Web use, should be whatever group runs Apache / Nginx
*/

function rudr_mailchimp_curl_connect( $url, $request_type, $api_key, $data = array() ) {
	if( $request_type == 'GET' )
		$url .= '?' . http_build_query($data);
 
	$mch = curl_init();
	$headers = array(
		'Content-Type: application/json',
		'Authorization: Basic '.base64_encode( 'user:'. $api_key )
	);
	curl_setopt($mch, CURLOPT_URL, $url );
	curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($mch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
	curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
	curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type); // according to MailChimp API: POST/GET/PATCH/PUT/DELETE
	curl_setopt($mch, CURLOPT_TIMEOUT, 10);
	curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection
 
	if( $request_type != 'GET' ) {
		curl_setopt($mch, CURLOPT_POST, true);
		curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json
	}
 
	return curl_exec($mch);
}

function get_mc_lists() {
    global $api_key;
    // Query String Perameters are here
    // for more reference please vizit http://developer.mailchimp.com/documentation/mailchimp/reference/lists/
    $data = array(
        'fields' => 'lists'
    );

    $url = 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/';
    $result = json_decode( rudr_mailchimp_curl_connect( $url, 'GET', $api_key, $data) );
    //print_r( $result);
    return $result;
}

function get_mc_list_members($listId) {
    global $api_key;
    // Query String Perameters are here
    // for more reference please vizit http://developer.mailchimp.com/documentation/mailchimp/reference/lists/
    $data = array(
        'fields' => 'members.id,members.email_address,members.status'
    );    
    $url = 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/' . $listId . '/members';
    $result = json_decode( rudr_mailchimp_curl_connect( $url, 'GET', $api_key, $data) );
    //print_r( $result);
    return $result;
}

// Retrieve our list of MailChimp lists.
// Get members of each list.
// Write each member-list and list of lists out to JSON files in $data_dir.

$AllMClists = array();
$foo = get_mc_lists();

// Create the directory if necessary
if (!is_dir($data_dir)) {
    mkdir($data_dir,0750);
    chgrp($data_dir,$webgroup);
}

foreach ($foo->lists as $oneList) {
    // echo "ID: " . $oneList->id . ", Name: " . $oneList->name . "\n";
    $AllMClists[$oneList->name] = $oneList->id; 
    $listMembers = get_mc_list_members($oneList->id);

    // echo json_encode($listMembers->members) . "\n\n";
    $filename = $data_dir . '/' . $oneList->id . '.json';
    file_put_contents($filename,json_encode($listMembers->members));
    chgrp($filename,$webgroup);
    chmod($filename,0640);

/*
    foreach ($listMembers->members as $oneMember) {
        // print_r( $oneMember );
       echo "   " . $oneMember->status . " " . $oneMember->email_address . " " . $oneMember->id . "\n";
    }
*/
}

// Finish up by writing out the index (list of MC lists)

$filename = $data_dir . '/mclists.json';
file_put_contents($filename,json_encode($AllMClists));
chgrp($filename,$webgroup);
chmod($filename,0640);

?>