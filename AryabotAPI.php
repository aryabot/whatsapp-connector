<?php

class AryabotAPI
{
    private $curl;
    public function __construct()
    {
        // load the config
        require 'env.php';
        $this->env = $env;
        $this->curl = curl_init();
    }

    public function init($data)
    {
        $options = [
            'method' => 'POST',
            'body' => $data,
            'url' => 'api/init',
            'qs' => [],
        ];

        return $this->callAI($options);
    }

    public function getAIResponseFromAudio($data)
    {
        $options = [
            'method' => 'POST',
            'body' => $data,
            'url' => 'api/audio',
            'qs' => [],
        ];

        return $this->callAI($options);
    }

    public function getAIResponseFromContact($data)
    {
        $options = [
            'method' => 'POST',
            'body' => $data,
            'url' => 'api/contact',
            'qs' => [],
        ];

        return $this->callAI($options);
    }

    public function getAIResponseFromText($data)
    {
        $options = [
            'method' => 'POST',
            'body' => $data,
            'url' => 'api/text',
            'qs' => [],
        ];

        return $this->callAI($options);
    }

    public function getAIResponseFromLocation($data)
    {
        $options = [
            'method' => 'POST',
            'body' => $data,
            'url' => 'api/location',
            'qs' => [],
        ];

        return $this->callAI($options);
    }

    // $options should have the following
    // url [string] => API end point to call
    // method [string] => GET, POST
    // qs [Array] => Query parameters, if not applicable, leave the array blank
    private function callAI($options)
    {
        $api_url = $this->env['AI_HOST'].$options['url'].'?'.http_build_query($options['qs']);
        curl_setopt_array($this->curl, [
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($options['body']),
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
            ],
        ]);

        $response = curl_exec($this->curl);
        $err = curl_error($this->curl);

        $data = [
            'request' => $options,
            'response' => $response,
            'error' => $err,
        ];

        $this->log($data);

        if ($err) {
            echo $api_url."\n";
            echo 'cURL Error #: '.$err."\n";

            return;
        } else {
            return json_decode($response, 1);
        }
    }

    /*******************************************
     *
     *  Using Sockets to make a POST request,
     *  to Another system, that will log the
     *  request/message sent by the user.
     *
     * This might cause a delay of 100 milliseconds,
     * but, this is the best and fastest way to log!
     *
     ******************************************/
    private function log($data)
    {
        print_r($data);
        $post_data = json_encode($data);
        $socket = null;

        // Initiates a connection to Logger using port logger_port with a timeout of 3 seconds.
        $socket = fsockopen($this->env['logger_ip'], $this->env['logger_port'], $errno, $errstr, 3);

        // Checks if the connection was fine
        if (!$socket) {
            // Connection failed so we display the error number and message and stop the script from continuing
            echo 'Error Logging request : '.$errno.' '.$errstr."\n";

            return false;
        } else {
            // Builds the header data we will send along with are post data.
            // This header data tells the web server we are connecting to what
            // we are, what we are requesting and the content type so that it can process are request.
            $http = "POST /log/aryabot_response HTTP/1.1\r\n";
            $http .= "Host: localhost\r\n";
            $http .= "Content-Type: application/json\r\n";
            $http .= 'Content-length: '.strlen($post_data)."\r\n";
            $http .= "Connection: close\r\n\r\n";
            $http .= $post_data."\r\n\r\n";

            // Sends are header data to the web server
            fwrite($socket, $http);
            // Close are request or the connection will stay open untill are script has completed.
            fclose($socket);
        }

        return true;
    }
}

