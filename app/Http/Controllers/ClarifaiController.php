<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Request;

class ClarifaiController extends Controller
{
    private $client;
    private $client_secret;
    private $access_token;
    private $path = null;
    private $tags = null;

    public function __construct() {

    }

    public function getAccessToken(){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clarifai.com/v1/token/");
        //curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/xml", "Authorization: removed_dev_key:removed_api_key"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=KX8bEdjfkaZ2XJzAxbIBFN5e2EBBT-syAa-PQNCA&client_secret=K4WCmwxkb1nz31EgI9tbMDOnSu1mrNjbPj0qoI22&grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if(!curl_exec($ch)){
            return array('Error'=>curl_error($ch), 'Code'=>curl_errno($ch));
        } else {
            $json = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if ($json['access_token']) {
                return $json['access_token'];
            } else {
                return array('Error'=>'Auth Error');
            }
        }

    }
    //GET TAGS FOR IMAGE
    public function tags(){

            if (Request::method() == "POST" && Request::ajax()){

                $this->path = Request::input('path');
                $this->access_token = $this->getAccessToken();

                if (!is_array($this->access_token)) {
                    $cmd = 'curl -v "https://api.clarifai.com/v1/tag/" \
  -X POST -F "encoded_data=@./'.$this->path.'" \
  -H "Authorization: Bearer '.$this->access_token.'"';

                    $response = shell_exec($cmd);
                    $json = json_decode($response, true);
                    return json_encode($json['results'][0]['result']['tag']['classes']);
                } else {
                    return $this->access_token['Error'];
                }
            } else {
                return "Ajax Error";
            }

    }
    
    
    public function getTags($t){
        
                $this->path = $t;
                $this->access_token = $this->getAccessToken();

                if (!is_array($this->access_token)) {
                    $cmd = 'curl -v "https://api.clarifai.com/v1/tag/" \
  -X POST -F "encoded_data=@./'.$this->path.'" \
  -H "Authorization: Bearer '.$this->access_token.'"';

                    $response = shell_exec($cmd);
                    $json = json_decode($response, true);
                    return $json['results'][0]['result']['tag']['classes'];
                } else {
                    return $this->access_token['Error'];
                }

    }
    
    //GET NUTRITION REPORT
    public function getReport(){

        $t = Request::input('path');
        $tags = $this->getTags($t);
        
        //$this->tags = $this->tags();
        
        $data = array();
        $food_report = array();
        
        //$search = array();
        
        if (is_array($tags)){
            foreach ($tags as $tag){
                $data[] = 'https://api.nal.usda.gov/ndb/search?q='.$tag.'&ds=Standard%20Reference&sort=r&max=1&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY';
            }
        }
        
//        $search = $this->multiRequest($data, array(), "search");
//        
//        foreach ($search as $s){
//            
//            if ($s != 0) $food_report[] = 'http://api.nal.usda.gov/ndb/reports/?ndbno='.$s.'&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY';
//            
//        }
//        
//        $search_report = $this->multiRequest($food_report, array(), "food_report");
//        
////        $food_report = array(
////            'http://api.nal.usda.gov/ndb/reports/?ndbno=13451&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY',
////            'http://api.nal.usda.gov/ndb/reports/?ndbno=11531&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY',
////            'http://api.nal.usda.gov/ndb/reports/?ndbno=18240&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY'
////          );
//        
//        $search_report = $this->multiRequest($food_report, array(), "food_report");
//        
//        echo json_encode($search_report);
                
        $timeout = 10;
        $search = $this->getResponsesFromUrlsAsynchronously($data, $timeout);
        
        foreach ($search as $s){                 
            if (isset($s['list'])) $food_report[] = 'http://api.nal.usda.gov/ndb/reports/?ndbno='.$s['list']['item'][0]['ndbno'].'&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY';                 
        }
 
//        $food_report = array(
//            'http://api.nal.usda.gov/ndb/reports/?ndbno=13451&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY',
//            'http://api.nal.usda.gov/ndb/reports/?ndbno=11531&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY',
//            'http://api.nal.usda.gov/ndb/reports/?ndbno=18240&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY'
//        );
        
        $data = $this->getResponsesFromUrlsAsynchronously($food_report, $timeout);
                        
        echo json_encode($data);
        
    }

    //NON BLOCKING MULTI REQUEST
    public function getResponsesFromUrlsAsynchronously(array $urlsArray, $timeout = 8) {
         
        $queue = new \cURL\RequestsQueue;

        // Set default options for all requests in queue
        $queue->getDefaultOptions()
                ->set(CURLOPT_TIMEOUT, $timeout)
                ->set(CURLOPT_RETURNTRANSFER, true);

        // =========================================================================
        // Define some extra variables to be used in callback

        global $requestUidToUserUrlIdentifiers;
        $requestUidToUserUrlIdentifiers = array();

        global $userIdentifiersToResponses;
        $userIdentifiersToResponses = array();

        // =========================================================================

        // Set function to be executed when request will be completed
        $queue->addListener('complete', function (\cURL\Event $event) {

            // Define user identifier for this url
            global $requestUidToUserUrlIdentifiers;
            $requestId = $event->request->getUID();
            $userIdentifier = $requestUidToUserUrlIdentifiers[$requestId];

            // =========================================================================

            $response = $event->response;
            $json = $response->getContent(); // Returns content of response
            
//             if ($report == "search"){
//                 
//                if (!isset($json['error']) && isset($json['list']))
//                    $json = $json['list']['item'][0]['ndbno'];
//                else 
//                    $json = 0;
//                
//             } else {
//                 
//             }

                
            $apiResponseAsArray = json_decode($json, true);
            //$apiResponseAsArray = $apiResponseAsArray['json'];

            // =========================================================================
            // Store this response in proper structure
            global $userIdentifiersToResponses;
            $userIdentifiersToResponses[$userIdentifier] = $apiResponseAsArray;
            
            return $apiResponseAsArray;
        });

        // =========================================================================

        // Add all request to queue
        foreach ($urlsArray as $userUrlIdentifier => $url) {
            $request = new \cURL\Request($url);
            $requestUidToUserUrlIdentifiers[$request->getUID()] = $userUrlIdentifier;
            $queue->attach($request);
        }

        // =========================================================================

        // Execute queue
        $queue->send();

        // =========================================================================

        return $userIdentifiersToResponses;
    }
    
    //SEND MULTI BLOCKING REQUEST 
    public function multiRequest($data, $options = array(), $report = "search") {

        // array of curl handles
        $curly = array();
        // data to be returned
        $result = array();

        // multi handle
        $mh = curl_multi_init();

        // loop through $data and create curl handles
        // then add them to the multi-handle
        foreach ($data as $id => $d) {

            $curly[$id] = curl_init();

            $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
            curl_setopt($curly[$id], CURLOPT_URL,            $url);
            curl_setopt($curly[$id], CURLOPT_HEADER,         0);
            curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

            // post?
//    if (is_array($d)) {
//      if (!empty($d['post'])) {
//        curl_setopt($curly[$id], CURLOPT_POST,       1);
//        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
//      }
//    }

            // extra options?
            if (!empty($options)) {
                curl_setopt_array($curly[$id], $options);
            }

            curl_multi_add_handle($mh, $curly[$id]);
        }

        // execute the handles
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);


        // get content and remove handles
        foreach($curly as $id => $c) {
            
        //    $result[$id] = curl_multi_getcontent($c);
            $json = json_decode(curl_multi_getcontent($c), true);

            if ($report == "search"){
                
                if (!isset($json['error']) && isset($json['list']))
                    $result[$id] = $json['list']['item'][0]['ndbno'];
                else 
                    $result[$id] = 0;
                
            } else {
                $result[$id] = $json['report']['food']['nutrients'];
            }
                
            curl_multi_remove_handle($mh, $c);
        }

        // all done
        curl_multi_close($mh);

        return $result;
    }
}
