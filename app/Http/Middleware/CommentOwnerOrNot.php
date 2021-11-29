<?php

namespace App\Http\Middleware;

use App\Models\Comments;
use Closure;
use Illuminate\Http\Request;
use App\Models\FriendRequest;
use App\Models\Posts;
use App\Models\User;
use Exception;

class CommentOwnerOrNot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try{
            $user_data=request()->user_data;
            if (isset($request->comment_id)) {
                $comment=Comments::with('Posts')->where('id',$request->comment_id)->first();

                // chekcing if they are friends or not
                if ($comment->user_id==$user_data->id) {
                    $request->merge(['comment_data'=>$comment]);
                    return $next($request);
                }else{

                    throw new Exception("You are not allowed to do that");

                }
                return $next($request);
            }


        }catch(\Exception $ex){
            info($ex->getMessage());
            $data['error']=$ex->getMessage();
            $data['message']="Someting went Worng";
            return response()->error($data,400);
        }
    }
}
