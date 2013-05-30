<?php
    /**
     * Created by JetBrains PhpStorm.
     * User: Julien
     * Date: 30/05/13
     * Time: 11:08
     * To change this template use File | Settings | File Templates.
     */
    namespace Hoathis\Bot\Command {
        class Help extends Generic implements Iface {
            public function getCommandName () {
                return 'help';
            }

            public function getCommandDescription () {
                return 'To get this screen';
            }


            public function command ($user, $id, $argument) {

                $module = $this->_bot->getModule();
                foreach ($module as $m) {
                    if($m instanceof \Hoathis\Bot\Command\Iface)
                        $this->_bot->sayPrivate($user, '!' . $m->getCommandName() . ': ' . $m->getCommandDescription());
                }

            }

            public function listen ($eventID, $argument) {

            }


        }
    }
