<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Floor;
use App\Room;
use Validator;

class PlacesController extends Controller
{
    public function __construct(){
      $this->middleware(['auth', 'admin']);
    }

    public function showManagePlaces(){
      $user = Auth::user();
      $floors = Floor::all();

      return view('manage-places', ["user" => $user, 'floors' => $floors]);
    }

    public function showEditFloor($id){
      $floor = Floor::find($id);

      if(!$floor){
        abort(404);
      }

      $user = Auth::user();

      return view('floor-form', ['floor' => $floor, 'user' => $user]);
    }

    public function editFloor(Request $request, $id){
      $floor = Floor::find($id);

      if(!$floor){
        abort(404);
      }

      Validator::make($request->all(), ["name" => 'required'])->validate();

      $name = $request->name;
      $floor->name = $name;
      $rooms = $floor->rooms()->get();
      $saved = [];
      foreach($request->all() as $key => $value){
        if(strpos($key, 'room_name_') !== false || strpos($key, 'room_number_') !== false){
          $i = explode("_", $key)[2];
          $room_name = $request->input('room_name_'.$i);
          $room_number = $request->input('room_number_'.$i);
          if((!$room_name && !$room_number) || array_search($i, $saved) !== false){
            continue;
          }
          if($request->input('room_'.$i.'_id') !== null){
            $room_id = $request->input('room_'.$i.'_id');
            $room = Room::find($room_id);
            if(!$room){
              continue;
            }
            $rooms = collect($rooms->where('id', '!=', $room_id)->all());

            $room->name = $room_name;
            $room->room_number = $room_number ? $room_number : -1;
            $room->save();
            $saved[] = $i;
          }
          else{
            $room = new Room(["name" => $room_name, "room_number" => $room_number ? $room_number : -1]);
            $room->floor()->associate($floor);
            $room->save();
            $saved[] = $i;
          }
        }
      }

      foreach($rooms as $room){
        $room->delete();
      }

      $floor->number_of_rooms = count($saved);
      $floor->save();

      $request->session()->flash('message', __('تم تعديل المكان'));
      $request->session()->flash('message_class', 'success');

      return redirect('/manage-places');
    }

    public function showAddFloor(){
      return view('floor-form', ['floor' => null, 'user' => Auth::user()]);
    }

    public function addFloor(Request $request){
      Validator::make($request->all(), [
        "name" => "required"
      ])->validate();

      $name = $request->name;
      $floor = new Floor(["name" => $name]);
      $rooms = [];
      foreach($request->all() as $key => $value){
        if(strpos($key, 'room_name_') !== false || strpos($key, 'room_number_') !== false){
          $i = explode("_", $key)[2];
          $room_name = $request->input('room_name_'.$i);
          $room_number = $request->input('room_number_'.$i);
          if((!$room_name && !$room_number) || array_search($i, array_keys($rooms)) !== false){
            continue;
          }
          $room = new Room(["name" => $room_name, "room_number" => $room_number ? $room_number : -1]);
          $rooms[$i] = $room;
        }
      }

      $floor->number_of_rooms = count($rooms);
      $floor->save();
      foreach($rooms as $room){
        $room->floor()->associate($floor);
        $room->save();
      }

      $request->session()->flash('message', __('تم إضافة المكان'));
      $request->session()->flash('message_class', 'success');

      return redirect('/manage-places');
    }

    public function deleteFloor(Request $request, $id){
      $floor = Floor::destroy($id);

      $request->session()->flash('message', __('تم حذف المكان'));
      $request->session()->flash('message_class', 'success');

      return redirect('/manage-places');
    }
}
