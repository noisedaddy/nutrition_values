<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\TagsRequest;

class ClarifaiController extends Controller
{
    private $access_token;
    private $path = null;
    private $tags = null;
    private $timeout = 10;
    const CLARIFAI_BASEURL = "https://api.clarifai.com/v1/token/";
    const CLARIFAI_TOKEN = "client_id=KX8bEdjfkaZ2XJzAxbIBFN5e2EBBT-syAa-PQNCA&client_secret=K4WCmwxkb1nz31EgI9tbMDOnSu1mrNjbPj0qoI22&grant_type=client_credentials";
    const USDA_FOOD_BASEURL = "https://api.nal.usda.gov/ndb/";
    const USDA_FOOD_TOKEN = "api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY";
    
    public function __construct() {

    }

    /**
     * Get clarifai access token 
     * @return type string
     */
    public static function getAccessToken(){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::CLARIFAI_BASEURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, self::CLARIFAI_TOKEN);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if(!curl_exec($ch)){
            return array('Error'=>curl_error($ch), 'Code'=>curl_errno($ch));
        } else {
            $json = json_decode(curl_exec($ch), true);
            curl_close($ch);
            return (isset($json['access_token'])) ? $json['access_token'] : array("Error" => __METHOD__." Line Number: ".__LINE__);
        }

    }
    
    /**
     * Find image tags from clarifai api. 
     * @param TagsRequest $request
     * @return type
     */
    public function tags(TagsRequest $request){

                $this->path = $request->input('path');
                $this->access_token = self::getAccessToken();

                if (!is_array($this->access_token)) {
                    $cmd = 'curl -v "https://api.clarifai.com/v1/tag/" \
  -X POST -F "encoded_data=@./'.$this->path.'" \
  -H "Authorization: Bearer '.$this->access_token.'"';

                    $response = shell_exec($cmd);
                    $json = json_decode($response, true);

                    return (isset($json['results'])) ? json_encode($json['results'][0]['result']['tag']['classes']) : array("Error" => __METHOD__." Line Number: ".__LINE__);
                } else {
                    return array("Error" => __METHOD__." Line Number: ".__LINE__);
                }

    }
   
    /**
     * Get nutrition report from api.nal.usda.gov
     * @param TagsRequest $request
     */
    public function getReport(TagsRequest $request){

        $tags = json_decode($this->tags($request));
                
        $data = array();
        $food_report = array();
        
        //$search = array();
        
        if (is_array($tags)){
            foreach ($tags as $tag){
                $data[] = self::USDA_FOOD_BASEURL."search?q=".$tag."&ds=Standard%20Reference&sort=r&max=1&format=json&".self::USDA_FOOD_TOKEN;
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
                
        
        $search = $this->getResponsesFromUrlsAsynchronously($data, $this->timeout);
        
        foreach ($search as $s){                 
            if (isset($s['list'])) $food_report[] = self::USDA_FOOD_BASEURL."reports/?ndbno=".$s['list']['item'][0]['ndbno']."&type=b&format=json&".self::USDA_FOOD_TOKEN;                 
        }
        
        $data = $this->getResponsesFromUrlsAsynchronously($food_report, $this->timeout);
                        
        return json_encode($data);
        
    }

    /**
     * Async, non-blocking curl requests for nutrition report
     * @global type $requestUidToUserUrlIdentifiers
     * @global array $userIdentifiersToResponses
     * @param array $urlsArray
     * @param type $timeout
     * @return array
     */
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
    
    /**
     * Curl blocking request. Testing purpose, replaced by getResponsesFromUrlsAsynchronously
     * @param type $data
     * @param type $options
     * @param type $report
     * @return type
     */
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
