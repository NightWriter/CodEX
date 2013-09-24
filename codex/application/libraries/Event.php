<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Event Handler
 * 
 * Implements a basic event handling system into CI and exposes an event library
 * class to be used when triggering and registering events.
 * 
 * @author      David Cole <neophyte@sourcetutor.com>
 * @version     0.1
 * @copyright   2008
 */

if (!defined('KH_EVENT_HANDLER'))
{    
    /*
     * CI uses require instead of require_once when including libraries
     * this causes issues as this file is needed at the pre_system stage
     * so this simple check stops the classes being defined twice.
     */
    
    define('KH_EVENT_HANDLER', true);    
    
    /**
     * Event Library Class
     * 
     * Acts as a CI Library wrapper to the KH_Dispatcher class
     * exposing the register and trigger methods.
     */
    class Event
    {
        /**
         * Event Dispatcher
         *
         * @var    object
         * @access private
         */
        var $_Dispatcher;
           
        /**
         * Class Constructor
         *
         * @return Event
         */
        function Event()
        {
            $this->_Dispatcher =& KH_Dispatcher::getInstance();
        }        
        
        /**
         * Register Event
         *
         * @param string $event
         * @param mixed  $handler  function or an instance of an KH_Event object.
         */
        function register($event, $handler)
        { 
    		$this->_Dispatcher->Register($event, $handler);
        }
        
        /**
         * Trigger Event
         *
         * @param string $event  Event to be triggered
         * @param array  $args   Arguments to be passed to the handler
         * 
         * @return array  Array of results received from the called event handlers
         */
        function trigger($event, $args = null)
        {
    		return $this->_Dispatcher->Trigger($event, $args);
        }
    }
    
    /**
     * Observable
     *
     * Implements the observable part of the
     * observer design pattern.
     * 
     */
    class KH_Observable
    {
    	/**
    	 * An array of Observer objects to notify
    	 *
    	 * @access private
    	 * @var array
    	 */
    	var $_observers = array();
    
    	/**
    	 * The state of the observable object
    	 *
    	 * @access private
    	 * @var mixed
    	 */
    	var $_state = null;    
        
        /**
         * Constructor
         *
         * @return KH_Event_Observable
         */
        function KH_Observable()
        {
            $this->_observers = array();
        }
        
        /**
         * Get Observable State
         *
         * @return mixed
         */
        function GetState()
        {
            return $this->_state;
        }
        
    	/**
    	 * Notify Observers
    	 * 
    	 * Update each attached observer object and return an array
    	 * of their return values
    	 *
    	 * @return array Array of return values from the observers
    	 */
    	function Notify()
    	{
    	    $results = array();
    	    
    		foreach ($this->_observers as $observer)
    			$results[] = $observer->Update();
    		
    		return $results;
    	}
    
    	/**
    	 * Attach Observer
    	 *
    	 * @param object $observer An observer object to attach
    	 */
    	function Attach(&$observer)
    	{
    		if (is_object($observer)) // Observer Object
    		{ 
    			$class = get_class($observer);
    			
    			foreach ($this->_observers as $check)
    				if (is_a($check, $class))
    					return;
    
    			$this->_observers[] =& $observer;			
    		}
    		else // Callable Function
    			$this->_observers[] =& $observer;		
    	}
    
    	/**
    	 * Detach Observer
    	 *
    	 * @param object $observer
    	 * 
    	 * @return boolean 
    	 */
    	function Detach($observer)
    	{
    	    if (($key = array_search($observer, $this->_observers)) !== false)
    	    {
    	        unset($this->_observers[$key]);
    	        return true;
    	    }
    	    else 
    	       return false;
    	}    
    }    

    /**
     * Observer
     * 
     * Implements the observer part of the observer
     * design pattern
     *
     */
    class KH_Observer
    {
    	/**
    	 * Event object to observe
    	 *
    	 * @access private
    	 * @var object
    	 */
    	var $_subject = null;
    
    	/**
    	 * Constructor
    	 */
    	function KH_Observer(&$subject)
    	{
    		// Register the observer ($this) so we can be notified
    		$subject->Attach($this);
    
    		// Set the subject to observe
    		$this->_subject = & $subject;
    	}
    
    	/**
    	 * Method to update the state of observable objects
    	 *
    	 * @abstract Implement in child classes
    	 * @access public
    	 * @return mixed
    	 */
    	function Update()
    	{
    		false;
    	}
    }      
    
    /**
     * Dispatcher
     * 
     * Provides a common class instance through use of a singleton
     * from which events are triggered and registered.
     *
     */
    class KH_Dispatcher extends KH_Observable
    {
        /**
         * Constructor
         *
         * @return KH_Event_Dispatcher
         */
        function KH_Dispatcher()
        {
            parent::KH_Observable();
        }
        
    	/**
    	 * Singleton
    	 * 
    	 * Returns a reference to the global Event Dispatcher object, only creating it
    	 * if it doesn't already exist.
    	 *
    	 * @access	public
    	 * @return	KH_Event_Dispatcher
    	 */
    	function &getInstance()
    	{
    		static $instance;
    
    		if (!is_object($instance))
    			$instance = new KH_Dispatcher();
    
    		return $instance;
    	}
    	
    	/**
    	 * Register Event
    	 *
    	 * @param string $event
    	 * @param mixed  $handler  Name of the event handler or a callable function/method
    	 * 
    	 * @return bool
    	 */
    	function Register($event, $handler)
    	{
    	    if (is_string($handler) && class_exists($handler))
    	    {
    	        $this->Attach(new $handler($this));
    	        return true;
    	    }
    	    else if (is_callable($handler))
    	    {
    	        $observer = array('event' => $event, 'handler' => $handler);
    	        $this->Attach($observer);
    	        
    	        return true;
    	    }
    	    else 
    	        return false;
    	}
    	
    	/**
    	 * Trigger Event
    	 *
    	 * @param string $event  Event to be triggered
    	 * @param array  $args   Array of args to be passed to each of the event handlers
    	 * 
    	 * @return array Array of results received from the called event handlers, false on error
    	 */
    	function Trigger($event, $args = null)
    	{    	   
    		$results = array ();
    
    		if ($args === null)
    			$args = array ();

    		/*
    		 * Iterate over the registered observers triggering the event
    		 * for each observer that handles the event.
    		 */
    		foreach ($this->_observers as $observer)
    		{
    			if (is_array($observer))
    			{
                    /*
                     * Observer in this case is simply a callable function
                     * or class method.
                     */
    				if ($observer['event'] == $event)
    				{
                        $results[] = call_user_func_array($observer['handler'], $args);
    				}
    				else
    					continue;
    			}
    			else if (is_object($observer) && method_exists($observer, 'update'))
    			{
    				/*
    				 * Observer is setup extending the KH_Observer class.
    				 */
       
                    if (method_exists($observer, $event))
                    {
                        $args['event'] = $event;
                        $results[] = $observer->Update($args);
                    }
                    else
                        continue;
    			}
    			else 
    			    return false;
    		}
    		
    		return $results;	    
    	}
    }
    
    /**
     * Plugin
     * 
     * Upon an event being triggered this class ensures
     * the correct method within the observer object is called.
     *
     */
    class KH_Plugin extends KH_Observer
    {
        /**
         * Constructor
         *
         * @param object $subject  Object to be observed
         * 
         * @return KH_Event
         */
    	function KH_Plugin(&$subject)
    	{
    		parent::KH_Observer($subject);
    	}
    
    	/**
    	 * Trigger Event
    	 *
    	 * @param array Arguments
    	 * 
    	 * @return mixed Routine return value
    	 */
    	function Update(&$args)
    	{
    		/*
    		 * Retrieve the name of the event to be triggered from
    		 * the supplied args.
    		 */
    		
    		$event = $args['event'];
    		unset($args['event']);
    
            // Trigger event
            
    		if (method_exists($this, $event))
    			return call_user_func_array (array(&$this, $event), $args);
    		else
    			return null;	
    	} 
    }    
}

?>
