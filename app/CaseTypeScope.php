<?php
/**
 * Created by PhpStorm.
 * User: phailee
 * Date: 17/6/16
 * Time: 6:37 PM
 */

namespace App;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;

class CaseTypeScope implements ScopeInterface
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $cases_type = Setting::where('field','cases_type')->first();
        if(count($cases_type)>0){
            $cases_type= explode(',',$cases_type->value);
            $builder->whereIn('jurisdiction', $cases_type);
        };

    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        // TODO: Implement remove() method.
    }
}