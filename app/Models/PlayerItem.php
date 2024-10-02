<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerItem extends Model
{
    use HasFactory;
    /**
     * 指定したプレイヤーが、指定したアイテムを持っている個数を返す
     * 
     * @param int playerid,itemid
     * @return int アイテムの個数
     */
    public function playerItemGet($playerId,$itemId){
        return(
        PlayerItem::query()->
        where('itemid',$itemId)->
        where('playerid',$playerId)->
        first());
    }

    /**
     * playeritemsのレコードを追加する
     * 
     * @param int playerId,itemId,itemcount
     */
    public function playerItemCreate($playerId,$itemId,$itemcount)
    {
        PlayerItem::query()->
        insert([
        'playerid'=>$playerId,
        'itemid' => $itemId,
        'itemcount' => $itemcount
        ]);
    }

    /**
     * playeritemsのレコードを更新する
     * 
     * @param int playerId,itemId,itemcount
     */
    public function playerItemUpdate($playerId,$itemId,$itemcount)
    {
        PlayerItem::query()->
        where('playerid',$playerId)->
        where('itemid',$itemId)->
        update([
        'playerid' => $playerId,
        'itemid' => $itemId,
        'itemcount' => $itemcount
        ]);
    }

    /**
     * playerItemのレコードを削除する
     * 
     * @param int playerId,itemId
     */
    public function playerItemDelete($playerId,$itemId){
        PlayerItem::query()->
        where('playerid',$playerId)->
        where('itemid',$itemId)->
        delete();
    }

    /**
     * playeritemsに指定したレコードが存在するか返す
     * 
     * @param int playerId,itemId
     * @return bool
     */
    public function playerItemExists($playerId,$itemId)
    {
        return(
        PlayerItem::query()->
        where('playerid',$playerId)->
        where('itemid',$itemId)->
        exists());
    }
}
