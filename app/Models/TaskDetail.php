<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDetail extends Model
{
    use HasFactory;
    protected $table = 'tbl_task_details';
    protected $primaryKey = 'tbl_task_detail_id';
    public $timestamps = false;
}
