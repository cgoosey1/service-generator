<?php namespace Repositories\%Name%;

use Illuminate\Database\Eloquent\Model;

class %Name%Repository implements %Name%Interface
{
    protected $%name%;
    
    public function __construct(Model $%name%)
    {
        $this->%name% = $%name%;
    }
}
