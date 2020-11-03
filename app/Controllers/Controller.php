<?php

namespace App\Controllers;
use Psr\Container\ContainerInterface;

 /**
 * Collection constructor
 *
 * @package BOARDS Forum
 * @since   0.1
 */


class Controller
{
   
   /**
   * The copy of application container
   *
   * @var object
   **/
	
	protected $container;
	
	/**
    * copy of main container
    *
    * @var object
    **/
	
    protected $property;
    
	/**
    * Default constructor of all controllers
    *
    * @param $config object of main container 
    * @return void
    **/
	
    public function __construct($container)
    {
        $this->container = $container;
    }
    
	/**
    *  Reading data from container and get property
    *
    * @param $property property name from container 
    * @return property
    **/
	
    public function __get($property)
    {
        if($this->container->get($property))
        {
            return $this->container->get($property);
        }
    }
	
}