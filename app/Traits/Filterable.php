<?php

namespace App\Traits;

trait Filterable
{
    /**
     * Scope untuk pengurutan dinamis
     */
    public function scopeSort($query, $field, $direction)
    {
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'desc';
        // Gunakan created_at jika field tidak disediakan
        return $query->orderBy($field ?? 'created_at', $direction);
    }

    /**
     * Scope pencarian dasar (bisa di-override di masing-masing model jika butuh logika kompleks)
     */
    public function scopeSearch($query, $search, $columns = ['name'])
    {
        if (!$search)
            return $query;

        return $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }
}
