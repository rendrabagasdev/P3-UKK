<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\BaseController;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends BaseController
{
    public function index()
    {
        // Semua peran (1,2,3) boleh melihat jadwal
        if ($redirect = $this->authorizeRoles([1,2,3])) {
            return $redirect;
        }

        // Ambil ruangan beserta booking & jadwal reguler, urutkan berdasarkan tanggal mulai
        $rooms = Room::with([
            'bookings' => function($q){
                $q->orderBy('tanggal_mulai', 'asc');
            },
            'jadwalRegulers' => function($q){
                $q->orderBy('tanggal_mulai', 'asc');
            }
        ])->get();

        return view('schedule.index', compact('rooms'));
    }
}
