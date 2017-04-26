<?php

require 'vendor/autoload.php';

require 'EventsHandler.php';
require 'AryabotAPI.php';

class WhatsAppAPI
{
    private $env;
    private $config;
    private $whatsApp;
    private $aryabot;
    private $events;

    public function __construct()
    {
        // load the config
        require 'env.php';
        $this->env = $env;

        $this->config = [
            'username' => $this->env['whatsapp']['username'],
            'password' => $this->env['whatsapp']['password'],
            'nickname' => $this->env['whatsapp']['nickname'],
            'debug' => $this->env['whatsapp']['debug'],
        ];

        // Load all the importat classes and create an Instance of them
        $this->whatsApp = new WhatsProt($this->config['username'], $this->config['nickname'], $this->config['debug']);
        $this->events = new EventsHandler($this->whatsApp);
        $this->aryabot = new AryabotAPI();

        // Initialize the events listner
        $this->events->init($this->aryabot, $this->whatsApp);
        $this->events->setEventsToListenFor($this->events->activeEvents);

        // Finally, Connect to the WhatsApp Service!
        $this->whatsApp->connect();
        $this->whatsApp->loginWithPassword($this->config['password']);
    }

    public function getMessages()
    {
        while (1) {
            $this->whatsApp->pollMessage();
        }
    }
}
