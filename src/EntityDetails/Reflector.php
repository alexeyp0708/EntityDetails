<?php
/**
 * Created by PhpStorm.
 * User: AlexeyP
 * Date: 25.02.2021
 * Time: 22:54
 */

namespace Alpa\EntityDetails;


interface Reflector
{
    /**
     * @return string|false
     */
    public function getFileName();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return \ReflectionClass[]
     */
    public function getInterfaces();

    /**
     * @return \ReflectionClass[]
     */
    public function getTraits();

    /**
     * @return \ReflectionClass|false
     */
    public function getParentClass();

    /**
     * @return array
     */
    public function getConstants();

    /**
     * @return \ReflectionProperty[]
     */
    public function getDefaultProperties();
    
    /**
     * @return \ReflectionProperty[]
     */
    public function getProperties();


    /**
     * @return \ReflectionMethod []
     */ 
    public function getMethods();
    
    
    
}