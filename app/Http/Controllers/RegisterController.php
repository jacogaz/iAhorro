<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Models\Client;

use Carbon\Carbon;

use App;

class RegisterController extends Controller
{

    public function saveRegister(Request $req){
        $validator = Validator::make($req->all(), [
            'name' => 'required',
            'surname' => 'required',
            'phone_number' => 'required',
            'personal_amount' => 'required|min:0',
            'required_amount' => 'required|min:0',
            'email' => 'required|unique',
            'time_zone' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 500,
                'message' => 'Error'
            ]);
        }

        $client = new Client();
        $client->name = $req->name;
        $client->surname = $req->surname;
        $client->phone_number = $req->phone_number;
        $client->personal_amount = $req->personal_amount;
        $client->required_amount = $req->required_amount;
        $client->email = $req->email;
        $client->time_zone = $req->time_zone;
        $save = $client->save();

        if(!$saved){
            return response()->json([
                'code' => 500,
                'message' => 'Error'
            ]);
        } else {
            return response()->json([
                'code' => 200,
                'message' => 'Saved'
            ]);
        }
    }

    public function getRegisters(Request $req){
        $expert_id = $req->expertId; //getting the expert id from url
        $clients = $this->getClientsWithScore(); //calculate and add score to client 
        $sorted_clients = $this->sortClientsByScore($clients); // sort by score

        $json = json_encode($sorted_clients);

        return response()->json($json);
    }

    public function getClientsWithScore(){
        $clients_arr = [];
        $clients = Client::where('active', true)->get();

        foreach($clients as $client){
            $score = $this->calculateScore($client->required_amount, $client->personal_amount, $client->created_at);
            $client->score = $score;
            array_push($clients_arr, $client);
        }

        return $clients_arr;
    }

    public function calculateScore($required, $personal, $date){
        $hours = $date->diffForHumans(null, true, true, 1);
        $score = ($required / $personal) * $hours;
        return $score;
    }

    public function sortClientsByScore($clients_arr){
        $clients_arr = $clients_arr->sortByDesc(function($item){
            return $item->score;
        })->values();

        return $clients_arr;
    }
}
