<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logenviosinterfaces extends Model
{
    use HasFactory;
    protected $table = 'logenviosinterfaces';

	protected $primary_key = 'id';

	public $timestamps = false;

	protected $fillable = [
		'id',
        'fecha',
        'resenvio'
	];

	protected $guarded =[];
}
