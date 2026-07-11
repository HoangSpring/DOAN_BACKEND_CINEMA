<?php
$tables = ['users', 'movies', 'tags', 'movie_tags', 'rooms', 'seats', 'showtimes', 'showtime_seats'];
foreach ($tables as $table) {
    echo $table . ': ' . DB::table($table)->count() . PHP_EOL;
}
