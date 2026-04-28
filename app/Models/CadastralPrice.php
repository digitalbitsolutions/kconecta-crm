<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CadastralPrice extends Model
{
    protected $fillable = [
        'province', 'municipality', 'neighborhood',
        'postal_code', 'price_m2_eur', 'import_batch_id'
    ];
}
