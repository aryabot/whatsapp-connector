<?php

class EventsHandler extends AllEvents
{
    protected $aryabot;
    protected $whatsApp;
    protected $env;

    /**
     * This is a list of all current events. Uncomment the ones you wish to listen to.
     * Every event that is uncommented - should then have a function below.
     *
     * @var array
     */
    public $activeEvents = [
        /******************************
         * List of events to listen to
         *****************************/
        // Connection Events
        'onConnect',
        'onConnectError',
        'onDisconnect',
        'onGetError',
        'onLoginFailed',
        'onGetServerProperties',
        'onGetServicePricing',

        // Group Messages
        'onGetGroupsSubject',
        'onGetGroupMessage',
        'onGetGroupAudio',
        'onGetGroupImage',
        'onGetGroupLocation',
        'onGetGroupVideo',
        'onGetGroupvCard',

        // Personal Messages
        'onGetAudio',
        'onGetImage',
        'onGetLocation',
        'onGetMessage',
        'onMessageComposing',
        'onVoiceMessageComposing',
        'onMessagePaused',
        'onGetNormalizedJid',
        'onGetReceipt',
        'onGetVideo',
        'onGetvCard',

        // Handle WhatsApp Calls
        'onCallReceived',

        // Other Events
        'onPresenceAvailable',
        'onPresenceUnavailable',
    ];

    public function init($aryabot, $whatsApp)
    {
        require 'env.php';
        $this->env = $env;

        $this->whatsApp = $whatsApp;
        $this->aryabot = $aryabot;
    }

    public function onConnect($mynumber, $socket)
    {
        $data = [
            'status' => 'CONNECTED',
            'time' => time() * 1000,
            'socket' => $socket,
        ];
        $this->log($data, 'connection');
        echo "Connected to WhatsApp! :)\n";
    }

    public function onConnectError($mynumber, $socket)
    {
        $data = [
            'status' => 'ERROR',
            'time' => time() * 1000,
            'socket' => $socket,
        ];
        $this->log($data, 'connection');
        echo 'Connection Error! Socket Number : '.$socket."\n";
        // Log the Error and,
        die;
    }

    public function onDisconnect($mynumber, $socket)
    {
        $data = [
            'status' => 'DISCONNECTED',
            'time' => time() * 1000,
            'socket' => $socket,
        ];
        $this->log($data, 'connection');
        echo "Socket Error.. Disconnecting\n";
        // Log the Error and,
        die;
    }

    /* Handling Personal Messages */

    public function onGetMessage($mynumber, $from, $id, $type, $time, $name, $body)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $data['message'] = $body;
        $this->log($data);

        $response = $this->aryabot->getAIResponseFromText($data);
        if ($response == null || $response == false) {
            $response = [
                'type' => 'text',
                'message' => 'Whoops! Looks like something is wrong here. I\'ll be right back!',
            ];
        }

