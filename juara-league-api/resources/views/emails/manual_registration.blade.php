@extends('emails.layout')

@section('title', 'Pendaftaran Turnamen Berhasil')

@section('content')
    <h2 style="margin: 0; padding: 0 0 20px; font-size: 20px; font-weight: 700; color: #f8fafc;">
        🏆 Pendaftaran Turnamen: {{ $tournament->title }}
    </h2>
    <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #94a3b8;">
        Halo, <strong>{{ $user->name }}</strong>!
    </p>
    <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #94a3b8;">
        Anda telah didaftarkan secara manual ke turnamen <strong>{{ $tournament->title }}</strong> oleh pihak penyelenggara. Pendaftaran Anda otomatis disetujui (Approved).
    </p>

    @if ($teamName)
        <blockquote style="margin: 0 0 20px; padding: 20px; border-left: 4px solid #6366f1; background-color: #334155; border-radius: 0 12px 12px 0;">
            <p style="margin: 0; font-size: 15px; color: #cbd5e1; line-height: 1.5;">
                Anda terdaftar sebagai perwakilan dari tim: <strong>{{ $teamName }}</strong>
            </p>
        </blockquote>
    @endif

    <div style="background-color: #0f172a; padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #1e293b;">
        <h3 style="margin: 0 0 10px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #6366f1;">
            Informasi Akun Juara League
        </h3>
        <p style="margin: 0 0 10px; font-size: 14px; color: #94a3b8;">
            Kami telah membuatkan akun untuk Anda agar dapat melihat jadwal dan braket pertandingan secara real-time.
        </p>
        <p style="margin: 0 0 5px; font-size: 15px; color: #cbd5e1;">
            <strong>Email:</strong> {{ $user->email }}
        </p>
        <p style="margin: 0; font-size: 15px; color: #cbd5e1;">
            <strong>Password Sementara:</strong> <span style="font-family: monospace; font-weight: bold; color: #38bdf8;">{{ $rawPassword }}</span>
        </p>
    </div>

    <div style="padding-bottom: 30px; text-align: center;">
        <a href="{{ $loginUrl }}" style="display: inline-block; padding: 18px 40px; font-size: 16px; font-weight: 800; color: #ffffff; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); text-decoration: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            Masuk ke Dashboard
        </a>
    </div>

    <hr style="border: 0; border-top: 1px solid #334155; margin-bottom: 20px;">
    
    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #64748b;">
        Harap segera mengganti kata sandi utama Anda setelah berhasil login demi keamanan akun.
    </p>
@endsection
