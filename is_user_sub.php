<?php
/*
 Matching functions for mclists.php
 Load the saved MailChimp lists and provide utility function
 
 By: DdSG daniel@hubrix.co
 Started: 14 Feb 2017 2:35am
 */

class MCSubLookup {
    public $allLists;
    public $loadedList;
    public $listMembers;
    public $dataLoaded;
    public $data_dir;
    
    public function __construct() {
        $this->dataLoaded = false;
        $this->loadedList = '';
        $this->data_dir = "/var/www/mclists"; // <-- NASTY! should be config variable somewhere
    }
    
    function loadAllLists() {
        $filename = $this->data_dir . '/mclists.json'; // <-- more NASTY! should be config variable somewhere
        $this->allLists = json_decode(file_get_contents($filename),true);   // <-- convert obj to assoc array
        $this->dataLoaded = true;
    }
    
    function loadMembers($listname) {
        if ($this->loadedList != $listname) {
            if (!$this->dataLoaded) {
                $this->loadAllLists();
            }
            $filename = $this->data_dir . '/' . $this->allLists[$listname] . '.json';
            $this->listMembers = json_decode(file_get_contents($filename));
            $this->loadedList = $listname;
        }
    } 
    
    function is_email_subscribed($emailaddr,$listname) {
        $this->loadMembers($listname);
        // Can eliminate next foreach() by smarter json storage format
        foreach ($this->listMembers as $mbr) {
            if ($mbr->email_address == $emailaddr) {
                return true;
            }
        }
        return false;
    }
}

function is_email_subscribed($emailaddr,$listname) {
    global $hubrixMCSubLookup;
    return $hubrixMCSubLookup->is_email_subscribed($emailaddr,$listname);
}

// Convenience function for WordPress (check current user by UserID)
// NOTE: The function parameter $listname is human-readable list name, NOT the List ID!
function is_user_subscribed($listname) {
    if (function_exists('wp_get_current_user')) {
        $current_user = wp_get_current_user();
        if ($current_user->ID == 0) return false;
        return is_email_subscribed($current_user->user_email,$listname);
    }
    return false;
}

$hubrixMCSubLookup = new MCSubLookup();

// Unit test - just for the non-WP part
/*
function unit_test_is_user_subscribed() {
    $testvals = array(
        'marketing@danyell.com',
        'test2@danyell.com',
        'cbruce.page@gmail.com',
        'heather@example.com'
    );

    $lists = array( 'Hubrix News', 'Hubrix Actu', 'hubrixdev' );
    
    foreach ($lists as $list) {
        foreach ($testvals as $val) {
            echo $val . ' in ' . $list . ': ';
            echo (is_email_subscribed($val,$list) ? 'YES' : 'No') . "\n";
        }
    }
}

unit_test_is_user_subscribed();
*/
?>