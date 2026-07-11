<?php
dump(App\Models\Movie::with('tags')->first());
dump(App\Models\Showtime::with('showtimeSeats.seat')->first());
