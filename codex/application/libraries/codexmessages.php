<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Message:: a class for writing feedback message information to the session
*
* Copyright 2006 Vijay Mahrra & Sheikh Ahmed <webmaster@designbyfail.com>
*
* See the enclosed file COPYING for license information (LGPL).  If you
* did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
*
* @author  Vijay Mahrra & Sheikh Ahmed <webmaster@designbyfail.com>
* @url http://www.designbyfail.com/
* @version 1.0
*/

class CodexMessages
{
    var $_ci;
    var $_types = array('success', 'failure', 'info');

    function CodexMessages($params = array())
    {
        $this->_ci =& get_instance();
        // check if theres already messages, if not, initialise the messages array in the session
        $messages = $this->_ci->codexsession->userdata('messages');
        if (empty($messages)) {
            $this->clear();
        }
    }

    // clear all messages
    function clear()
    {
        $messages = array();
        foreach ($this->_types as $type) {
            $messages[$type] = array();
        }
        $this->_ci->codexsession->set_userdata('messages', $messages);
    }

    // add a message, default type is message
    function add($type, $message)
    {
        $messages = $this->_ci->codexsession->userdata('messages');

        //is this a new type?
        if (!in_array($type, $this->_types)) {
            $this->_types[] = $type;
        }
        $messages[$type][] = $message;
        $messages = $this->_ci->codexsession->set_userdata('messages', $messages);
    }

    // return messages of given type or all types, return false if none
    function sum($type = null)
    {
        $messages = $this->_ci->codexsession->userdata('messages');
        if (!empty($type)) {
            $i = count($messages[$type]);
            return $i;
        }
        $i = 0;
        foreach ($this->_types as $type) {
            $i += count($messages[$type]);
        }
        return $i;
    }

    // return messages of given type or all types, return false if none, clearing stack
    function get($type = null)
    {
        $return = array();
        $messages = $this->_ci->codexsession->userdata('messages');
        if (!empty($type)) {
            if (count($messages[$type]) == 0) {
                return false;
            }
            return $messages[$type];
        }

        // order return by order of type array above
        // i.e. success, failure, info and then informational messages last
        foreach ($this->_types as $type) {
            if(count($messages[$type]) > 0)
                $return[$type] = $messages[$type];
        }
        $this->clear();
        return $return;
    }
}

