<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public $timestamps = false;
    use HasFactory;

    /**
     * プレイヤーのidとnameを全件取得する
     * 
     * @return 全プレイヤーのidとname
     */
    public function playerIndex()
    {
        return (
        Player::query()->
        select(['id', 'name'])->
        get());
    }

    /**
     * プレイヤーを1件取得
     */
    public function playerShow($id) {
        return (Player::query()where('id', $id)->first());
    }

    /**
     * プレイヤーのレコードを更新する
     * 
     * @param int id,name,hp,mp,money
     */
    public function playerUpdate($id,$name,$hp,$mp,$money)
    {
            Player::query()->
            where('id',$id)->
            update(
                [
                    'name' => $name,
                    'hp' => $hp,
                    'mp' => $mp,
                    'money' => $money
                ]
            );
    }

    /**
     * 新規プレイヤーのレコードを作成し、idを返す
     * 
     * @param int name,hp,mp,money
     * @return 新規プレイヤーのid
     */
    public function playerCreate($name,$hp,$mp,$money)
    {
       
            return(
                Player::query()->
                insertGetId(
                [
                    'name'=>$name,
                    'hp' => $hp,
                    'mp' => $mp,
                    'money' => $money
                ]
            ));
    }

    /**
     * idで指定したプレイヤーのレコードを削除する
     * 
     * @param int id
     */
    public function playerDestroy($id)
    {
                Player::query()->
                where('id',$id)->
                delete();
    } 

    /**
     * idで指定したプレイヤーのレコードを１件取得する
     * 
     * @param int id
     * @return プレイヤー情報のレコード１件
     */
    public function playerGet($id)
    {
        return(
        Player::query()->
        where('id',$id)->
        first());
    }

    /**
     * idで指定したプレイヤーのレコードを１件取得する
     * 
     * @param int id
     * @return プレイヤー情報のレコード１件
     */
    public function txPlayerGet($id)
    {
        return(
        Player::query()->
        where('id',$id)->
        lockForUpdate()->
        first());
    }
}
