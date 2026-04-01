@extends('emails.layout')

@section('title', 'Team Invitation')

@section('content')
    <h2 style="margin: 0; padding: 0 0 20px; font-size: 20px; font-weight: 700; color: #f8fafc;">
        🔥 Invitation to Join Team: {{ $team->name }}
    </h2>
    <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #94a3b8;">
        Hello, <strong>{{ $email }}</strong>!
    </p>
    <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #94a3b8;">
        Captain <strong>{{ $team->captain->name }}</strong> has invited you to join their team in the Juara League platform.
    </p>
    
    @if ($team->description)
        <blockquote style="margin: 0 0 30px; padding: 20px; border-left: 4px solid #6366f1; background-color: #334155; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; font-size: 15px; font-style: italic; color: #cbd5e1; line-height: 1.5;">
                "{{ $team->description }}"
            </p>
        </blockquote>
    @endif

    <div style="padding-bottom: 40px; text-align: center;">
        <a href="{{ $acceptUrl }}" style="display: inline-block; padding: 18px 40px; font-size: 16px; font-weight: 800; color: #ffffff; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); text-decoration: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
            Accept Invitation
        </a>
    </div>

    <hr style="border: 0; border-top: 1px solid #334155; margin-bottom: 20px;">
    
    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #64748b;">
        This invitation is valid for 7 days. If you didn't expect this, you can safely ignore this email.
    </p>
    <p style="margin: 10px 0 0; font-size: 12px; line-height: 1.5; color: #64748b; font-style: italic;">
        Having trouble? Copy and paste the link below: <br>
        <span style="color: #6366f1;">{{ $acceptUrl }}</span>
    </p>
@endsection
