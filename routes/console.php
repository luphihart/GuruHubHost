<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Notification;
use App\Models\Journal;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Midnight task: Delete old read notifications (>30 days)
Schedule::call(function () {
    Notification::where('is_read', true)
        ->where('updated_at', '<', Carbon::now()->subDays(30))
        ->delete();
})->dailyAt('00:00');

// 2. Notification warning task: Warning for draft journals > 3 days
Schedule::call(function () {
    $threeDaysAgo = Carbon::now()->subDays(3);
    
    // Get draft journals
    $draftJournals = Journal::with(['schedule.teacher.user', 'schedule.class', 'schedule.subject'])
        ->where('status', 'DRAFT')
        ->where('date', '<', $threeDaysAgo)
        ->get();
        
    foreach ($draftJournals as $journal) {
        $teacher = $journal->schedule->teacher ?? null;
        if ($teacher && $teacher->user_id) {
            // Prevent duplicate notification within 24 hours
            $alreadyNotified = Notification::where('user_id', $teacher->user_id)
                ->where('type', 'JOURNAL_OVERDUE')
                ->where('message', 'like', '%' . $journal->id . '%')
                ->exists();
                
            if (!$alreadyNotified) {
                Notification::create([
                    'user_id' => $teacher->user_id,
                    'title' => 'Jurnal Belum Difinalisasi',
                    'message' => "Jurnal kelas {$journal->schedule->class->name} ({$journal->schedule->subject->name}) tanggal " . Carbon::parse($journal->date)->format('d/m/Y') . " belum difinalisasi. (ID: {$journal->id})",
                    'type' => 'JOURNAL_OVERDUE',
                    'is_read' => false
                ]);
            }
        }
    }
})->dailyAt('01:00');

