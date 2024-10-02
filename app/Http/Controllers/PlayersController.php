<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\PlayerItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;


class PlayersController extends Controller
{
    const ITEMTYPE_HP_POTION = 1;
    const ITEMTYPE_MP_POTION = 2;
    const STATUS_MAX_VALUE = 200;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $player = new Player();
        return new Response(
            $player -> playerIndex()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $player = new Player();

        try
        {
            $player->playerUpdate($id,$request->name,$request->hp,$request->mp,$request->money);
            return 'success';
        }
        catch(QueryException $e)
        {
            return 'error';
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $player = new Player();
        try
        {
            $player -> playerDestroy($id);
            return'success';
        }
        catch(QueryException $e)
        {
            return'error';
        }

        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $player = new Player();
        try
        {
            $newid = $player -> playerCreate($request -> name,
            $request -> hp,$request -> mp,$request -> money);
            return new Response(["id" => $newid]);
        }
        catch(QueryException $e)
        {
            return'error';
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * idで指定したプレイヤーのアイテムを増加させる
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function addItem($id,Request $request,Response $response)
    {
        //モデルのクラスを格納
        $playerItem = new PlayerItem();
        $player = new Player();
        $item = new Item();
        try
        {
            //プレイヤーデータの存在チェック
            $playerdata = $player->playerGet($id);
            if($playerdata == null)
            {
                throw new Exception('no playerdata');
            }
            
            //アイテムデータの存在チェック
            $itemdata = $item->itemGet($request -> itemId);
            if($itemdata == null)
            {
                throw new Exception('no itemdata');
            }

            //プレイヤーアイテムデータの存在チェック
            //存在していれば値を取得し、存在しなければレコードを追加する
            $playerItemdata = $playerItem -> playerItemGet($id, $request -> itemId);
            if($playerItemdata != null)
            {
                $itemcount = $playerItemdata -> itemcount;
                $itemcount = $request->count + $itemcount;
                $playerItem -> playerItemUpdate($id, $request -> itemId, $itemcount);
            }
            else
            {
                $playerItem -> playerItemCreate($id, $request -> itemId, $request -> count);
                
                $itemcount = $request -> count;
            }
            //増加したアイテムのitemIdと、その結果の現在の所持数を返す
            return new Response(["itemId" => $request -> itemId, "count" => $itemcount,
            "code" => $response -> status()]);
        }
        catch(Exception $e)
        {
            return ["message" => $e -> getMessage(),"code" => $response -> status()];
        }
    }
    
    /**
     * 指定したアイテムを使用し、効果によってプレイヤーのステータスを更新する
     * 
     * @param int id
     * @return \Illuminate\Http\Response
     */
    public function useItem($id,Request $request,Response $response)
    {
        //モデルのクラスを格納
        $player = new Player();
        $playerItem = new PlayerItem();
        $item = new Item();

        DB::beginTransaction();
        try
        {
            //プレイヤーデータの存在チェック
            $playerdata = $player->txPlayerGet($id);
            if($playerdata == null)
            {
                throw new Exception('no playerdata');
            }
            //プレイヤーのステータスを取得
            $hp = $playerdata["hp"];
            $mp = $playerdata["mp"];

            //指定したアイテムデータのレコードの存在チェック
            $itemdata = $item -> itemGet($request->itemId);
            if($itemdata == null)
            {
                throw new Exception('no itemdata');
            }

            //プレイヤーアイテムデータの存在チェック
            $playeritemdata = $playerItem->playerItemGet($id,$request->itemId);
            if($playeritemdata == null)
            {
                throw new Exception('error:400');
            }

            //アイテムの所持数を格納
            $itemcount = $playeritemdata["itemcount"];

            //アイテムの所持数が０個でないかチェック
            if($itemcount == 0)
            {
                throw new Exception('error:400');
            }
            //アイテムの使用数が所持数を超えていないかチェック
            else if($itemcount<$request->count)
            {
                throw new Exception('usecount exceeds playeritemcount');
            }
            //アイテムタイプによって変化するステータスを格納する
            if($itemdata->item_type == self::ITEMTYPE_HP_POTION)
            {
                $status = $hp;
            }
            else if($itemdata->item_type == self::ITEMTYPE_MP_POTION)
            {
                $status = $mp;
            }
            //アイテム毎の回復量を格納する
            $itemValue = $itemdata -> value;

            //ステータスが最大値でないかチェック
            if($status >= self::STATUS_MAX_VALUE)
            {
                throw new Exception('status is full');
            }

            //アイテムを使用する個数を判定
            //プレイヤーに指定された個数を使用すると、最大値を超えてしまう場合
            $useitemcount = 0;
            for($i = 0; $i < $request -> count; $i++)
            {
                $useitemcount++;
                if($itemValue * $useitemcount+$status >= self::STATUS_MAX_VALUE)
                {
                    //使用後ステータスが200を超えた場合ステータスに200を入れる
                    $status = self::STATUS_MAX_VALUE;
                    break;
                }
                $status += $itemValue;
            }
            //元の所持数から使用した分を引く
            $itemcount -= $useitemcount;

            //算出したステータスの値を、アイテムタイプによって決まるステータスへ格納
            if($itemdata -> item_type == self::ITEMTYPE_HP_POTION)
            {
                $hp = $status;
            }
            else if($itemdata -> item_type == self::ITEMTYPE_MP_POTION)
            {
                $mp = $status;
            }
            //プレイヤーアイテムデータの値を更新する
            //０個になった場合はテーブルを削除する
            if($itemcount == 0)
            {
                $playerItem->playerItemDelete($id, $request -> itemId);
            }
            else
            {
                $playerItem->playerItemUpdate($id, $request -> itemId, $itemcount);
            }

            //プレイヤーデータの値を更新する
            $player -> playerUpdate($id, $playerdata["name"], $hp, $mp, $playerdata["money"]);
            DB::commit();
            //アイテムの使用後の個数と、変化したプレイヤーのステータスを返す
            return new Response([
                "itemId" => $request->itemId, "count"=>$itemcount,
                "player" => ["id" => (int)$id,
                "hp" => $hp, "mp" => $mp],
                "code" => $response -> status()
            ]);
        }
        catch(Exception $e)
        {
            DB::rollback();
            return ["message" => $e -> getMessage(),"code" => $response -> status()];
        }
        
    }

    /**
     * countの回数ガチャを引き、結果手に入れたアイテムを加算する
     * 
     * @param int id
     * @return \Illuminate\Http\Response
     */
    public function useGacha($id,Request $request,Response $response)
    {
        //モデルのクラスを格納
        $player = new Player();
        $item = new Item();
        $playerItem = new PlayerItem();
        try
        {
            DB::beginTransaction();

            //プレイヤーデータの存在チェック
            $playerdata = $player->txPlayerGet($id);
            if($playerdata == null)
            {
                throw new Exception('no playerdata');
            }
            //プレイヤーの所持金を格納
            $money = $playerdata["money"];

            //アイテムデータの存在チェック
            $allitemdata = $item -> itemIndex();
            $allitemcount = count($allitemdata);
            if($allitemcount == 0)
            {
                throw new Exception('no itemdatas');
            }

            //１回のガチャの価格を格納
            $price = 10;

            //ガチャに使用するお金が足りているかチェック
            if($money < $price * $request -> count)
            {
                throw new Exception('no money error');
            }

            //ガチャの結果、アイテム毎の確率を格納する配列を宣言
            $results = array();
            $itemcounts = array();
            $percentSUM = 0;

            
            //指定したプレイヤーのプレイヤーアイテムデータを全アイテム分
            //存在チェックし、所持数と排出確立を配列に格納する
            //存在していなければプレイヤーアイテムデータのレコードを作成する
            for($i = 0; $i < $allitemcount; $i++)
            {
                $playerItemdata = $playerItem->playerItemGet($id,$i+1);

                if($playerItemdata != null)
                {
                    $itemcounts[] = $playerItemdata->itemcount;
                }
                else
                {
                    $itemcounts[] = 0;
                }

                $results[] = 0;
                $percents[] = $allitemdata[$i]["percent"];
                $percentSUM += $allitemdata[$i]["percent"];

            }

            //排出確率の合計が１００を超えていないかチェック
            if($percentSUM > 100)
            {
                throw new Exception('percent error');
            }

            

            //ガチャを行い、結果を格納する
            $percentSUM = 0;
            for($i = 0; $i<$request -> count; $i++)
            {
                $gachaResult = mt_rand(0,100);
                for($j = 0; $j < $allitemcount; $j++)
                {
                    $percentSUM += $percents[$j];
                    if($gachaResult < $percentSUM)
                    {
                        $results[$j]++;
                        $itemcounts[$j]++;
                        break;
                    }
                }
            }

            //プレイヤーのデータを更新する
            $money -= $price*$request -> count;
            $player -> playerUpdate($id, $playerdata["name"],
            $playerdata["hp"], $playerdata["mp"], $money);

            //レスポンスに使用するJSONデータを格納する配列を宣言する
            $resultdatas = array();
            $playerItems = array();

            //ガチャで排出されたアイテム毎の個数と
            //その結果増加した現在の所持数をJSONデータで格納すると共に、
            //プレイヤーアイテムデータの更新を行う
            for($i = 0; $i < $allitemcount; $i++)
            {
                if($results[$i] != 0)
                {
                    //プレイヤーアイテムデータが存在しない場合、
                    //レコードを作成して排出された個数を格納する
                    $playerItemdata = $playerItem->playerItemGet($id,$i+1);
                    if($playerItemdata == null)
                    {
                        $playerItem -> playerItemCreate($id, $i + 1, $results[$i]);
                    }
                    //存在する場合は排出後の所持数でレコードを更新する
                    else
                    {
                        $playerItem -> playerItemUpdate($id, $i + 1, $itemcounts[$i]);
                    }
                    $resultdatas[] = ["itemId" => $i + 1,"count" => $results[$i]];
                }
                if($itemcounts[$i] != 0)
                {
                    $playerItems[] = ["itemId" => $i + 1, "count" => $itemcounts[$i]];
                }
            }
            DB::commit();
            
            //ガチャによって排出されたアイテムと、
            //その結果更新された現在のアイテム所持数、所持金を返す
            return new Response([
                "results" => $resultdatas,
                "player" => ["money" => $money,"items" => $playerItems],
                "code" => $response -> status()
            ]);
        }
        catch(Exception $e)
        {
            DB::rollback();
            return ["message"=>$e->getMessage(),"code" => $response -> status()];
        }  
    }
}

