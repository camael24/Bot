<?php
    /**
     * Created by JetBrains PhpStorm.
     * User: Julien
     * Date: 30/05/13
     * Time: 11:08
     * To change this template use File | Settings | File Templates.
     */
    namespace Hoathis\Bot\Command {
        class Log extends Generic implements Iface {
            private $_inProgress = false;
            private $_logAuthor = null;
            private $_logSubject = null;
            private $_log = array();

            public function getCommandName () {
                return 'log';
            }

            public function getCommandDescription () {
                return 'To log all conversation';
            }

            protected function isLogging () {
                return ($this->_inProgress === true);
            }

            protected function addLog ($user, $message) {
                if($this->isLogging() === true)
                    $this->_log[] = date('[Y/m/d H:i:s]') . ' ' . $user . ' :' . $message;
            }

            public function command ($user, $id, $argument) {
                if($this->isLogging() === true and $this->_logAuthor !== $user)
                    $this->_bot->sayPrivate($user, 'You are not the author of the logging , you can\'t interect with it');

                if($user === null)
                    return;

                switch ($id) {
                    case 'start':
                        if($this->_inProgress === false && $this->_logAuthor === null) {
                            $this->_log        = array();
                            $this->_inProgress = true;
                            $this->_logAuthor  = $user;
                            $this->_logSubject = $argument;
                            $this->_bot->sayPublic('An logging session are in progress by ' . $this->_logAuthor . ' with subject ' . $this->_logSubject);
                            $this->addLog($this->_bot->getUser(), 'Start log by ' . $this->_logAuthor . ' with subject of ' . $argument . '');
                        }
                        else {
                            $this->_bot->sayPrivate($user, 'An logging session are in progress or in pause by ' . $this->_logAuthor . ' with subject ' . $this->_logSubject);
                        }
                        break;
                    case 'stop':
                        $this->_inProgress = false;
                        $this->_logAuthor  = null;
                        $this->_logSubject = null;
                        $this->addLog($this->_bot->getUser(), 'Stop log by ' . $this->_logAuthor);
                        $this->_bot->sayPublic('The logging session are stopped');
                        break;
                    case 'pause':
                        $this->_inProgress = false;
                        $this->addLog($this->_bot->getUser(), 'Session are paused');
                        $this->_bot->sayPublic('The logging session are paused');
                        break;
                    case 'resume':
                        $this->_inProgress = true;
                        $this->addLog($this->_bot->getUser(), 'Session are resumed');
                        $this->_bot->sayPublic('The logging session are resumed , by ' . $this->_logAuthor . ' with subject ' . $this->_logSubject);
                        break;
                    case 'show':
                        foreach ($this->_log as $log)
                            $this->_bot->sayPrivate($user, $log);
                        break;
                    case 'subject':
                        $old               = $this->_logSubject;
                        $this->_logSubject = $argument;
                        $this->addLog($this->_bot->getUser(), 'Change subject of ' . $argument);
                        $this->_bot->sayPublic('Change subject ' . $old . ' by ' . $this->_logSubject);
                        break;
                    case 'reset':
                        $this->_log        = array();
                        $this->_inProgress = false;
                        $this->_logAuthor  = null;
                        break;
                    case 'save':
                        if($this->_logAuthor !== $user) {
                            $this->_bot->sayPrivate($user, 'You can\'t save this log , you are not the author');

                            return;
                        }

                        $input = '';
                        foreach ($this->_log as $log)
                            $input .= $log . "\n";

                        $address = 'paste.hoa-project.net:80';
                        $server  = stream_socket_client('tcp://' . $address, $errno, $errstr, 30);
                        if(false == $server) {
                            echo 'Cannot connect to the server.', "\n";

                            return 1;
                        }

                        $request = 'POST / HTTP/1.1' . "\r\n" . 'Host: ' . $address . "\r\n" . 'Content-Type: text/plain' . "\r\n" . 'Content-Length: ' . strlen($input) . "\r\n\r\n" . $input;

                        if(-1 === stream_socket_sendto($server, $request)) {

                            echo 'Pipe is broken, cannot write data.', "\n";

                            return 2;
                        }

                        $response = stream_socket_recvfrom($server, 1024);
                        list($headers, $body) = explode("\r\n\r\n", $response);

                        $this->_bot->sayPublic('The log are saved ' . trim($body));

                        break;

                    case 'status':
                        if($this->isLogging()) {
                            $this->_bot->sayPrivate($user, 'An logging session are in progress');
                        }
                        else {
                            if($this->_logAuthor === null)
                                $this->_bot->sayPrivate($user, 'No logging session');
                            else
                                $this->_bot->sayPrivate($user, 'An loggin session are in suspend');
                        }
                    case 'help':
                    default:
                        $command = array(
                            'start <subject>' => 'To start the logging session with an optionnal subject',
                            'stop '           => 'To stop the logging session',
                            'pause'           => 'To set in pause the logging session',
                            'resume'          => 'To resume the logging session',
                            'subject'         => 'To change the subject logging session',
                            'show'            => 'To show the logging session',
                            'status'          => 'To get the logging session status'
                        );
                        foreach ($command as $cmd => $txt)
                            $this->_bot->sayPrivate($user, '!log ' . $cmd . ' : ' . $txt);


                }
            }

            public function listen ($eventID, $argument) {
                if(count($argument) === 2)
                    list($user, $message) = $argument;

                switch ($eventID) {
                    case 'message':
                        if($this->isLogging() === true)
                            $this->addLog($user, $message);
                        break;
                    case 'new-user':
                        if($this->isLogging() === true) {
                            if(strpos($user, ':') === 0)
                                $user = substr($user, 1);
                            $this->addLog($this->_bot->getUser(), $user . ' has join the channel ' . $this->_bot->getChannel());
                            $this->_bot->sayPrivate($user, 'Hello ' . $user . ' an logging session are in progress by ' . $this->_logAuthor . ' with subject ' . $this->_logSubject);
                        }
                        break;
                    case 'part-user':
                        if($this->isLogging() === true) {
                            if(strpos($user, ':') === 0)
                                $user = substr($user, 1);
                            $this->addLog($this->_bot->getUser(), $user . ' has left the channel ' . $this->_bot->getChannel());
                        }
                    default:


                }
            }


        }
    }