        $this->sendResponseToUser($from, $response);
    }

    public function onMessageComposing($mynumber, $from, $id, $type, $time)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        print_r($data);
        $this->log($data, 'message_composing');
    }

    public function onVoiceMessageComposing($mynumber, $from, $id, $type, $time)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        print_r($data);
        $this->log($data, 'recording_voice');
    }

    public function onMessagePaused($mynumber, $from, $id, $type, $time)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        print_r($data);
        $this->log($data, 'message_paused');
    }

    public function onGetImage($mynumber, $from, $id, $type, $time, $name, $size, $url, $file, $mimeType, $fileHash, $width, $height, $preview, $caption)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;

        unset($data['preview']);

        print_r($data);
        $this->log($data);
    }

    public function onGetLocation($mynumber, $from, $id, $type, $time, $name, $author, $longitude, $latitude, $url, $preview)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;

        unset($data['preview']);

        $this->log($data);

        $response = $this->aryabot->getAIResponseFromLocation($data);
        if ($response == null || $response == false) {
            $response = [
                'type' => 'text',
                'message' => 'Whoops! Looks like something is wrong here. I\'ll be right back!',
            ];
        }

        $this->sendResponseToUser($from, $response);
    }

    public function onGetvCard($mynumber, $from, $id, $type, $time, $name, $vcardname, $vcard)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $this->log($data);

        $response = $this->aryabot->getAIResponseFromContact($data);
        if ($response == null || $response == false) {
            $response = [
                'type' => 'text',
                'message' => 'Whoops! Looks like something is wrong here. I\'ll be right back!',
            ];
        }

        $this->sendResponseToUser($from, $response);
    }

    public function onGetAudio($mynumber, $from, $id, $type, $time, $name, $size, $url, $file, $mimeType, $fileHash, $duration, $acodec)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $this->log($data);

        $response = $this->aryabot->getAIResponseFromAudio($data);
        if ($response == null || $response == false) {
            $response = [
                'type' => 'text',
                'message' => 'Whoops! Looks like something is wrong here. I\'ll be right back!',
            ];
        }

        $this->sendResponseToUser($from, $response);
    }

    /*******************************************
     *
     * Handling Groups :
     * Right now, we'll just log it somewhere
     * till we figure out what to do with them!
     *
     *******************************************/

    public function onGetGroupMessage($mynumber, $from_group_jid, $from_user_jid, $id, $type, $time, $name, $body)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from_user_jid);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $data['is_group'] = true;
        print_r($data);
        $this->log($data, 'group_message');
    }

    public function onGetGroupAudio($mynumber, $from_group_jid, $from_user_jid, $id, $type, $time, $name, $size, $url, $file, $mimeType, $fileHash, $duration, $acodec)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from_user_jid);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $data['is_group'] = true;
        print_r($data);
        $this->log($data, 'group_message');
    }

    public function onGetGroupImage($mynumber, $from_group_jid, $from_user_jid, $id, $type, $time, $name, $size, $url, $file, $mimeType, $fileHash, $width, $height, $preview, $caption)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from_user_jid);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $data['is_group'] = true;
        print_r($data);
        $this->log($data, 'group_message');
    }

    public function onGetGroupLocation($mynumber, $from_group_jid, $from_user_jid, $id, $type, $time, $name, $author, $longitude, $latitude, $url, $preview)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from_user_jid);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $data['is_group'] = true;
        print_r($data);
        $this->log($data, 'group_message');
    }

    public function onGetGroupVideo($mynumber, $from_group_jid, $from_user_jid, $id, $type, $time, $name, $url, $file, $size, $mimeType, $fileHash, $duration, $vcodec, $acodec, $preview, $caption)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from_user_jid);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $data['is_group'] = true;
        print_r($data);
        $this->log($data, 'group_message');
    }

    public function onGetGroupvCard($mynumber, $from_group_jid, $from_user_jid, $id, $type, $time, $name, $vcardname, $vcard)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from_user_jid);
        $data['time'] = $time * 1000;
        $data['function'] = __FUNCTION__;
        $data['is_group'] = true;
        print_r($data);
        $this->log($data, 'group_message');
    }

    /*************************
     * Handling Other Events *
     ************************/

    public function onPresenceAvailable($mynumber, $from)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = time() * 1000;
        $data['function'] = __FUNCTION__;
        print_r($data);
        $this->log($data, 'online');
    }

    public function onPresenceUnavailable($mynumber, $from, $last)
    {
        $data = get_defined_vars();
        $data['phone'] = $this->getNumberFromWhatsApp($from);
        $data['time'] = time() * 1000;
        $data['function'] = __FUNCTION__;
        print_r($data);
        $this->log($data, 'offline');
    }

    private function sendResponseToUser($target, $response)
    {
        switch ($response['type']) {
            case 'text':
                // Send "typing..." message to Whatsapp
                $this->whatsApp->sendMessageComposing($target);

                // Pretend to type for two seconds
                sleep($this->getTimeoutSeconds());

                // Pretend to Pause for sometime..
                $this->whatsApp->sendMessagePaused($target);

                // Respond Back
                $this->whatsApp->sendMessage($target, $response['message']);
                break;

            default:
                # code...
                break;
        }

        return true;
    }

    /*************
     *  HELPERS  *
     ************/

    private function getNumberFromWhatsApp($from)
    {
        $phoneArray = explode('@', $from);
        $phone = $phoneArray[0];

        return $phone;
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
    private function log($data, $type = null)
    {
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

            if ($type != null) {
                $http = 'POST /log/'.$type." HTTP/1.1\r\n";
            } else {
                $http = "POST /log/chat_log HTTP/1.1\r\n";
            }

            $http .= "Host: localhost\r\n";
            $http .= "Content-Type: application/json\r\n";
            $http .= 'Content-length: '.strlen($post_data)."\r\n";
            $http .= "Connection: close\r\n\r\n";
            $http .= $post_data."\r\n\r\n";

            //Sends are header data to the web server
            fwrite($socket, $http);
            //Close are request or the connection will stay open untill are script has completed.
            fclose($socket);
        }

        return true;
    }

    private function getTimeoutSeconds()
    {
        return (int)rand(2, 5);
    }
}
