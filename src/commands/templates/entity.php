<?php namespace Entities;

use Eloquent;

class %Name% extends Eloquent
{
    // Our table name
    protected $table = "%table%";
    // Our primary key
    protected $primaryKey = "%primaryKey%";
    // Telling Laravel we don't want it to add created_at and updated_at columns
    public $timestamps = %timestamps%;
}