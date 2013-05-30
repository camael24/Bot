<?php

    namespace Hoathis\Bot\Bin {

        from('Hoathis')
            ->import('Bot.Connect')
        ->import('Bot.Command.*');

        class Command extends \Hoa\Console\Dispatcher\Kit {

            protected $options
                = array(
                    array(
                        'remote',
                        \Hoa\Console\GetOption::REQUIRED_ARGUMENT,
                        'r'
                    ),
                    array(
                        'user',
                        \Hoa\Console\GetOption::REQUIRED_ARGUMENT,
                        'u'
                    ),
                    array(
                        'channel',
                        \Hoa\Console\GetOption::REQUIRED_ARGUMENT,
                        'c'
                    ),
                    array(
                        'help',
                        \Hoa\Console\GetOption::NO_ARGUMENT,
                        'h'
                    ),
                    array(
                        'help',
                        \Hoa\Console\GetOption::NO_ARGUMENT,
                        '?'
                    )
                );


            /**
             * The entry method.
             *
             * @access  public
             * @return  int
             */
            public function main () {

                $user    = null;
                $channel = null;
                $server  = 'tcp://chat.freenode.org:6667';

                while (false !== $c = $this->getOption($v)) switch ($c) {
                    case 'u':
                        $user = $v;
                        break;
                    case 'c':
                        $channel = $v;
                        break;
                    case 'r':
                        $server = $v;
                        break;
                    case 'h':
                    case '?':
                        return $this->usage();
                        break;

                }

                if($user === null or $channel === null)
                    return $this->usage();

                echo 'Connect on ' . $server . ' ' . $user . ' ' . $channel . "\n";


                $bot = new \Hoathis\Bot\Connect();
                $bot->setChannel($channel);
                $bot->setUser($user);

                $bot->attach(new \Hoathis\Bot\Command\Log($bot));
                $bot->attach(new \Hoathis\Bot\Command\Help($bot));

                $bot->run($server);

                return;
            }

            /**
             * The command usage.
             *
             * @access  public
             * @return  int
             */
            public function usage () {

                echo 'Usage   : hoathis:bot <options> ' . "\n";
                echo 'Options :' . "\n";
                echo $this->makeUsageOptionsList(array(
                                                      'remote'  => 'Address of dist server like tcp://chat.freenode.org:6667',
                                                      'channel' => 'The channel to join like #hoaproject',
                                                      'user'    => 'User',
                                                      'help'    => 'This help.'
                                                 )
                ), "\n";

                return;
            }
        }

    }

__halt_compiler();
Connect an bot on IRC Channel
