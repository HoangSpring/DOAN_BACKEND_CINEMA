<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Tag;
use App\Models\Showtime;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index(Request $request)
    {
        $validStatuses = ['showing', 'coming_soon'];
        $status = in_array($request->query('status'), $validStatuses) ? $request->query('status') : 'showing';

        $query = Movie::with(['tags', 'showtimes' => function ($q) {
            $q->where('start_time', '>=', now())->orderBy('start_time');
        }])->where('status', $status);
        
        if ($request->has('tags') && !empty($request->tags)) {
            $tagSlugs = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tagSlugs) {
                $q->whereIn('slug', $tagSlugs);
            });
        }
        
        $movies = $query->get();
        $tags = Tag::all();
        $featuredMovies = $movies->take(3);
        
        return view('pages.home', compact('movies', 'tags', 'featuredMovies', 'status'));
    }

    public function showMovie($id)
    {
        $movie = Movie::with('tags')->findOrFail($id);
        
        // Next 7 days
        $dates = collect();
        for ($i = 0; $i < 7; $i++) {
            $dates->push(now()->addDays($i)->format('Y-m-d'));
        }
        
        $selectedDate = request('date', now()->format('Y-m-d'));
        
        $showtimes = Showtime::with('room')
            ->where('movie_id', $id)
            ->whereDate('start_time', $selectedDate)
            ->orderBy('start_time')
            ->get();
            
        return view('pages.movie-details', compact('movie', 'dates', 'selectedDate', 'showtimes'));
    }

    public function seats($showtimeId)
    {
        $showtime = Showtime::with(['movie', 'room'])->findOrFail($showtimeId);
        return view('pages.seats', compact('showtime'));
    }

    public function seatsApi($showtimeId)
    {
        $showtime = Showtime::findOrFail($showtimeId);
        $seats = \App\Models\ShowtimeSeat::with('seat')
            ->where('showtime_id', $showtimeId)
            ->get();
            
        // Map data appropriately for the frontend
        $mapped = $seats->map(function ($ss) {
            $status = $ss->status;
            if ($status === 'holding' && $ss->hold_expires_at && $ss->hold_expires_at->isPast()) {
                $status = 'available';
            }

            $seatType = $ss->seat?->seat_type ?? 'standard';

            return [
                'id' => $ss->id,
                'seat_id' => $ss->seat_id,
                'row' => $ss->seat?->seat_row,
                'seat_row' => $ss->seat?->seat_row,
                'number' => $ss->seat?->seat_number,
                'seat_number' => $ss->seat?->seat_number,
                'type' => $seatType,
                'seat_type' => $seatType,
                'status' => $status,
                'price' => $seatType === 'vip' ? $ss->showtime->price_vip : $ss->showtime->price_standard,
            ];
        });
        
        return response()->json(['data' => $mapped]);
    }

    public function loginForm()
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            return redirect('/');
        }
        return view('pages.login');
    }

    public function loginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
    public function registerForm()
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            return redirect('/');
        }
        return view('pages.register');
    }

    public function registerSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role' => 'customer',
        ]);

        \Illuminate\Support\Facades\Auth::login($user);
        return redirect('/');
    }

    public function checkout(\App\Models\Booking $booking)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $booking);
        $booking->load(['showtime.movie', 'showtime.room', 'bookingSeats.showtimeSeat.seat']);
        return view('pages.checkout', compact('booking'));
    }

    public function ticket(\App\Models\Booking $booking)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $booking);
        $booking->load(['showtime.movie', 'showtime.room', 'bookingSeats.showtimeSeat.seat']);
        return view('pages.ticket', compact('booking'));
    }

    public function myTickets()
    {
        $bookings = \App\Models\Booking::with(['showtime.movie', 'showtime.room'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        return view('pages.my-tickets', compact('bookings'));
    }
}
