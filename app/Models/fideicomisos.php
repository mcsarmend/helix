<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fideicomisos extends Model
{
    use HasFactory;
    protected $table = 'fideicomisos';

	protected $primary_key = 'id';

	public $timestamps = false;

	protected $fillable = [
		'id',
		'nombre',
	];

	protected $guarded =[];
}
