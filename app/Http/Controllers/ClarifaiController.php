<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClarifaiController extends Controller
{
    private $client;
    private $client_secret;
    private $access_token;

    public function __construct() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clarifai.com/v1/token/");
        //curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/xml", "Authorization: removed_dev_key:removed_api_key"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=KX8bEdjfkaZ2XJzAxbIBFN5e2EBBT-syAa-PQNCA&client_secret=K4WCmwxkb1nz31EgI9tbMDOnSu1mrNjbPj0qoI22&grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if(!curl_exec($ch)){
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        } else {
            $json = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if ($json['access_token']) {
                echo $json['access_token'];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.clarifai.com/v1/tag/?model=food-items-v1.0&url=http://weknowyourdreams.com/images/food/food-07.jpg");
                curl_setopt($ch, CURLOPT_HEADER, true);
                //curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$json['access_token']));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $json = curl_exec($ch);
                print_r($json);
                curl_close($ch);
            } else {
                var_dump($json);
            }



        }
    }

    public function getResponse(){
        //$ch = curl_init();
        //curl_setopt($ch, CURLOPT_URL, "https://api.clarifai.com/v1/tag/?model=food-items-v1.0&url=https://samples.clarifai.com/food.jpg");
        //curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer nEGd0aPNBsd7GxWPyYeBCudkLA6Ehy"));
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //$json = curl_exec($ch);
        //print_r($json);
        //curl_close($ch);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clarifai.com/v1/tag/?model=food-items-v1.0");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data","application/x-www-form-urlencoded","Authorization: Bearer raDVyKZLg74kO006Xrl9cMWJFb3URM"));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer raDVyKZLg74kO006Xrl9cMWJFb3URM"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "encoded_data=@./food-07.jpg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        print_r($json);
        curl_close($ch);
    }

}
