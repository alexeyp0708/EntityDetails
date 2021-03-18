<?php


namespace Alpa\EntityDetails;


class PropertyNameGenerator
{
    const FINAL_LABEL='***';
    const PRIVATE_LABEL='**';
    const PROTECTED_LABEL='*';
    const PUBLIC_LABEL='';
    const ABSTRACT_LABEL='~';
    const STATIC_LABEL='%';
    
    protected string $name;
    protected string $p1='';// abstract
    protected string $p2='';// static
    protected string $p3='';// public|protected|private|final
    public function __construct($name)
    {
        $this->name=$name;
    }
    public function setTypes($types): PropertyNameGenerator
    {
        if(is_string($types)){
            $types=explode($types,'|');
        }
        foreach($types as $type){
            $position=null;
            switch ($type){
                case 'final':
                case 'private':
                case 'protected':
                case 'public':
                    $position='p3';
                break;
                case 'static':
                    $position='p2';
                break;
                case 'abstract':
                    $position='p1';
                break;
            }
            if($position!==null){
                $type=mb_strtoupper($type);
                $this->$position=constant(static::class.'::'.$type.'_LABEL');
            }
        } 
        return $this;
    }
    public function getName():string
    {
        $prefix="{$this->p1}{$this->p2}{$this->p3}";
        if(strlen($prefix)>0){
            $prefix.=' ';
        }
        return "{$prefix}{$this->name}";
    }
}