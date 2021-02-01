<?php

namespace App\Models\Old\News;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CorpNews extends Model
{
    const PER_PAGE = 7;
    protected $connection = 'sqlsrv';
    protected $table = 'main_corp_news';
    protected $primaryKey = 'id';

    public function scopePublished(Builder $query, $limit = false, $offset = 0)
    {
        $query = $query->where('date_end', '>', time())
            ->orWhere('date_end', 0)
            ->orderBy('date', 'desc');

        if ($limit) {
            $query = $query->limit($limit)
                ->offset($offset);
        }

        return $query;
    }
}