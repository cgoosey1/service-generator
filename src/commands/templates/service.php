<?php namespace Services\%Name%;

use Repositories\%Name%\%Name%Interface;

class %Name%Service
{
    protected $%name%Repo;
    
    public function __construct(%Name%Interface $%name%Repo)
    {
        $this->%name%Repo = $%name%Repo;
    }
}
