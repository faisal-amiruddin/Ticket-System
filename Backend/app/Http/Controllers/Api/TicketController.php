<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function store(TicketStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = new Ticket();
            $ticket->user_id = auth()->user()->id;
            $ticket->code = "YOKA-" . rand(1000, 99999);
            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket Berhasil Dibuat',
                'data' => new TicketResource($ticket)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal Membuat Ticket',
                'data' => null
            ], 500);
        }
    }
}
