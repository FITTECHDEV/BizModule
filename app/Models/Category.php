<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Category extends Model
{use Cachable;
    use HasFactory;

    protected $table = 'Cust_Category';

    protected $guarded = [];

    const DELETED_AT = 'DeletedOn';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'LastModifiedOn';

    /**
     * category
     */
    public function getCategoryType($criteria= null, $category = null)
    {
        if ($category && $criteria) {
            return $this->where($criteria, $category)->orderBy('CategorySeq', 'ASC')->get();
        }else {
            return $this->all();
        }
    }
}
