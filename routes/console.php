<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('documents:remind')->dailyAt('08:00');
