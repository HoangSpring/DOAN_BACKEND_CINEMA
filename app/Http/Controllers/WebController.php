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
        $validStatuses = ['all', 'showing', 'coming_soon'];
        $status = in_array($request->query('status'), $validStatuses) ? $request->query('status') : 'all';

        $showingMovies = collect();
        $comingSoonMovies = collect();
        
        $queryShowing = Movie::with(['tags', 'showtimes' => function ($q) {
            $q->where('start_time', '>=', now()->subMinutes(30))->orderBy('start_time');
        }])->where('status', 'showing');
        
        $queryComingSoon = Movie::with(['tags', 'showtimes' => function ($q) {
            $q->where('start_time', '>=', now()->subMinutes(30))->orderBy('start_time');
        }])->where('status', 'coming_soon');

        if ($request->has('tags') && !empty($request->tags)) {
            $tagSlugs = explode(',', $request->tags);
            $queryShowing->whereHas('tags', function ($q) use ($tagSlugs) {
                $q->whereIn('slug', $tagSlugs);
            });
            $queryComingSoon->whereHas('tags', function ($q) use ($tagSlugs) {
                $q->whereIn('slug', $tagSlugs);
            });
        }
        
        if ($status === 'all' || $status === 'showing') {
            $showingMovies = $queryShowing->get();
        }
        
        if ($status === 'all' || $status === 'coming_soon') {
            $comingSoonMovies = $queryComingSoon->get();
        }
        
        $tags = Tag::all();
        $featuredMovies = $showingMovies->isNotEmpty() ? $showingMovies->take(4) : $comingSoonMovies->take(4);
        
        return view('pages.home', compact('showingMovies', 'comingSoonMovies', 'tags', 'featuredMovies', 'status'));
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
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = \App\Models\User::create([
            'full_name' => $data['full_name'],
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

    public function profile()
    {
        $user = auth()->user();
        return view('pages.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->full_name = $data['full_name'];
        $user->phone = $data['phone'];
        $user->email = $data['email'];
        
        if (!empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        
        $user->save();
        
        return back()->with('success', 'Cập nhật thông tin tài khoản thành công!');
    }
}
