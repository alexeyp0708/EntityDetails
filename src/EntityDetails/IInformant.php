<?php


namespace Alpa\EntityDetails;


use Alpa\ProxyObject\Proxy;

interface IInformant
{
    //public function  __construct($observed,$is_recursive=false);
    static public function newObject(&$observed,bool $is_recursive=false):object; //php 8 IInformant|Proxy
}