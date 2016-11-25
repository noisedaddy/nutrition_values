<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use cURL;
use cURL\RequestsQueue;

class ManageController extends Controller
{
    //
    public function multicurl_testing(){
        
        $time_start = microtime(true);

        $cmd = 'curl -v "https://api.clarifai.com/v1/tag/" \
  -X POST -F "encoded_data=@./uploads/1/1479979443-orange-01.jpg" \
  -H "Authorization: Bearer qu4W1d5Ljmlrpp1V035xYpnBQ3qksw"';

        $response = shell_exec($cmd);
        $json = json_decode($response, true);
        $tags = $json['results'][0]['result']['tag']['classes'];
                        
        $data = array();
        $food_report = array();
        
        //$search = array();
        
//        if (is_array($tags)){
//            foreach ($tags as $tag){
//                $data[] = 'https://api.nal.usda.gov/ndb/search?q='.$tag.'&ds=Standard%20Reference&sort=r&max=1&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY';
//            }
//        }
        
        $data = array(
            'tomato'=>'http://api.nal.usda.gov/ndb/reports/?ndbno=13451&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY',
            'apple'=> 'http://api.nal.usda.gov/ndb/reports/?ndbno=11531&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY',
            'juice'=> 'http://api.nal.usda.gov/ndb/reports/?ndbno=18240&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY'
          );
        
        $timeout = 10;
        $search = $this->getResponsesFromUrlsAsynchronously($data, $timeout);
        
        foreach ($search as $s){            
            //if ($s != 0) $food_report[] = 'http://api.nal.usda.gov/ndb/reports/?ndbno='.$s.'&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY';     
            if (isset($s['list'])) $food_report[] = 'http://api.nal.usda.gov/ndb/reports/?ndbno='.$s['list']['item'][0]['ndbno'].'&type=b&format=json&api_key=RApFefou0FWBiBmidn83eAPPt1WRSWTTl5MqL7eY';     
            
        }
        
        print_r($food_report);
//        $data = $this->getResponsesFromUrlsAsynchronously($food_report, $timeout);
        
//        echo "<pre>";
//        print_r($search);
//        echo "</pre>";
        
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start) / 60;
        echo 'Total Execution Time: ' . ($time_end - $time_start) . ' Sec';

    }
    
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
    
}
