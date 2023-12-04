<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class historicoaforo extends Model
{
    use HasFactory;
    protected $table = 'historicoaforo';

	protected $primary_key = 'id';

	public $timestamps = false;

	protected $fillable = [
		'id',
        'fondeador',
        'fecha',
        'aforo'
	];

	protected $guarded =[];
}
