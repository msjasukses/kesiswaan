<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: Helvetica, Arial, sans-serif; }
        body { font-size: 10px; color: #1e293b; margin: 0; }
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #334155; padding-bottom: 8px; }
        .header .sekolah { font-size: 15px; font-weight: bold; }
        .header .alamat { font-size: 9px; color: #475569; }
        h2.title { text-align: center; font-size: 13px; margin: 10px 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 0.5px solid #94a3b8; padding: 4px 6px; text-align: left; vertical-align: top; }
        thead th { background-color: #e2e8f0; font-weight: bold; }
        tbody tr:nth-child(even) td { background-color: #f8fafc; }
        .meta { margin-top: 12px; font-size: 9px; color: #64748b; }
        .empty { text-align: center; padding: 16px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="sekolah">{{ setting('nama_sekolah', 'Aplikasi Kesiswaan') }}</div>
        @if (setting('alamat'))
            <div class="alamat">{{ setting('alamat') }}@if (setting('telepon')) &middot; Telp. {{ setting('telepon') }}@endif</div>
        @endif
    </div>

    <h2 class="title">{{ $title }}</h2>

    <table>
        <thead>
            <tr>
                @foreach ($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $val)
                        <td>{{ $val === null || $val === '' ? '-' : $val }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td class="empty" colspan="{{ count($headers) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="meta">
        Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
        @if (current_tahun_ajaran()) &middot; T.A. {{ current_tahun_ajaran()->nama_tahun_ajaran }} @endif
        &middot; Jumlah data: {{ count($rows) }}
    </div>
</body>
</html>
