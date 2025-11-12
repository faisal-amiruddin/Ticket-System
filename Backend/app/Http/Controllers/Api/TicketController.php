<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketReplyStoreRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketReplyResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketReply;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Ticket::query();
            $query->orderBy('created_at', 'desc');

            if ($request->search) {
                $query->where('code', 'like', '%' . $request->search . '%')
                ->orWhere('title', 'like', '%' . $request->search . '%');
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if(auth()->user()->role == 'user') {
                $query->where('user_id', auth()->user()->id);
            }

            $ticket = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Tiket Berhasil Didapatkan',
                'data' => TicketResource::collection($ticket)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Mendapatkan Data Ticket',
                'error' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
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
                'error' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function storeReply(TicketReplyStoreRequest $request, $code)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket Tidak Ditemukan',
                    'data' => null
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda Tidak Memiliki Izin',
                    'data' =>null
                ], 403);
            }

            $ticketReply = new TicketReply();
            $ticketReply->ticket_id = $ticket->id;
            $ticketReply->user_id = $ticket->auth()->user()->id;
            $ticketReply->content = $data['content'];
            $ticketReply->save();

            if (auth()->user()->role == 'king') {
                $ticket->status = $data['status'];

                if ($data['status'] == 'resolved') {
                    $ticket->completed_at = now();
                }
                $ticket->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Membalas',
                'data' => new TicketReplyResource($ticketReply)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan',
                'data' => null
            ], 500);
        }
    }

    public function show($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket Tidak Ditemukan',
                    'data' => null
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda Tidak Memiliki Izin',
                    'data' =>null
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket Berhasil Ditampilkan',
                'data' => new TicketResource($ticket)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Menampilkan Ticket',
                'error' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
