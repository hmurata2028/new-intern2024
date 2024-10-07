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
     * @param int playerId,itemId
     * @return int アイテムの個数
     */
    public function playerItemGet($playerId,$itemId) {
        return(
            PlayerItem::query()->
            where('item_id',$itemId)->
            where('player_id',$playerId)->
            first());
    }

    /**
     * playerItemsのレコードを追加する
     * 
     * @param int playerId,itemId,itemCount
     */
    public function playerItemCreate($playerId,$itemId,$itemCount) {
        PlayerItem::query()->
        insert([
            'player_id'=>$playerId,
            'item_id' => $itemId,
            'item_count' => $itemCount
        ]);
    }

    /**
     * playerItemsのレコードを更新する
     * 
     * @param int playerId,itemId,itemCount
     */
    public function playerItemUpdate($playerId,$itemId,$itemCount) {
        PlayerItem::query()->
        where('player_id',$playerId)->
        where('item_id',$itemId)->
        update([
            'player_id' => $playerId,
            'item_id' => $itemId,
            'item_count' => $itemCount
        ]);
    }

    /**
     * playerItemのレコードを削除する
     * 
     * @param int playerId,itemId
     */
    public function playerItemDelete($playerId,$itemId) {
        PlayerItem::query()->
        where('player_id',$playerId)->
        where('item_id',$itemId)->
        delete();
    }

    /**
     * playerItemsに指定したレコードが存在するか返す
     * 
     * @param int playerId,itemId
     * @return bool
     */
    public function playerItemExists($playerId,$itemId) {
        return(
            PlayerItem::query()->
            where('player_id',$playerId)->
            where('item_id',$itemId)->
            exists());
    }
}
