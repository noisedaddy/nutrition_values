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

                    echo json_encode($json['results'][0]['result']['tag']['classes']);

                } else {
                    echo $this->access_token['Error'];
                }


            } else {
                echo "Ajax Error";
            }


    }

}
