<?php
    namespace Hoathis\Bot {
        from('Hoa')
            ->import('Socket.Client')
        ->import('Irc.Client');
        /**
         * Class Connect
         *
         * @package Hoathis\Bot
         */
        class Connect {
            /**
             * @var null
             */
            private $_user = null;
            /**
             * @var null
             */
            private $_channel = null;
            /**
             * @var \Hoa\Irc\Client
             */
            private $_client = null;

            private $_module = array();

            /**
             * @param null $channel
             */
            public function setChannel ($channel) {
                $this->_channel = $channel;
            }

            /**
             * @return null
             */
            public function getChannel () {
                return $this->_channel;
            }

            /**
             * @param null $client
             */
            public function setClient ($client) {
                $this->_client = $client;
            }

            /**
             * @return null
             */
            public function getClient () {
                return $this->_client;
            }


            /**
             * @param null $user
             */
            public function setUser ($user) {
                $this->_user = $user;
            }

            /**
             * @return null
             */
            public function getUser () {
                return $this->_user;
            }

            /**
             * @param $raw
             */
            public function sendCommand ($raw) {
                $this->_client->send($raw);
            }

            /**
             * @param $message
             */
            public function sayPublic ($message) {
                $this->_client->say($message , $this->getChannel());
            }

            /**
             * @param $user
             * @param $message
             */
            public function sayPrivate ($user, $message) {
                $raw = $user . ': ' . $message;
                $this->_client->say($raw, $user);
            }

            /**
             * @param     $log
             * @param int $lvl
             */
            public function log ($log, $lvl = 0) {
                echo $log . "\n";
            }

            /**
             * @param $user
             * @param $message
             */
            public function message ($user, $message) {

                $message = trim($message);

                $botName = $this->getUser();
                if(strpos($message, $botName . ':') === 0) {
                    $command = trim(substr($message, strlen($botName) + 1));
                    $this->fire('mentions', func_get_args());
                    $this->_commandMessage($user, $command);
                }
                else if($this->isCommand($message)) {
                    $this->_commandMessage($user, $message);
                }
                else {
                    $this->fire('message', func_get_args());
                }

            }

            protected function _commandMessage ($user, $command) {
                if($this->isCommand($command)) {
                    $list     = substr($command, 1);
                    $list     = explode(' ', $list);
                    $id       = array_shift($list);
                    $command  = null;
                    $argument = null;
                    if(count($list) > 0)
                        $command = array_shift($list);
                    if(count($list) > 0)
                        $argument = implode(' ', $list);

                    if($this->isAttach($id))
                        $this->command($id, $user, $command, $argument);

                }
            }

            protected function isCommand ($message) {
                return (strpos($message, '!') === 0);

            }

            /**
             * @param $status
             */
            public function status ($status) {


                $list = explode(' ', $status);
                if(count($list) > 2) {
                    $from = array_shift($list);
                    $code = array_shift($list);
                    $rest = implode(' ', $list);

                    switch ($code) {
                        case 'JOIN':
                            $user = $this
                                    ->getClient()
                                    ->parseNick($from);
                            $this->fire('new-user', array(
                                                         $user['nick'],
                                                         ''
                                                    )
                            );
                            break;
                        case 'QUIT':
                        case 'PART':
                            $user = $this
                                    ->getClient()
                                    ->parseNick($from);
                            $this->fire('part-user', array(
                                                          $user['nick'],
                                                          ''
                                                     )
                            );
                            break;
                        default:
                    }

                }
                $this->fire('status', func_get_args());
            }

            public function attach (\Hoathis\Bot\Command\Iface $module) {
                $name = $module->getCommandName();
                if(!$this->isAttach($name))
                    $this->_module[$name] = $module;


            }

            public function getModule () {
                return $this->_module;
            }

            protected function isAttach ($id) {
                return array_key_exists($id, $this->_module);
            }

            protected function fire ($eventID, $array) {
                foreach ($this->_module as $module)
                    if($module instanceof \Hoathis\Bot\Command\Iface)
                        $module->listen($eventID, $array);
            }

            protected function command ($listener, $user, $command, $argument) {

                if($this->isAttach($listener)) {
                    $listener = $this->_module[$listener];
                    $listener->command($user, $command, $argument);

                }
            }


            /**
             * @param $server
             */
            public function run ($server) {
                $client = new \Hoa\Irc\Client(new \Hoa\Socket\Client($server));
                $bot    = $this;

                $this->setClient($client);
                $client->on('open', function ($bucket) use (&$bot) {
                        $bucket
                        ->getSource()
                        ->join($bot->getUser(), $bot->getChannel());

                        return;
                    }
                );

                $client->on('join', function ($bucket) use (&$bot) {
                        $channel = $bucket->getData();
                        $bot->log('======= JOIN ' . $channel['channel'] . ' =====');
                        $bot->sendCommand('WHO ' . $channel['channel']);

                    }
                );

                $client->on('message', function ($bucket) use (&$bot) {

                        $data    = $bucket->getData();
                        $user    = $data['from']['nick'];
                        $message = $data['message'];

                        $bot->message($user, $message);
                        $bot->log($user . ': ' . $message);
                    }
                );

                $client->on('ping', function ($bucket) use (&$bot) {
                        $data = $bucket->getData();
                        $bot->log('=== PING from ', $data['daemons'][0]);
                    }
                );

                $client->on('other-message', function ($bucket) use (&$bot) {
                        $data = $bucket->getData();
                        $bot->status($data['line']);
                        $bot->log($data['line']);

                    }
                );

                $client->on('error', function ($bucket) use (&$bot) {
                        $data = $bucket->getData();
                        $bot->log('Error > ' . $data['exception']->raise(true));
                    }
                );

                $client->run();

            }

        }
    }
