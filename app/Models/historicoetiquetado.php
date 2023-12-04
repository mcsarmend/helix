<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class historicoetiquetados extends Model
{
    use HasFactory;
    protected $table = 'historicoetiquetados';

	protected $primary_key = 'id';

	public $timestamps = false;

	protected $fillable = [
		'id',
        'fecha',
        'creditos',
        'idusuario',
        'fondeadoranterior',
        'fondeadornuevo',
        'sistema'
	];
}
