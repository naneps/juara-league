<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\User;
use App\Models\TournamentApproval;
use Illuminate\Support\Collection;

class TournamentApprovalService
{
    /**
     * Evaluate the tournament for approval.
     */
    public function evaluate(Tournament $tournament): string
    {
        $user = $tournament->user;

        // Pro tier users get auto-approved
        if ($user->plan === 'pro') {
            return $this->autoApprove($tournament);
        }

        // Check monthly quota for free tier (Max 5 tournaments per month)
        if ($this->isQuotaExceeded($user)) {
            // This should ideally be caught before creation, 
            // but we add it here as a safety measure.
            throw new \Exception('QUOTA_EXCEEDED');
        }

        // Run auto-check criteria
        $checks = $this->runAutoChecks($tournament);
        $allPassed = collect($checks)->every(fn($c) => $c['passed']);

        if ($allPassed) {
            return $this->autoApprove($tournament);
        }

        return $this->sendToReview($tournament, $checks);
    }

    /**
     * Run automated checks on the tournament and its owner.
     */
    private function runAutoChecks(Tournament $tournament): array
    {
        $user = $tournament->user;
        
        return [
            'email_verified' => [
                'passed' => !is_null($user->email_verified_at),
                'message' => 'Email must be verified.'
            ],
            'account_age' => [
                'passed' => $user->created_at->diffInDays() >= 7,
                'message' => 'Account must be at least 7 days old.'
            ],
            'not_suspended' => [
                'passed' => !$user->is_suspended,
                'message' => 'Account is suspended.'
            ],
            // Add banned words check logic here if needed
            'no_banned_words' => [
                'passed' => $this->checkBannedWords($tournament->title),
                'message' => 'Title contains restricted words.'
            ],
        ];
    }

    /**
     * Check if the user has exceeded their monthly tournament quota.
     */
    private function isQuotaExceeded(User $user): bool
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $count = Tournament::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return $count > 5;
    }

    /**
     * Auto-approve the tournament.
     */
    private function autoApprove(Tournament $tournament): string
    {
        $tournament->update(['approval_status' => 'auto_approved']);
        
        TournamentApproval::create([
            'tournament_id' => $tournament->id,
            'status' => 'auto_approved',
            'note' => 'System automatically approved based on criteria.'
        ]);

        return 'auto_approved';
    }

    /**
     * Send for manual review.
     */
    private function sendToReview(Tournament $tournament, array $checks): string
    {
        $tournament->update(['approval_status' => 'pending_review']);

        TournamentApproval::create([
            'tournament_id' => $tournament->id,
            'status' => 'pending_review',
            'auto_check_log' => $checks,
            'note' => 'Awaiting manual review due to failed auto-checks.'
        ]);

        return 'pending_review';
    }

    /**
     * Approve the tournament manually by an admin.
     */
    public function approveManually(Tournament $tournament, string $reviewerId, ?string $note = null): void
    {
        $tournament->update(['approval_status' => 'approved']);

        TournamentApproval::create([
            'tournament_id' => $tournament->id,
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'note' => $note ?: 'Approved by administrator.'
        ]);
    }

    /**
     * Reject the tournament manually by an admin.
     */
    public function rejectManually(Tournament $tournament, string $reviewerId, string $note): void
    {
        $tournament->update(['approval_status' => 'rejected']);

        TournamentApproval::create([
            'tournament_id' => $tournament->id,
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'note' => $note
        ]);
    }

    /**
     * Simple banned words check.
     */
    private function checkBannedWords(string $text): bool
    {
        $bannedWords = ['scam', 'cheat', 'hack', 'porn', 'gambling']; // Example list
        $text = strtolower($text);
        
        foreach ($bannedWords as $word) {
            if (str_contains($text, $word)) {
                return false;
            }
        }

        return true;
    }
}
