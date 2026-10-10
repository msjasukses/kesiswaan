<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 34px; }
        * { font-family: Helvetica, Arial, sans-serif; }
        body { font-size: 9.5px; color: #1e293b; margin: 0; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #334155; padding-bottom: 6px; }
        .header .sekolah { font-size: 14px; font-weight: bold; }
        .header .alamat { font-size: 8.5px; color: #475569; }
        h1 { text-align: center; font-size: 13px; margin: 8px 0 2px; letter-spacing: 1px; }
        .sub { text-align: center; font-size: 9.5px; color: #475569; margin-bottom: 10px; }
        h3 { font-size: 10px; margin: 10px 0 4px; background: #e2e8f0; padding: 3px 6px; }
        table { width: 100%; border-collapse: collapse; }
        .ident td { padding: 2px 4px; vertical-align: top; }
        .ident td.label { width: 36%; color: #334155; }
        .ident td.sep { width: 2%; }
        .grid th, .grid td { border: 0.5px solid #94a3b8; padding: 3px 4px; text-align: left; vertical-align: top; }
        .grid thead th { background-color: #e2e8f0; font-weight: bold; text-align: center; }
        .grid td.c { text-align: center; }
        .grid td.kelompok { background: #f1f5f9; font-weight: bold; }
        .meta td { padding: 1px 4px; }
        .box { page-break-inside: avoid; }
        .page-break { page-break-before: always; }
        .foto { width: 90px; height: 120px; border: 0.5px solid #94a3b8; text-align: center; vertical-align: middle; color: #94a3b8; font-size: 8.5px; }
        .ttd td { vertical-align: top; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    @php $capaianP5 = config('buku_induk.capaian_p5'); @endphp

    <div class="header">
        <div class="sekolah">{{ setting('nama_sekolah', 'Aplikasi Kesiswaan') }}</div>
        @if (setting('alamat'))
            <div class="alamat">{{ setting('alamat') }}@if (setting('npsn')) &middot; NPSN {{ setting('npsn') }}@endif</div>
        @endif
    </div>

    <h1>BUKU INDUK SISWA</h1>
    <div class="sub">{{ $bukuInduk->kurikulum_label }}</div>

    <table class="meta" style="margin-bottom: 4px;">
        <tr>
            <td style="width: 18%;">Nomor Induk (NIS)</td><td style="width: 32%;">: <b>{{ $siswa->nis ?? '-' }}</b></td>
            <td style="width: 18%;">NISN</td><td>: <b>{{ $siswa->nisn }}</b></td>
        </tr>
    </table>

    @foreach ($identitas as $judul => $baris)
        <div class="box">
            <h3>{{ $judul }}</h3>
            <table class="ident">
                @foreach ($baris as $label => $nilai)
                    <tr>
                        <td class="label">{{ $loop->iteration }}. {{ $label }}</td>
                        <td class="sep">:</td>
                        <td>{{ filled($nilai) ? $nilai : '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach

    @if ($bukuInduk->catatan)
        <h3>Catatan</h3>
        <p style="margin: 2px 4px;">{{ $bukuInduk->catatan }}</p>
    @endif

    <div class="box">
        <h3>Riwayat Kelas</h3>
        <table class="grid">
            <thead><tr><th style="width: 25%;">Tahun Ajaran</th><th style="width: 25%;">Kelas</th><th>Wali Kelas</th></tr></thead>
            <tbody>
                @forelse ($riwayatKelas as $r)
                    <tr><td class="c">{{ $r->tahunAjaran?->nama_tahun_ajaran ?? '-' }}</td><td class="c">{{ $r->kelas?->nama_rombel ?? '-' }}</td><td>{{ $r->kelas?->wali_kelas_nama ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="3" class="c muted">-</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="box">
        <h3>Prestasi</h3>
        <table class="grid">
            <thead><tr><th style="width: 5%;">No</th><th>Prestasi</th><th style="width: 18%;">Tingkat</th><th style="width: 18%;">Tanggal</th></tr></thead>
            <tbody>
                @forelse ($prestasi as $p)
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td>{{ $p->nama_prestasi }}@if ($p->peringkat) ({{ $p->peringkat }})@endif</td>
                        <td class="c">{{ $p->tingkat }}</td>
                        <td class="c">{{ $p->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="c muted">-</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <table class="ttd box" style="margin-top: 14px;">
        <tr>
            <td style="width: 50%;">
                <table><tr><td class="foto">Pas Foto<br>3 x 4</td></tr></table>
            </td>
            <td>
                {{ now()->translatedFormat('d F Y') }}<br>
                Kepala Sekolah,<br><br><br><br><br>
                <b>{{ setting('kepala_sekolah', '....................................') }}</b><br>
                @if (setting('nip_kepala_sekolah'))NIP. {{ setting('nip_kepala_sekolah') }}@endif
            </td>
        </tr>
    </table>

    {{-- Laporan hasil belajar per semester --}}
    @foreach ($semester as $sem)
        @php $no = 0; $k13 = $sem->isK13(); @endphp
        <div class="page-break"></div>

        <h1>LAPORAN PERKEMBANGAN HASIL BELAJAR</h1>
        <div class="sub">{{ $sem->kurikulum_label }}</div>

        <table class="meta" style="margin-bottom: 6px;">
            <tr>
                <td style="width: 16%;">Nama Siswa</td><td style="width: 38%;">: {{ $siswa->nama_siswa }}</td>
                <td style="width: 16%;">Kelas</td><td>: {{ $sem->kelas }}</td>
            </tr>
            <tr>
                <td>NIS / NISN</td><td>: {{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</td>
                <td>Semester</td><td>: {{ $sem->semester_label }}</td>
            </tr>
            <tr>
                <td>Wali Kelas</td><td>: {{ $sem->wali_kelas ?? '-' }}</td>
                <td>Tahun Pelajaran</td><td>: {{ $sem->tahun_pelajaran }}</td>
            </tr>
        </table>

        @if ($k13)
            <h3>A. Sikap</h3>
            <table class="grid">
                <thead><tr><th style="width: 18%;">Aspek</th><th style="width: 16%;">Predikat</th><th>Deskripsi</th></tr></thead>
                <tbody>
                    <tr><td>Sikap Spiritual</td><td class="c">{{ $sem->sikap_spiritual_predikat ?? '-' }}</td><td>{{ $sem->sikap_spiritual_deskripsi ?: '-' }}</td></tr>
                    <tr><td>Sikap Sosial</td><td class="c">{{ $sem->sikap_sosial_predikat ?? '-' }}</td><td>{{ $sem->sikap_sosial_deskripsi ?: '-' }}</td></tr>
                </tbody>
            </table>
            <h3>B. Pengetahuan dan Keterampilan</h3>
        @else
            <h3>A. Nilai Akademik</h3>
        @endif

        <table class="grid">
            <thead>
                @if ($k13)
                    <tr>
                        <th rowspan="2" style="width: 4%;">No</th>
                        <th rowspan="2" style="width: 20%;">Mata Pelajaran</th>
                        <th rowspan="2" style="width: 5%;">KKM</th>
                        <th colspan="3">Pengetahuan</th>
                        <th colspan="3">Keterampilan</th>
                    </tr>
                    <tr>
                        <th style="width: 5%;">Nilai</th><th style="width: 5%;">Pred</th><th>Deskripsi</th>
                        <th style="width: 5%;">Nilai</th><th style="width: 5%;">Pred</th><th>Deskripsi</th>
                    </tr>
                @else
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 28%;">Mata Pelajaran</th>
                        <th style="width: 9%;">Nilai Akhir</th>
                        <th>Capaian Kompetensi</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @forelse ($sem->nilai->groupBy(fn ($n) => $n->kelompok ?: '') as $kelompok => $rows)
                    @if ($kelompok !== '')
                        <tr><td colspan="{{ $k13 ? 9 : 4 }}" class="kelompok">{{ $kelompok }}</td></tr>
                    @endif
                    @foreach ($rows as $n)
                        <tr>
                            <td class="c">{{ ++$no }}</td>
                            <td>{{ $n->nama_mapel }}</td>
                            @if ($k13)
                                <td class="c">{{ $n->kkm ?? '-' }}</td>
                                <td class="c">{{ $n->nilai_pengetahuan ?? '-' }}</td>
                                <td class="c">{{ $n->predikat_pengetahuan ?? '-' }}</td>
                                <td>{{ $n->deskripsi_pengetahuan ?: '-' }}</td>
                                <td class="c">{{ $n->nilai_keterampilan ?? '-' }}</td>
                                <td class="c">{{ $n->predikat_keterampilan ?? '-' }}</td>
                                <td>{{ $n->deskripsi_keterampilan ?: '-' }}</td>
                            @else
                                <td class="c">{{ $n->nilai_akhir ?? '-' }}</td>
                                <td>{{ $n->capaian_kompetensi ?: '-' }}</td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="{{ $k13 ? 9 : 4 }}" class="c muted">Belum ada nilai.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="box">
            <h3>{{ $k13 ? 'C' : 'B' }}. Ekstrakurikuler</h3>
            <table class="grid">
                <thead><tr><th style="width: 5%;">No</th><th style="width: 35%;">Kegiatan</th><th style="width: 18%;">Predikat</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @forelse ($sem->ekstrakurikuler ?? [] as $e)
                        <tr><td class="c">{{ $loop->iteration }}</td><td>{{ $e['nama'] }}</td><td class="c">{{ $e['predikat'] ?: '-' }}</td><td>{{ $e['keterangan'] ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="c muted">-</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @unless ($k13)
            <div class="box">
                <h3>C. Projek Penguatan Profil Pelajar Pancasila / Kokurikuler</h3>
                <table class="grid">
                    <thead><tr><th style="width: 5%;">No</th><th style="width: 35%;">Tema / Projek</th><th style="width: 22%;">Capaian</th><th>Catatan</th></tr></thead>
                    <tbody>
                        @forelse ($sem->projek ?? [] as $p)
                            <tr>
                                <td class="c">{{ $loop->iteration }}</td>
                                <td>{{ $p['tema'] }}</td>
                                <td class="c">{{ ! empty($p['capaian']) ? ($capaianP5[$p['capaian']] ?? $p['capaian']) : '-' }}</td>
                                <td>{{ $p['catatan'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="c muted">-</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endunless

        <div class="box">
            <h3>D. Ketidakhadiran</h3>
            <table class="grid" style="width: 50%;">
                <tr><td>Sakit</td><td class="c" style="width: 30%;">{{ $sem->sakit }} hari</td></tr>
                <tr><td>Izin</td><td class="c">{{ $sem->izin }} hari</td></tr>
                <tr><td>Tanpa Keterangan</td><td class="c">{{ $sem->alpa }} hari</td></tr>
            </table>
        </div>

        <div class="box">
            <h3>E. Catatan Wali Kelas</h3>
            <p style="margin: 2px 4px;">{{ $sem->catatan_wali_kelas ?: '-' }}</p>

            <h3>F. Keputusan</h3>
            <p style="margin: 2px 4px;">{{ $sem->keputusan ?: '-' }}@if ($sem->tanggal_rapor) <span class="muted">&middot; {{ $sem->tanggal_rapor->translatedFormat('d F Y') }}</span>@endif</p>
        </div>
    @endforeach
</body>
</html>
