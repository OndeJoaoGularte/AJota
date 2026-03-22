<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
    * Proteção contra Mass Assignment (Atribuição em Massa).
    * O Laravel bloqueia salvar dados enviados de formulários por segurança.
    * Ao preencher o $fillable, dizemos explicitamente quais colunas 
    * permitimos que sejam preenchidas de uma só vez usando o comando create().
    **/
    protected $fillable = ['description', 'amount', 'type', 'date'];
}

?>