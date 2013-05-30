<?php
    /**
     * Created by JetBrains PhpStorm.
     * User: Julien
     * Date: 30/05/13
     * Time: 11:08
     * To change this template use File | Settings | File Templates.
     */
    namespace Hoathis\Bot\Command {
        class Record extends Generic implements Iface {
            public function getCommandName () {
                return 'record';
            }

            public function command ($user, $id, $argument) {
            }


            public function listen ($command, $argument) {

            }


        }
    }
