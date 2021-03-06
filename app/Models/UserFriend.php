<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

class UserFriend extends Model
{
    protected $table = 'user_friend';

    /**
     * Check user friend has exist or not
     * @author baohq
     * @date   2017-10-13
     * @param  [type]     $userId
     * @param  [type]     $characterId
     * @return boolean
     */
    public static function isExist($userId, $characterId)
    {
        $count = UserFriend::where('user_id', $userId)
                            ->where('character_id', $characterId)
                            ->where('is_deleted', 0)
                            ->count();
        if($count > 0) {
            return true;
        }

        return false;
    }
}
