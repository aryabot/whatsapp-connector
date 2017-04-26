<?php

/*********************************************
 *
 *  Here's how this is gonna work:
 *
 *  The whole thing will run as a daemon, with
 *  a single instance of all classes. This not
 *  only reducs memory consumption, but also
 *  makes things faster.
 *
 *  The program does not have to create new
 *  variables and we don't have to worry
 *  about memory leaks and stuff!
 *
 ********************************************/

require 'WhatsAppAPI.php';

$whatsapp = new WhatsAppAPI();
$whatsapp->getMessages();
