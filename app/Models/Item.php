<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;
    use HasFactory;

    /**
     * 指定したアイテムデータのレコードを１件を返す
     * 
     * @param int itemId
     * @return int value
     */
    public function itemGet($itemId)
    {
        return(
        Item::query()->
        where('id',$itemId)->
        first());
    }

    /**
     * アイテムのデータを全件取得する
     * 
     * @return アイテムのデータ全件
     */
    public function itemIndex(){
    return (
        Item::query()->
        get());
    }
}
