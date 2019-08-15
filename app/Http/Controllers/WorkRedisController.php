<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class WorkRedisController extends Controller
{
    public function createRedis($email)
    {
        $redis = Redis::connection();
        $redis->hmset('payment:'.$email, [
            'paid' => "0",
            'botId' => "",
            'listenerId' => "",
        ]);
        $redis->expire('payment:'.$email, 2628000);
        return $redis->hgetall('payment:'.$email);
    }

    public function getRedis(Request $request)
    {
        $redis = Redis::connection();
        $value = $redis->hgetall('payment:'.$request->email);

        if($value) {
            return response()->json(['user' => $value]);
        } else {
            $new = $this->createRedis($request->email);
            return response()->json(['user' => $new]);
        }
    }

    public function updateRedis(Request $request)
    {
        $redis = Redis::connection();
        $value = $redis->hgetall('payment:'.$request->email);

        $paid = $request->paid;
        $botId = $request->botId;
        $listenerId = $request->listenerId;

        if($value) {
            $redis->hset('payment:'.$request->email, 'paid', $paid);
            $redis->hset('payment:'.$request->email, 'botId', $botId);
            $redis->hset('payment:'.$request->email, 'listenerId', $listenerId);
            return response()->json(['user' => $redis->hgetall('payment:'.$request->email)]);
        } else {
            $new = $this->createRedis($request->email);
            return response()->json(['user' => $new]);
        }
    }

    public function deleteRedis(Request $request)
    {
        $redis = Redis::connection();
        $redis->del('payment:'.$request->email);
        $value = $redis->hgetall('payment:'.$request->email);
        if($value) {
            return response()->json(['error' => 1, 'message' => 'Not delete!']);
        } else {
            return response()->json(['error' => 0, 'message' => 'Ok, delete!']);
        }
    }
}
