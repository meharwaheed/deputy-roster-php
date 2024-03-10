<?php

require 'vendor/autoload.php';  // Load Guzzle

class Client {

    public static function request($method, $apiEndPoint, $data = null) {

        try {

            /**
             * BASE URL
             */
            $base_url= "https://353f6527123646.as.deputy.com/";
            /**
             * Define Auth Token
             */
            $authToken = '4f768e19bf2f699bb7380feac71f6130';
            /**
             * Create a Guzzle client
             */
            $client = new GuzzleHttp\Client();

            /**
             * Request headers
             */
            $headers = [
                'Authorization' => "Bearer ". $authToken,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ];

            /**
             * Make a request
             */
            return $client->request($method, ($base_url . $apiEndPoint), [
                'headers' => $headers,
                'json' => $data,
            ]);

        } catch (Exception $e) {
            $message = $e->getMessage();
            $pattern = '/(.*Overlap detected.*already working.* )/i';
            if (preg_match($pattern, $message)) {
                throw new Exception("OverlapDetected");
            } else {
                die(var_export($message));
            }
        }
    }
}