<?php
    /**
     * Created by JetBrains PhpStorm.
     * User: Julien
     * Date: 30/05/13
     * Time: 09:37
     * To change this template use File | Settings | File Templates.
     */

    namespace Hoathis\Bot\Command {


        interface Iface {

            public function getCommandName ();

            public function getCommandDescription ();

            public function listen ($command, $argument);

            public function command ($user, $id, $argument);
        }
    }