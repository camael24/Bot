<?php
    /**
     * Created by JetBrains PhpStorm.
     * User: Julien
     * Date: 30/05/13
     * Time: 09:37
     * To change this template use File | Settings | File Templates.
     */
    namespace Hoathis\Bot\Command {
        class Generic {
            protected $_bot;

            final public function __construct (\Hoathis\Bot\Connect $bot) {
                $this->_bot = $bot;
            }



        }
    }
