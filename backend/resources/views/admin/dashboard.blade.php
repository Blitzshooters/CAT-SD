<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin CAT SD - Panel Manajemen Ujian & Bank Soal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen">

    <!-- Header Navigation -->
    <header class="bg-indigo-700 text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl font-black text-amber-300 shadow-inner">
                    🎒
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight leading-none">CAT SD — Panel Guru & Admin</h1>
                    <p class="text-xs text-indigo-200 mt-1">Computer Assisted Test & AI Proctoring Management</p>
                </div>
            </div>

            <!-- Stats Quick Badge -->
            <div class="flex items-center gap-2 sm:gap-4 text-xs font-medium">
                <div class="bg-indigo-800/80 px-3 py-1.5 rounded-lg border border-indigo-500/30 flex items-center gap-2">
                    <i class="fa-solid fa-book-open text-amber-300"></i>
                    <span>{{ $stats['total_subjects'] }} Ujian</span>
                </div>
                <div class="bg-indigo-800/80 px-3 py-1.5 rounded-lg border border-indigo-500/30 flex items-center gap-2">
                    <i class="fa-solid fa-circle-question text-emerald-300"></i>
                    <span>{{ $stats['total_questions'] }} Soal</span>
                </div>
                <div class="bg-indigo-800/80 px-3 py-1.5 rounded-lg border border-indigo-500/30 flex items-center gap-2">
                    <i class="fa-solid fa-user-graduate text-cyan-300"></i>
                    <span>{{ $stats['total_students'] }} Siswa</span>
                </div>
                <a href="#monitoring" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold px-3 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved"></i> Monitor AI
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Flash Message -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 font-semibold mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                    <span>Terjadi kesalahan input:</span>
                </div>
                <ul class="list-disc list-inside text-sm text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Top Navigation Tabs (Ujian & Bank Soal vs Manajemen Akun) -->
        <div class="flex items-center gap-3 border-b border-slate-200 pb-3">
            <a href="{{ route('admin.dashboard', ['tab' => 'exams', 'grade' => $grade]) }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition flex items-center gap-2 {{ ($tab ?? 'exams') === 'exams' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-graduation-cap text-sm"></i> Bank Soal & Ujian
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition flex items-center gap-2 {{ ($tab ?? 'exams') === 'users' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-users-gear text-sm"></i> Manajemen Akun Siswa & Guru ({{ $users->count() }})
            </a>
        </div>

        @if(($tab ?? 'exams') === 'exams')
        <!-- Grade Filter Bar & Actions -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold uppercase text-slate-500 mr-2 flex items-center gap-1">
                    <i class="fa-solid fa-filter"></i> Filter Kelas:
                </span>
                <a href="{{ route('admin.dashboard', ['tab' => 'exams', 'grade' => 'all']) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ (!$grade || $grade === 'all') ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Kelas
                </a>
                @for($g = 1; $g <= 6; $g++)
                    <a href="{{ route('admin.dashboard', ['tab' => 'exams', 'grade' => $g]) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ ($grade == $g) ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Kelas {{ $g }} SD
                    </a>
                @endfor
            </div>

            <button onclick="openModal('modalCreateSubject')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-sm"></i>
                Tambah Ujian Baru
            </button>
        </div>

        <!-- 2 Column Workspace: Left (Subjects) & Right (Questions for Selected Subject) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left: Subjects List (5 cols) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-book-bookmark text-indigo-600"></i>
                        Daftar Ujian ({{ $subjects->count() }})
                    </h2>
                </div>

                <div class="space-y-3 max-h-[750px] overflow-y-auto pr-1">
                    @forelse($subjects as $subj)
                        @php
                            $isSelected = $selectedSubject && $selectedSubject->id == $subj->id;
                        @endphp
                        <div class="p-4 rounded-2xl border transition-all duration-200 {{ $isSelected ? 'bg-indigo-50/70 border-indigo-400 shadow-md ring-2 ring-indigo-200' : 'bg-white border-slate-200 hover:border-slate-300 hover:shadow-sm' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $subj->grade <= 2 ? 'bg-emerald-100 text-emerald-800' : ($subj->grade <= 4 ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800') }}">
                                            Kelas {{ $subj->grade }} SD
                                        </span>
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <i class="fa-regular fa-clock"></i> {{ $subj->duration_minutes }} mnt
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.dashboard', ['grade' => $grade, 'subject_id' => $subj->id]) }}"
                                       class="text-sm font-bold text-slate-900 hover:text-indigo-600 block line-clamp-1">
                                        {{ $subj->title }}
                                    </a>
                                </div>

                                <div class="flex items-center gap-1">
                                    <button onclick="editSubject({{ json_encode($subj) }})"
                                            title="Edit Ujian"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 flex items-center justify-center text-xs transition">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form action="{{ route('admin.subjects.delete', $subj->id) }}" method="POST" onsubmit="return confirm('Hapus ujian ini beserta seluruh bank soalnya?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Hapus Ujian"
                                                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-600 flex items-center justify-center text-xs transition">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Badges for settings -->
                            <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-[11px]">
                                <div class="bg-rose-50 text-rose-700 px-2 py-1 rounded-lg flex items-center gap-1 font-semibold">
                                    <i class="fa-solid fa-ban text-[10px]"></i>
                                    <span>Maks: <strong>{{ $subj->max_violations ?? 3 }}x</strong></span>
                                </div>
                                <div class="bg-amber-50 text-amber-800 px-2 py-1 rounded-lg flex items-center gap-1 font-semibold">
                                    <i class="fa-solid fa-key text-[10px]"></i>
                                    <span class="truncate">Kode: <strong>{{ $subj->remedy_code ?? 'REMEDI'.$subj->grade }}</strong></span>
                                </div>
                            </div>

                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                <span><i class="fa-solid fa-layer-group text-slate-400"></i> {{ $subj->questions_count }} Soal</span>
                                <a href="{{ route('admin.dashboard', ['grade' => $grade, 'subject_id' => $subj->id]) }}"
                                   class="font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                    Kelola Soal <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white p-8 text-center rounded-2xl border border-slate-200">
                            <i class="fa-solid fa-inbox text-slate-300 text-3xl mb-2"></i>
                            <p class="text-xs text-slate-500 font-medium">Belum ada ujian untuk kelas ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Questions Bank of Selected Subject (8 cols) -->
            <div class="lg:col-span-8 space-y-4">
                @if($selectedSubject)
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-black uppercase bg-indigo-100 text-indigo-800">
                                    Kelas {{ $selectedSubject->grade }} SD
                                </span>
                                <h2 class="text-lg font-extrabold text-slate-900">{{ $selectedSubject->title }}</h2>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-3 flex-wrap">
                                <span><i class="fa-regular fa-clock text-indigo-500"></i> Durasi: <strong>{{ $selectedSubject->duration_minutes }} Menit</strong></span>
                                <span><i class="fa-solid fa-triangle-exclamation text-rose-500"></i> Batas Pelanggaran: <strong>{{ $selectedSubject->max_violations }}x</strong></span>
                                <span><i class="fa-solid fa-key text-amber-500"></i> Kode Remedi: <code class="bg-amber-100 text-amber-900 px-1.5 py-0.5 rounded font-bold">{{ $selectedSubject->remedy_code }}</code></span>
                            </p>
                        </div>

                        <button onclick="openModal('modalCreateQuestion')"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                            <i class="fa-solid fa-file-circle-plus text-sm"></i>
                            Tambah Soal Baru
                        </button>
                    </div>

                    <!-- Questions List -->
                    <div class="space-y-4">
                        @forelse($questions as $idx => $q)
                            @php
                                $opts = is_array($q->options) ? $q->options : json_decode($q->options, true);
                                $optImgs = is_array($q->option_images) ? $q->option_images : json_decode($q->option_images, true);
                            @endphp
                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-slate-300 transition space-y-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 font-extrabold text-xs flex items-center justify-center shrink-0">
                                            #{{ $idx + 1 }}
                                        </span>
                                        <div class="space-y-2">
                                            <p class="text-sm font-semibold text-slate-900 leading-relaxed">{{ $q->prompt }}</p>

                                            <!-- Question Image -->
                                            @if($q->question_image)
                                                <div class="mt-2">
                                                    <img src="{{ $q->question_image }}" alt="Gambar Soal"
                                                         class="max-h-48 rounded-xl border border-slate-200 object-contain bg-slate-50 p-1 shadow-sm">
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button onclick="editQuestion({{ json_encode($q) }})"
                                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 text-xs font-semibold transition flex items-center gap-1">
                                            <i class="fa-solid fa-pen text-[11px]"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.questions.delete', $q->id) }}" method="POST" onsubmit="return confirm('Hapus pertanyaan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-600 text-xs font-semibold transition flex items-center gap-1">
                                                <i class="fa-solid fa-trash text-[11px]"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Options Grid (A, B, C, D) -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 pt-2">
                                    @foreach(['A', 'B', 'C', 'D'] as $optIdx => $label)
                                        @php
                                            $isCorrect = $q->correct_answer_index == $optIdx;
                                            $optText = $opts[$optIdx] ?? '';
                                            $optImg = $optImgs[$optIdx] ?? null;
                                        @endphp
                                        <div class="p-3 rounded-xl border text-xs flex items-start gap-2.5 transition {{ $isCorrect ? 'bg-emerald-50/80 border-emerald-300 text-emerald-950 font-semibold' : 'bg-slate-50/70 border-slate-200 text-slate-700' }}">
                                            <span class="w-5 h-5 rounded-md flex items-center justify-center font-black text-[11px] shrink-0 {{ $isCorrect ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                                {{ $label }}
                                            </span>
                                            <div class="space-y-1 w-full">
                                                <span>{{ $optText }}</span>
                                                @if($optImg)
                                                    <img src="{{ $optImg }}" alt="Opsi {{ $label }}" class="max-h-24 rounded-lg border border-slate-200 mt-1 object-contain bg-white">
                                                @endif
                                            </div>
                                            @if($isCorrect)
                                                <span class="text-emerald-600 font-bold ml-auto shrink-0"><i class="fa-solid fa-circle-check"></i> Kunci</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @if($q->explanation)
                                    <div class="bg-amber-50/60 border border-amber-200/60 p-2.5 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                                        <i class="fa-regular fa-lightbulb text-amber-600 mt-0.5"></i>
                                        <div>
                                            <strong class="font-bold">Pembahasan:</strong> {{ $q->explanation }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="bg-white p-12 text-center rounded-2xl border border-slate-200">
                                <i class="fa-solid fa-file-circle-question text-slate-300 text-4xl mb-3"></i>
                                <h3 class="text-sm font-bold text-slate-700">Bank Soal Masih Kosong</h3>
                                <p class="text-xs text-slate-500 mt-1">Silakan klik tombol "Tambah Soal Baru" di atas untuk menambahkan pertanyaan beserta gambar.</p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="bg-white p-16 text-center rounded-2xl border border-slate-200">
                        <i class="fa-solid fa-arrow-left text-slate-300 text-3xl mb-3"></i>
                        <p class="text-sm font-bold text-slate-700">Pilih Ujian di panel sebelah kiri untuk mengelola bank soal.</p>
                    </div>
                @endif
            </div>

        </div>

        <!-- Section: Monitoring AI Proctoring & Hasil Ujian -->
        <section id="monitoring" class="mt-10 pt-6 border-t border-slate-200 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-shield-cat text-indigo-600"></i>
                        Monitoring AI Proctoring & Nilai Siswa Terbaru
                    </h2>
                    <p class="text-xs text-slate-500">Aktivitas pengerjaan ujian CAT dan log pelanggaran terkini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Nilai Ujian Terakhir -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-graduation-cap text-indigo-500"></i> Hasil Ujian Terakhir
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b">
                                <tr>
                                    <th class="py-2 px-3">Siswa</th>
                                    <th class="py-2 px-3">Mapel</th>
                                    <th class="py-2 px-3">Skor</th>
                                    <th class="py-2 px-3">Benar/Salah</th>
                                    <th class="py-2 px-3">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentResults as $res)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 font-semibold">{{ $res->user->name ?? 'Siswa' }} (Kls {{ $res->grade }})</td>
                                        <td class="py-2 px-3">{{ $res->subject->title ?? '-' }}</td>
                                        <td class="py-2 px-3 font-black text-indigo-600 text-sm">{{ $res->score }}</td>
                                        <td class="py-2 px-3 text-slate-600">{{ $res->correct_count }}B / {{ $res->wrong_count }}S</td>
                                        <td class="py-2 px-3 text-slate-400">{{ $res->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-slate-400">Belum ada data pengerjaan ujian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Log Pelanggaran AI Proctoring -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500"></i> Log Pelanggaran Proctoring
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] border-b">
                                <tr>
                                    <th class="py-2 px-3">Siswa</th>
                                    <th class="py-2 px-3">Tipe Pelanggaran</th>
                                    <th class="py-2 px-3">Keterangan</th>
                                    <th class="py-2 px-3">Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentViolations as $log)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 font-semibold">{{ $log->user->name ?? 'Siswa' }}</td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                {{ $log->violation_type }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-slate-600">{{ $log->description }}</td>
                                        <td class="py-2 px-3 text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-400">Tidak ada log pelanggaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
        @else
        <!-- ================= TAB: MANAJEMEN AKUN SISWA & GURU ================= -->
        <section class="space-y-4">
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-users-gear text-indigo-600"></i>
                        Manajemen Akun Siswa & Guru
                    </h2>
                    <p class="text-xs text-slate-500">Kelola hak akses, username, password, dan kelas akun untuk ujian.</p>
                </div>

                <button onclick="openModal('modalCreateUser')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-2xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                    Tambah Akun Baru
                </button>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-black text-[11px] border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Pengguna / Nama</th>
                                <th class="py-3.5 px-4">Username</th>
                                <th class="py-3.5 px-4">Hak Akses / Role</th>
                                <th class="py-3.5 px-4">Tingkat Kelas</th>
                                <th class="py-3.5 px-4">Waktu Terdaftar</th>
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($users as $u)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full {{ $u->is_admin ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }} flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-sm">{{ $u->name }}</p>
                                                <p class="text-[11px] text-slate-400">Avatar: {{ $u->avatar ?? 'av1' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-700 text-sm">
                                        {{ $u->username }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($u->is_admin)
                                            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-200 inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-shield-halved text-[10px]"></i> Guru / Admin
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-user-graduate text-[10px]"></i> Siswa SD
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-700">
                                        @if($u->is_admin)
                                            <span class="text-slate-400 font-medium">Semua Kelas</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-md bg-amber-100 text-amber-900 font-black text-xs">
                                                Kelas {{ $u->grade }} SD
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-slate-400 text-[11px]">
                                        {{ $u->created_at ? $u->created_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button onclick="editUser({{ json_encode($u) }})"
                                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-100 text-slate-700 hover:text-indigo-700 font-bold text-xs transition flex items-center gap-1">
                                                <i class="fa-solid fa-pen text-[11px]"></i> Edit
                                            </button>
                                            <form action="{{ route('admin.users.delete', $u->id) }}" method="POST" onsubmit="return confirm('Hapus akun user {{ $u->name }} ({{ $u->username }})?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-700 hover:text-rose-700 font-bold text-xs transition flex items-center gap-1">
                                                    <i class="fa-solid fa-trash text-[11px]"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-medium">Belum ada data akun user terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        @endif

    </main>

    <!-- ================= MODAL: TAMBAH USER / AKUN ================= -->
    <div id="modalCreateUser" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i> Tambah Akun Baru
                </h3>
                <button onclick="closeModal('modalCreateUser')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                <div>
                    <label class="block mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Username (Login) *</label>
                    <input type="text" name="username" required placeholder="Contoh: budi123"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Nomor Induk (NISN / NIP / NIK) *</label>
                    <input type="text" name="nomor_induk" required placeholder="Contoh: 202401005 / 198503..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Minimal 4 karakter"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1">Tingkat Kelas *</label>
                        <select name="grade" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                            @for($g = 1; $g <= 6; $g++)
                                <option value="{{ $g }}">Kelas {{ $g }} SD</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Avatar / Foto Profil</label>
                        <select name="avatar" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                            <option value="av1">Kelinci Ceria (av1)</option>
                            <option value="av2">Beruang Pintar (av2)</option>
                            <option value="av3">Robot Cerdas (av3)</option>
                            <option value="av4">Astronot Cilik (av4)</option>
                            <option value="av5">Juara Super (av5)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_admin" value="1" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="text-xs font-bold text-slate-800">Beri Hak Akses Guru / Admin</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closeModal('modalCreateUser')" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: EDIT USER / AKUN ================= -->
    <div id="modalEditUser" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-indigo-600"></i> Edit Akun Pengguna
                </h3>
                <button onclick="closeModal('modalEditUser')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form id="formEditUser" action="" method="POST" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                @method('PUT')

                <div>
                    <label class="block mb-1">Nama Lengkap *</label>
                    <input type="text" id="edit_user_name" name="name" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Username *</label>
                    <input type="text" id="edit_user_username" name="username" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Nomor Induk (NISN / NIP / NIK)</label>
                    <input type="text" id="edit_user_nomor_induk" name="nomor_induk" placeholder="Nomor Induk Siswa/Guru"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Password Baru (Kosongkan jika tidak ingin diubah)</label>
                    <input type="password" id="edit_user_password" name="password" placeholder="Kosongkan jika tidak ganti password"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1">Tingkat Kelas *</label>
                        <select id="edit_user_grade" name="grade" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                            @for($g = 1; $g <= 6; $g++)
                                <option value="{{ $g }}">Kelas {{ $g }} SD</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">Avatar / Foto Profil</label>
                        <select id="edit_user_avatar" name="avatar" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                            <option value="av1">Kelinci Ceria (av1)</option>
                            <option value="av2">Beruang Pintar (av2)</option>
                            <option value="av3">Robot Cerdas (av3)</option>
                            <option value="av4">Astronot Cilik (av4)</option>
                            <option value="av5">Juara Super (av5)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit_user_is_admin" name="is_admin" value="1" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="text-xs font-bold text-slate-800">Beri Hak Akses Guru / Admin</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closeModal('modalEditUser')" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: TAMBAH UJIAN ================= -->
    <div id="modalCreateSubject" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-indigo-600"></i> Tambah Ujian Baru
                </h3>
                <button onclick="closeModal('modalCreateSubject')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                <div>
                    <label class="block mb-1">Mata Pelajaran / Judul Ujian *</label>
                    <input type="text" name="title" required placeholder="Contoh: Matematika Pecahan & Desimal"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1">Tingkat Kelas (Ganti Kelas) *</label>
                        <select name="grade" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                            @for($g = 1; $g <= 6; $g++)
                                <option value="{{ $g }}" {{ ($grade == $g) ? 'selected' : '' }}>Kelas {{ $g }} SD</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1">Waktu Pengerjaan (Menit) *</label>
                        <input type="number" name="duration_minutes" value="45" min="5" max="180" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1">Maksimum Pelanggaran *</label>
                        <input type="number" name="max_violations" value="3" min="1" max="10" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400 font-normal">Batas sebelum ujian terkunci</span>
                    </div>

                    <div>
                        <label class="block mb-1">Kode Remedi / Buka Kunci *</label>
                        <input type="text" name="remedy_code" value="REMEDI{{ $grade ?: 5 }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none uppercase font-bold text-amber-900 bg-amber-50">
                        <span class="text-[10px] text-slate-400 font-normal">Diberikan ke siswa jika terkunci</span>
                    </div>
                </div>

                <div>
                    <label class="block mb-1">Deskripsi Singkat</label>
                    <textarea name="description" rows="2" placeholder="Ujian semester berbasis komputer..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closeModal('modalCreateSubject')" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Simpan Ujian</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: EDIT UJIAN ================= -->
    <div id="modalEditSubject" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-pen text-indigo-600"></i> Edit Pengaturan Ujian
                </h3>
                <button onclick="closeModal('modalEditSubject')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form id="formEditSubject" method="POST" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1">Mata Pelajaran / Judul Ujian *</label>
                    <input type="text" id="edit_subj_title" name="title" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1">Ganti Kelas (Tingkat) *</label>
                        <select id="edit_subj_grade" name="grade" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                            @for($g = 1; $g <= 6; $g++)
                                <option value="{{ $g }}">Kelas {{ $g }} SD</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1">Waktu Pengerjaan (Menit) *</label>
                        <input type="number" id="edit_subj_duration" name="duration_minutes" min="5" max="180" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block mb-1">Maksimum Pelanggaran *</label>
                        <input type="number" id="edit_subj_max_viol" name="max_violations" min="1" max="10" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block mb-1">Kode Remedi / Buka Kunci *</label>
                        <input type="text" id="edit_subj_remedy" name="remedy_code" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none uppercase font-bold text-amber-900 bg-amber-50">
                    </div>
                </div>

                <div>
                    <label class="block mb-1">Deskripsi Singkat</label>
                    <textarea id="edit_subj_desc" name="description" rows="2"
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closeModal('modalEditSubject')" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: TAMBAH SOAL (DENGAN GAMBAR) ================= -->
    <div id="modalCreateQuestion" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-file-circle-plus text-emerald-600"></i> Tambah Soal Baru
                    </h3>
                    <p class="text-xs text-slate-500">Mata Pelajaran: <strong>{{ $selectedSubject?->title }} (Kelas {{ $selectedSubject?->grade }})</strong></p>
                </div>
                <button onclick="closeModal('modalCreateQuestion')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.questions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $selectedSubject?->id }}">

                <!-- Teks Soal -->
                <div>
                    <label class="block mb-1 text-slate-900">Pertanyaan Soal *</label>
                    <textarea name="prompt" rows="3" required placeholder="Tuliskan butir soal di sini..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <!-- Gambar Soal -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-2">
                    <label class="block text-slate-900 font-bold flex items-center gap-1.5">
                        <i class="fa-regular fa-image text-indigo-600"></i> Sisipkan Gambar di Soal (Opsional)
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">Upload File Gambar (JPG/PNG/WebP):</span>
                            <input type="file" name="question_image_file" accept="image/*"
                                   class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">Atau Gunakan Link / URL Gambar:</span>
                            <input type="text" name="question_image_url" placeholder="https://..."
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- 4 Pilihan Jawaban (A, B, C, D) + Gambar Pilihan -->
                <div class="space-y-3">
                    <label class="block text-slate-900 font-bold flex items-center justify-between">
                        <span>4 Pilihan Jawaban & Gambar Pilihan *</span>
                        <span class="text-[11px] font-normal text-slate-500">Pilih Radio untuk Jawaban Benar</span>
                    </label>

                    @foreach(['A', 'B', 'C', 'D'] as $optIdx => $label)
                        <div class="p-3 rounded-2xl border border-slate-200 bg-white space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" id="correct_opt_{{ $optIdx }}" name="correct_answer_index" value="{{ $optIdx }}" {{ $optIdx == 0 ? 'checked' : '' }}
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <label for="correct_opt_{{ $optIdx }}" class="w-6 h-6 rounded-md bg-slate-100 font-black text-xs flex items-center justify-center shrink-0 cursor-pointer">
                                    {{ $label }}
                                </label>
                                <input type="text" name="option_{{ $optIdx }}" required placeholder="Teks Pilihan {{ $label }}..."
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>

                            <!-- Gambar Pilihan Jawaban -->
                            <div class="pl-8 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                <input type="file" name="opt_image_file_{{ $optIdx }}" accept="image/*"
                                       class="w-full text-[10px] text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-slate-100 file:text-slate-700">
                                <input type="text" name="opt_image_url_{{ $optIdx }}" placeholder="URL Gambar Opsi {{ $label }}..."
                                       class="w-full px-2.5 py-1 rounded-md border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none text-[11px]">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pembahasan Soal -->
                <div>
                    <label class="block mb-1 text-slate-900">Pembahasan / Penjelasan Soal</label>
                    <textarea name="explanation" rows="2" placeholder="Penjelasan cara menjawab soal ini untuk siswa..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closeModal('modalCreateQuestion')" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">Simpan Soal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: EDIT SOAL (LENGKAP DENGAN GAMBAR & KUNCI) ================= -->
    <div id="modalEditQuestion" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-indigo-600"></i> Edit Soal Ujian
                    </h3>
                    <p class="text-xs text-slate-500">Mata Pelajaran: <strong>{{ $selectedSubject?->title }} (Kelas {{ $selectedSubject?->grade }})</strong></p>
                </div>
                <button onclick="closeModal('modalEditQuestion')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form id="formEditQuestion" action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                @method('PUT')

                <!-- Teks Soal -->
                <div>
                    <label class="block mb-1 text-slate-900">Pertanyaan Soal *</label>
                    <textarea id="edit_q_prompt" name="prompt" rows="3" required placeholder="Tuliskan butir soal di sini..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <!-- Gambar Soal -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-2">
                    <label class="block text-slate-900 font-bold flex items-center justify-between">
                        <span class="flex items-center gap-1.5"><i class="fa-regular fa-image text-indigo-600"></i> Gambar Soal</span>
                        <span id="edit_q_img_status" class="text-[11px] font-normal text-slate-500"></span>
                    </label>
                    <div id="edit_q_img_preview" class="hidden mb-2">
                        <img id="edit_q_img_tag" src="" alt="Preview" class="max-h-36 rounded-xl border border-slate-300 object-contain bg-white p-1">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">Ganti File Gambar (JPG/PNG/WebP):</span>
                            <input type="file" name="question_image_file" accept="image/*"
                                   class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">Atau Gunakan Link / URL Gambar:</span>
                            <input type="text" id="edit_q_image_url" name="question_image_url" placeholder="https://..."
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- 4 Pilihan Jawaban (A, B, C, D) + Gambar Pilihan -->
                <div class="space-y-3">
                    <label class="block text-slate-900 font-bold flex items-center justify-between">
                        <span>4 Pilihan Jawaban & Gambar Pilihan *</span>
                        <span class="text-[11px] font-normal text-slate-500">Pilih Radio untuk Jawaban Benar</span>
                    </label>

                    @foreach(['A', 'B', 'C', 'D'] as $optIdx => $label)
                        <div class="p-3 rounded-2xl border border-slate-200 bg-white space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="radio" id="edit_correct_opt_{{ $optIdx }}" name="correct_answer_index" value="{{ $optIdx }}"
                                       class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                                <label for="edit_correct_opt_{{ $optIdx }}" class="w-6 h-6 rounded-md bg-slate-100 font-black text-xs flex items-center justify-center shrink-0 cursor-pointer">
                                    {{ $label }}
                                </label>
                                <input type="text" id="edit_q_opt_{{ $optIdx }}" name="option_{{ $optIdx }}" required placeholder="Teks Pilihan {{ $label }}..."
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>

                            <!-- Gambar Pilihan Jawaban -->
                            <div class="pl-8 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                <input type="file" name="opt_image_file_{{ $optIdx }}" accept="image/*"
                                       class="w-full text-[10px] text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-slate-100 file:text-slate-700">
                                <input type="text" id="edit_q_opt_img_{{ $optIdx }}" name="opt_image_url_{{ $optIdx }}" placeholder="URL Gambar Opsi {{ $label }}..."
                                       class="w-full px-2.5 py-1 rounded-md border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-[11px]">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pembahasan Soal -->
                <div>
                    <label class="block mb-1 text-slate-900">Pembahasan / Penjelasan Soal</label>
                    <textarea id="edit_q_explanation" name="explanation" rows="2" placeholder="Penjelasan cara menjawab soal ini untuk siswa..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t">
                    <button type="button" onclick="closeModal('modalEditQuestion')" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md">Simpan Perubahan Soal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Javascript Handlers -->
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function editSubject(subject) {
            document.getElementById('edit_subj_title').value = subject.title;
            document.getElementById('edit_subj_grade').value = subject.grade;
            document.getElementById('edit_subj_duration').value = subject.duration_minutes;
            document.getElementById('edit_subj_max_viol').value = subject.max_violations || 3;
            document.getElementById('edit_subj_remedy').value = subject.remedy_code || ('REMEDI' + subject.grade);
            document.getElementById('edit_subj_desc').value = subject.description || '';

            const form = document.getElementById('formEditSubject');
            form.action = `/admin/subjects/${subject.id}`;

            openModal('modalEditSubject');
        }

        function editQuestion(q) {
            document.getElementById('edit_q_prompt').value = q.prompt || '';
            document.getElementById('edit_q_explanation').value = q.explanation || '';
            document.getElementById('edit_q_image_url').value = q.question_image || '';

            const previewBox = document.getElementById('edit_q_img_preview');
            const previewTag = document.getElementById('edit_q_img_tag');
            const imgStatus = document.getElementById('edit_q_img_status');

            if (q.question_image) {
                previewTag.src = q.question_image;
                previewBox.classList.remove('hidden');
                imgStatus.textContent = '(Gambar terpasang)';
            } else {
                previewBox.classList.add('hidden');
                imgStatus.textContent = '(Belum ada gambar)';
            }

            // Parse options
            let opts = q.options;
            if (typeof opts === 'string') {
                try { opts = JSON.parse(opts); } catch(e) { opts = []; }
            }
            if (Array.isArray(opts)) {
                for (let i = 0; i < 4; i++) {
                    const el = document.getElementById(`edit_q_opt_${i}`);
                    if (el) el.value = opts[i] || '';
                }
            }

            // Parse option images
            let optImgs = q.option_images;
            if (typeof optImgs === 'string') {
                try { optImgs = JSON.parse(optImgs); } catch(e) { optImgs = []; }
            }
            for (let i = 0; i < 4; i++) {
                const el = document.getElementById(`edit_q_opt_img_${i}`);
                if (el) el.value = (Array.isArray(optImgs) && optImgs[i]) ? optImgs[i] : '';
            }

            // Select radio correct answer
            const correctIdx = parseInt(q.correct_answer_index) || 0;
            const radio = document.getElementById(`edit_correct_opt_${correctIdx}`);
            if (radio) radio.checked = true;

            const form = document.getElementById('formEditQuestion');
            form.action = `/admin/questions/${q.id}`;

            openModal('modalEditQuestion');
        }

        function editUser(u) {
            document.getElementById('edit_user_name').value = u.name || '';
            document.getElementById('edit_user_username').value = u.username || '';
            document.getElementById('edit_user_nomor_induk').value = u.nomor_induk || '';
            document.getElementById('edit_user_password').value = '';
            document.getElementById('edit_user_grade').value = u.grade || 1;
            document.getElementById('edit_user_avatar').value = u.avatar || 'av1';
            document.getElementById('edit_user_is_admin').checked = !!u.is_admin;

            const form = document.getElementById('formEditUser');
            form.action = `/admin/users/${u.id}`;

            openModal('modalEditUser');
        }
    </script>
</body>
</html>
