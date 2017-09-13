<?php

namespace Point\PointExpedition\Models;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class ExpeditionOrderGroup extends Model
{
    public $timestamps = false;
    protected $table = 'point_expedition_order_group';

    use FormulirTrait;
}