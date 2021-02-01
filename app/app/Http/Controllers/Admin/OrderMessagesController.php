<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\OrderMessages;
use Illuminate\Support\Facades\Validator;
use URL;

class OrderMessagesController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.order_messages.list')->with('title', 'Диалоги');
    }

    /**
     * @param $order_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function messages($order_id, $receiver_id)
    {

        OrderMessages::whereRaw('status=0 AND (receiver_id=' . $receiver_id . ' OR receiver_id=0)')->update(['status' => 1, 'receiver_id' => \Auth::id()]);

        return view('admin.order_messages.messages', compact('order_id', 'receiver_id'))->with('title', 'Комментарии к заказу');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addAnswer(Request $request)
    {
        $rules = [
            'order_id' => 'required|numeric',
            'message' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $data['order_id'] = $request->order_id;
            $data['sender_id'] = null;
            $data['receiver_id'] = \Auth::id();
            $data['message'] = $request->message;
            $data['status'] = 0;

            OrderMessages::create($data);

            return redirect(URL::route('admin.order_messages.list'))->with('success', 'Комментарий добавлен');

        }
    }

    /**
     * @param $order_id
     */
    public function destroy($order_id)
    {
        OrderMessages::where('order_id',$order_id)->update(['status' => 3]);
    }

}