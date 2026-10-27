<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin CAT SD - Panel Manajemen Ujian & Bank Soal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm 8mm;
            }
            header, nav, .no-print, [class*="no-print"] {
                display: none !important;
            }
            body {
                background: white !important;
                color: #0f172a !important;
                font-family: 'Plus Jakarta Sans', Arial, sans-serif !important;
                font-size: 10px !important;
                line-height: 1.25 !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            main {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .shadow-sm, .shadow-md, .shadow-lg, .shadow-2xl {
                box-shadow: none !important;
            }
            .rounded-3xl, .rounded-2xl, .rounded-xl {
                border-radius: 4px !important;
            }
            .border, .border-slate-200 {
                border-color: #cbd5e1 !important;
            }
            .print-only {
                display: block !important;
            }
            .overflow-x-auto {
                overflow: visible !important;
            }
            table {
                border-collapse: collapse !important;
                width: 100% !important;
                page-break-inside: auto;
                font-size: 9.5px !important;
            }
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            thead {
                display: table-header-group !important;
            }
            th, td {
                border: 1px solid #94a3b8 !important;
                padding: 4px 6px !important;
                color: #0f172a !important;
            }
            th {
                background-color: #f1f5f9 !important;
                font-weight: 800 !important;
                text-align: center !important;
            }
            .badge-print {
                border: 1px solid #64748b !important;
                background: #f8fafc !important;
                color: #0f172a !important;
                font-weight: 700 !important;
                padding: 1px 4px !important;
            }
            .student-detail-subrow {
                display: none !important;
            }
        }
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
                <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}#settings-class-code" class="bg-indigo-900/90 hover:bg-indigo-950 px-3 py-1.5 rounded-lg border border-amber-400/40 text-amber-300 transition shadow-sm flex items-center gap-1.5 font-bold" title="Kode verifikasi bagi siswa saat ingin berpindah kelas">
                    <i class="fa-solid fa-key text-amber-400"></i> Kode Kelas: <code class="bg-amber-400/20 px-1 rounded text-white">{{ $classChangeCode }}</code>
                </a>
                <a href="{{ route('admin.dashboard', ['tab' => 'monitoring']) }}" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-bold px-3 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved"></i> Monitor AI ({{ $stats['total_violations'] }})
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Flash Message -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm flex items-center justify-between no-print">
                <div class="flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-sm no-print">
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

        <!-- Top Navigation Tabs (Ujian & Bank Soal, Monitoring AI Proctoring, Rekap Nilai Per Kelas, Manajemen Akun) -->
        <div class="flex items-center gap-2 sm:gap-3 border-b border-slate-200 pb-3 flex-wrap no-print">
            <a href="{{ route('admin.dashboard', ['tab' => 'exams', 'grade' => $grade]) }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition flex items-center gap-2 {{ ($tab ?? 'exams') === 'exams' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-graduation-cap text-sm"></i> Bank Soal & Ujian
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'monitoring']) }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition flex items-center gap-2 {{ ($tab ?? 'exams') === 'monitoring' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-shield-halved text-sm {{ ($tab ?? 'exams') === 'monitoring' ? 'text-white' : 'text-amber-500' }}"></i> Monitoring AI Proctoring & Hasil Ujian
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ ($tab ?? 'exams') === 'monitoring' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $stats['total_violations'] }} Log</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'recap', 'recap_grade' => $recapGrade]) }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition flex items-center gap-2 {{ ($tab ?? 'exams') === 'recap' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-chart-column text-sm {{ ($tab ?? 'exams') === 'recap' ? 'text-white' : 'text-indigo-600' }}"></i> Rekap Nilai Per Kelas
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ ($tab ?? 'exams') === 'recap' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700' }}">6 Kelas SD</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}"
               class="px-5 py-2.5 rounded-2xl text-xs font-extrabold transition flex items-center gap-2 {{ ($tab ?? 'exams') === 'users' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                <i class="fa-solid fa-users-gear text-sm"></i> Manajemen Akun & Password ({{ $users->count() }})
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
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-inner">
                                        @if(str_contains($subj->icon_name ?? '', '/') || str_contains($subj->icon_name ?? '', '.'))
                                            <img src="{{ $subj->icon_name }}" class="w-full h-full object-contain p-0.5">
                                        @elseif(($subj->icon_name ?? '') === 'calculate')
                                            <i class="fa-solid fa-calculator text-indigo-600 text-base"></i>
                                        @elseif(($subj->icon_name ?? '') === 'science')
                                            <i class="fa-solid fa-flask text-emerald-600 text-base"></i>
                                        @elseif(($subj->icon_name ?? '') === 'menu_book')
                                            <i class="fa-solid fa-book-open text-blue-600 text-base"></i>
                                        @elseif(($subj->icon_name ?? '') === 'flag')
                                            <i class="fa-solid fa-flag text-rose-600 text-base"></i>
                                        @elseif(($subj->icon_name ?? '') === 'palette')
                                            <i class="fa-solid fa-palette text-amber-600 text-base"></i>
                                        @elseif(($subj->icon_name ?? '') === 'sports_soccer')
                                            <i class="fa-solid fa-futbol text-teal-600 text-base"></i>
                                        @else
                                            <i class="fa-solid fa-graduation-cap text-indigo-600 text-base"></i>
                                        @endif
                                    </div>
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
                                <div class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded-lg flex items-center gap-1 font-semibold col-span-2">
                                    <i class="fa-solid fa-shuffle text-[10px] text-indigo-500"></i>
                                    <span>Tampil per Sesi: <strong>{{ min($subj->max_questions ?? 20, $subj->questions_count) }} Soal Acak</strong></span>
                                    <span class="text-[10px] text-indigo-400 font-normal ml-auto">({{ $subj->questions_count }} di bank)</span>
                                </div>
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
                                <span><i class="fa-solid fa-layer-group text-slate-400"></i> Total {{ $subj->questions_count }} Soal</span>
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
                                <span class="inline-flex items-center gap-1 text-indigo-700 font-bold bg-indigo-50 px-2 py-0.5 rounded-md">
                                    <i class="fa-solid fa-shuffle text-indigo-500"></i>
                                    Rotasi Acak: {{ $selectedSubject->max_questions ?? 20 }} Soal / Siswa
                                </span>
                                <span><i class="fa-solid fa-layer-group text-slate-400"></i> Bank Soal: <strong>{{ count($questions) }} Soal</strong></span>
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
                                    <th class="py-2 px-3 text-center">Aksi</th>
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
                                        <td class="py-2 px-3 text-center">
                                            <form action="{{ route('admin.exam-results.delete', $res->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat ujian {{ $res->subject->title ?? 'Ujian' }} siswa {{ $res->user->name ?? 'Siswa' }}? Siswa dapat mengambil ulang ujian ini.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Ujian (Reset agar siswa dapat mengerjakan lagi)"
                                                        class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1 border border-rose-200">
                                                    <i class="fa-solid fa-trash text-[10px]"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
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
        @elseif(($tab ?? 'exams') === 'monitoring')
        <!-- ================= TAB: MONITORING AI PROCTORING & HASIL UJIAN ================= -->
        <section class="space-y-6">

            <!-- Monitoring Top Banner & Actions -->
            <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white p-6 rounded-3xl shadow-lg border border-indigo-700/50 flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 bg-indigo-500/30 px-3 py-1 rounded-full text-xs font-bold text-amber-300 border border-amber-300/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        AI Proctoring Live Monitor
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-white flex items-center gap-2.5">
                        <i class="fa-solid fa-shield-cat text-amber-400"></i>
                        Pusat Pantauan AI Proctoring & Hasil Ujian Siswa
                    </h2>
                    <p class="text-xs text-indigo-200">Pantau aktivitas ujian siswa, pelanggaran proctoring, dan kelola atau hapus ujian yang telah diambil.</p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Refresh Button -->
                    <button onclick="window.location.reload()" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition border border-white/20 flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-rotate text-xs"></i> Segarkan Data
                    </button>

                    <!-- Auto Refresh Toggle -->
                    <button id="btnAutoRefresh" onclick="toggleAutoRefresh()" class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold text-xs transition border border-emerald-400/30 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400" id="dotAutoRefresh"></span>
                        <span id="textAutoRefresh">Auto-Refresh: Aktif (10s)</span>
                    </button>

                    <!-- Clear Logs Button -->
                    <form action="{{ route('admin.proctoring.clear') }}" method="POST" onsubmit="return confirm('Yakin ingin membersihkan semua catatan riwayat pelanggaran AI proctoring?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 font-bold text-xs transition border border-rose-400/30 flex items-center gap-2">
                            <i class="fa-solid fa-trash-can text-xs"></i> Bersihkan Log
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xl font-bold">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400">Total Ujian Dikerjakan</p>
                        <p class="text-xl font-black text-slate-900">{{ $stats['total_results'] }}</p>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 text-xl font-bold">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400">Pelanggaran Terdeteksi</p>
                        <p class="text-xl font-black text-rose-600">{{ $stats['total_violations'] }}</p>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl font-bold">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400">Siswa Terdaftar</p>
                        <p class="text-xl font-black text-emerald-600">{{ $stats['total_students'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Tabel 1: Riwayat & Hasil Ujian Siswa (dengan Fitur Hapus Ujian) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-file-signature text-indigo-600"></i>
                            Hasil Pengerjaan Ujian Siswa ({{ $recentResults->count() }})
                        </h3>
                        <p class="text-xs text-slate-500">Admin dapat menghapus ujian yang telah diambil jika siswa memerlukan remedi atau ingin ujian ulang.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-black text-[11px] border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Siswa</th>
                                <th class="py-3.5 px-4">Kelas & Ujian</th>
                                <th class="py-3.5 px-4 text-center">Nilai / Skor</th>
                                <th class="py-3.5 px-4">Rincian Jawaban</th>
                                <th class="py-3.5 px-4">Waktu Pengerjaan</th>
                                <th class="py-3.5 px-4">Tanggal Ujian</th>
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentResults as $res)
                                @php
                                    $scoreBg = $res->score >= 75 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($res->score >= 50 ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200');
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($res->user->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $res->user->name ?? 'Siswa' }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">{{ $res->user->username ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="font-bold text-slate-800">{{ $res->subject->title ?? 'Ujian' }}</p>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-600">
                                            Kelas {{ $res->grade }} SD
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-3 py-1 rounded-xl text-sm font-black border {{ $scoreBg }} inline-block">
                                            {{ $res->score }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        <div class="space-y-0.5 text-[11px]">
                                            <span class="text-emerald-700 font-bold"><i class="fa-solid fa-check"></i> {{ $res->correct_count }} Benar</span> •
                                            <span class="text-rose-700 font-bold"><i class="fa-solid fa-xmark"></i> {{ $res->wrong_count }} Salah</span> •
                                            <span class="text-slate-500 font-bold"><i class="fa-solid fa-minus"></i> {{ $res->unanswered_count }} Kosong</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-600 text-[11px]">
                                        <i class="fa-regular fa-clock text-slate-400 mr-1"></i>
                                        {{ floor($res->time_spent_seconds / 60) }}m {{ $res->time_spent_seconds % 60 }}s
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-[11px]">
                                        <p class="font-semibold">{{ $res->created_at ? $res->created_at->format('d M Y, H:i') : '-' }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $res->created_at ? $res->created_at->diffForHumans() : '' }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <form action="{{ route('admin.exam-results.delete', $res->id) }}" method="POST"
                                              onsubmit="return confirm('HAPUS UJIAN INI?\n\nSiswa: {{ $res->user->name ?? 'Siswa' }}\nMapel: {{ $res->subject->title ?? 'Ujian' }}\nSkor: {{ $res->score }}\n\nSetelah dihapus, siswa dapat mengambil dan mengerjakan ulang ujian ini.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 font-bold text-xs transition border border-rose-200 inline-flex items-center gap-1.5 shadow-sm">
                                                <i class="fa-solid fa-trash text-xs"></i> Hapus Ujian
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-clipboard-question text-3xl text-slate-300 mb-2"></i>
                                        <p class="font-semibold">Belum ada siswa yang mengambil atau menyelesaikan ujian.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Log Catatan Pelanggaran AI Proctoring (Full Width) -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-3">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                            Log Catatan Pelanggaran AI Proctoring ({{ $recentViolations->count() }})
                        </h3>
                        <p class="text-xs text-slate-500">Terdeteksi otomatis oleh ML Kit Face Tracking & Pengawas Aplikasi CAT</p>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-black text-[10px] border-b sticky top-0 z-10">
                            <tr>
                                <th class="py-2.5 px-3">Siswa</th>
                                <th class="py-2.5 px-3">Tipe Pelanggaran</th>
                                <th class="py-2.5 px-3">Keterangan</th>
                                <th class="py-2.5 px-3">Soal</th>
                                <th class="py-2.5 px-3">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentViolations as $log)
                                @php
                                    $typeBadge = match($log->violation_type) {
                                        'SWITCH_TAB'        => ['bg' => 'bg-purple-100 text-purple-800 border-purple-200', 'icon' => 'fa-arrow-up-right-from-square', 'label' => 'Pindah Aplikasi'],
                                        'NO_FACE'           => ['bg' => 'bg-rose-100 text-rose-800 border-rose-200', 'icon' => 'fa-user-slash', 'label' => 'Wajah Hilang'],
                                        'MULTIPLE_FACES'    => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'icon' => 'fa-users', 'label' => 'Multi Wajah'],
                                        'LOOKING_AWAY'      => ['bg' => 'bg-orange-100 text-orange-800 border-orange-200', 'icon' => 'fa-eye-slash', 'label' => 'Menoleh'],
                                        'RAPID_ANSWERING'   => ['bg' => 'bg-yellow-100 text-yellow-900 border-yellow-200', 'icon' => 'fa-bolt', 'label' => 'Jawab Kilat'],
                                        'SNAPSHOT_CAPTURED' => ['bg' => 'bg-cyan-100 text-cyan-800 border-cyan-200', 'icon' => 'fa-camera', 'label' => 'Snapshot'],
                                        default             => ['bg' => 'bg-slate-100 text-slate-800 border-slate-200', 'icon' => 'fa-shield-halved', 'label' => $log->violation_type],
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-2.5 px-3 font-bold text-slate-900">
                                        {{ $log->user->name ?? 'Siswa' }}
                                        <span class="block text-[10px] text-slate-400 font-normal">Kls {{ $log->user->grade ?? '-' }} SD</span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black border {{ $typeBadge['bg'] }} inline-flex items-center gap-1">
                                            <i class="fa-solid {{ $typeBadge['icon'] }} text-[9px]"></i>
                                            {{ $typeBadge['label'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-700 font-medium">
                                        {{ $log->description }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 font-mono text-[11px]">
                                        {{ $log->question_index !== null ? '#' . ($log->question_index + 1) : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-400 text-[11px] whitespace-nowrap">
                                        {{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }}
                                        <span class="block text-[10px]">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        <i class="fa-solid fa-circle-check text-2xl text-emerald-400 mb-1"></i>
                                        <p class="font-bold">Tidak ada catatan pelanggaran.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
        @elseif(($tab ?? 'exams') === 'recap')
        <!-- ================= TAB: REKAP NILAI PER KELAS ================= -->
        <section class="space-y-6">

            <!-- Official Header Khusus Cetak / Print -->
            <div class="print-only hidden mb-4 pb-3 border-b-2 border-slate-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="text-3xl">🎒</div>
                        <div>
                            <h1 class="text-base font-black uppercase tracking-wider text-slate-900 leading-tight">
                                LAPORAN REKAPITULASI HASIL UJIAN SISWA
                            </h1>
                            <p class="text-xs font-bold text-slate-700">
                                Computer Assisted Test (CAT) SD &bull; {{ $recapGrade ? 'Kelas ' . $recapGrade . ' SD' : 'Semua Tingkat Kelas (1 s.d. 6 SD)' }}
                            </p>
                            <p class="text-[10px] text-slate-500">
                                Tahun Ajaran {{ date('Y') }}/{{ date('Y') + 1 }} &bull; Dokumen Evaluasi Hasil Belajar Resmi
                            </p>
                        </div>
                    </div>
                    <div class="text-right text-[10px] text-slate-600 space-y-0.5">
                        <p class="font-extrabold text-xs text-indigo-900">
                            {{ $recapGrade ? 'REKAP KELAS ' . $recapGrade . ' SD' : 'REKAP SELURUH KELAS SD' }}
                        </p>
                        <p>Jumlah Siswa: <strong>{{ $detailedStudents->count() }} Siswa</strong></p>
                        <p>Waktu Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
            </div>

            <!-- Banner -->
            <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white p-6 rounded-3xl shadow-lg border border-indigo-700/50 flex flex-wrap items-center justify-between gap-4 no-print">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 bg-indigo-500/30 px-3 py-1 rounded-full text-xs font-bold text-amber-300 border border-amber-300/30">
                        <i class="fa-solid fa-chart-bar"></i> Rekap Akademik
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-white flex items-center gap-2.5">
                        <i class="fa-solid fa-chart-column text-amber-400"></i>
                        Rekap Nilai Ujian Per Kelas — Kelas 1 s.d. 6 SD
                    </h2>
                    <p class="text-xs text-indigo-200">
                        Pantau akumulasi nilai, rata-rata, kelulusan, dan riwayat ujian seluruh siswa. Siap cetak pas format A4.
                    </p>
                </div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition border border-indigo-400/40 flex items-center gap-2 shadow-md">
                        <i class="fa-solid fa-print text-xs"></i> Cetak Rekap (Print A4)
                    </button>
                    @if($recapGrade)
                    <a href="{{ route('admin.dashboard', ['tab' => 'recap']) }}" class="px-4 py-2 rounded-xl bg-amber-400/20 hover:bg-amber-400/30 text-amber-300 font-bold text-xs transition border border-amber-400/30 flex items-center gap-2">
                        <i class="fa-solid fa-table-list text-xs"></i> Tampilkan Semua Kelas
                    </a>
                    @endif
                </div>
            </div>

            <!-- Filter Cepat Tingkat Kelas (Pill Bar) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 no-print">
                <span class="text-xs font-extrabold text-slate-500 shrink-0 mr-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-filter text-indigo-600"></i> Filter Kelas:
                </span>
                <a href="{{ route('admin.dashboard', ['tab' => 'recap']) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 {{ empty($recapGrade) ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-400' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                    <i class="fa-solid fa-layer-group text-[11px]"></i> Semua Kelas (1-6 SD)
                </a>
                @for($k = 1; $k <= 6; $k++)
                    <a href="{{ route('admin.dashboard', ['tab' => 'recap', 'recap_grade' => $k]) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 {{ (string)$recapGrade === (string)$k ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-400' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                        <i class="fa-solid fa-school text-[11px]"></i> Kelas {{ $k }} SD
                    </a>
                @endfor
            </div>

            <!-- Grid Kartu Ringkasan Per Kelas (1-6 SD) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 no-print">
                @foreach($classRecaps as $g => $recap)
                    @php
                        $isSelected = ($recapGrade == $g);
                        $passColor = $recap['pass_rate'] >= 75 ? 'emerald' : ($recap['pass_rate'] >= 50 ? 'amber' : 'rose');
                        $gradients = [
                            1 => ['from-blue-600','to-indigo-700'],
                            2 => ['from-violet-600','to-purple-700'],
                            3 => ['from-emerald-600','to-teal-700'],
                            4 => ['from-orange-500','to-amber-600'],
                            5 => ['from-rose-600','to-pink-700'],
                            6 => ['from-indigo-600','to-sky-700'],
                        ];
                        $from = $gradients[$g][0] ?? 'from-slate-600';
                        $to   = $gradients[$g][1] ?? 'to-slate-700';
                    @endphp
                    <a href="{{ route('admin.dashboard', ['tab' => 'recap', 'recap_grade' => $g]) }}"
                       class="relative rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-200 group block {{ $isSelected ? 'ring-4 ring-offset-2 ring-indigo-400 scale-[1.02]' : '' }}">
                        <div class="bg-gradient-to-br {{ $from }} {{ $to }} p-4 text-white space-y-3">
                            <!-- Kelas Label -->
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-black leading-none">{{ $g }}</span>
                                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                                    <i class="fa-solid fa-school text-white text-sm"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white/80">Kelas {{ $g }} SD</p>
                                <p class="text-lg font-black">{{ $recap['students_count'] }} Siswa</p>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-white/20 pt-2 space-y-1 text-[11px]">
                                <div class="flex justify-between">
                                    <span class="text-white/70">Rata-Rata</span>
                                    <span class="font-extrabold">{{ $recap['avg_score'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white/70">Lulus ≥70</span>
                                    <span class="font-extrabold {{ $recap['pass_rate'] >= 70 ? 'text-emerald-300' : ($recap['pass_rate'] >= 50 ? 'text-amber-300' : 'text-rose-300') }}">
                                        {{ $recap['pass_rate'] }}%
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white/70">Ujian</span>
                                    <span class="font-bold">{{ $recap['total_exams'] }}</span>
                                </div>
                            </div>
                        </div>
                        @if($isSelected)
                        <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-white flex items-center justify-center shadow">
                            <i class="fa-solid fa-check text-indigo-700 text-[10px]"></i>
                        </div>
                        @endif
                    </a>
                @endforeach
            </div>

            <!-- Header Detail Siswa -->
            <div class="flex flex-wrap items-center justify-between gap-3 no-print">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-users text-indigo-600"></i>
                        {{ $recapGrade ? 'Rekap Detail Siswa — Kelas ' . $recapGrade . ' SD' : 'Rekap Nilai Siswa — Semua Tingkat Kelas (1 s.d. 6 SD)' }}
                        <span class="text-sm font-bold text-slate-500">({{ $detailedStudents->count() }} Siswa)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $recapGrade ? 'Menampilkan riwayat ujian dan akumulasi nilai siswa Kelas ' . $recapGrade . ' SD.' : 'Menampilkan akumulasi nilai dan riwayat ujian seluruh siswa dari Kelas 1 sampai 6 SD.' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition flex items-center gap-2 shadow-md">
                        <i class="fa-solid fa-print text-xs"></i> Cetak Rekap {{ $recapGrade ? 'Kelas ' . $recapGrade : 'Semua Kelas' }}
                    </button>
                </div>
            </div>

            <!-- Tabel Rekap Siswa Detail -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left recap-print-table">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-black text-[10px] border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3 text-center">#</th>
                                <th class="py-3 px-3 text-left">Siswa</th>
                                <th class="py-3 px-3 text-center">Kelas</th>
                                <th class="py-3 px-3 text-center">Ujian<br>Diambil</th>
                                <th class="py-3 px-3 text-center">Rata-Rata<br>Nilai</th>
                                <th class="py-3 px-3 text-center">Nilai<br>Terbaik</th>
                                <th class="py-3 px-3 text-center">Nilai<br>Terendah</th>
                                <th class="py-3 px-3 text-center">Benar</th>
                                <th class="py-3 px-3 text-center">Salah</th>
                                <th class="py-3 px-3 text-center">Kosong</th>
                                <th class="py-3 px-3 text-center">Pelanggaran</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3 text-center no-print">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($detailedStudents as $idx => $student)
                                @php
                                    $avg = $student['avg_score'];
                                    $statusBg = $avg >= 70 ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : ($avg >= 50 ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200');
                                    $statusText = $avg >= 70 ? 'Lulus' : ($avg >= 50 ? 'Perlu Bimbing' : 'Perlu Remedial');
                                    $studentId = $student['id'];
                                @endphp
                                <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="toggleStudentDetail('detail-{{ $studentId }}')">
                                    <td class="py-2.5 px-3 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-xs shrink-0 no-print">
                                                {{ strtoupper(substr($student['name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 leading-tight">{{ $student['name'] }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono">{{ $student['nomor_induk'] ?? $student['username'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-indigo-50 border border-indigo-200 text-indigo-800 badge-print">
                                            Kls {{ $student['grade'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-black text-slate-800">{{ $student['total_exams'] }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-xl text-xs font-black border badge-print {{ $statusBg }}">
                                            {{ $avg }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-emerald-700">{{ $student['max_score'] ?: '-' }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-rose-700">{{ $student['min_score'] ?: '-' }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-emerald-600">{{ $student['total_correct'] }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-rose-600">{{ $student['total_wrong'] }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-500">{{ $student['total_unanswered'] }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($student['violations_count'] > 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200 badge-print">
                                                {{ $student['violations_count'] }} Log
                                            </span>
                                        @else
                                            <span class="text-slate-300 font-bold">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold border badge-print {{ $statusBg }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center no-print">
                                        <button onclick="event.stopPropagation(); toggleStudentDetail('detail-{{ $studentId }}')"
                                                class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition border border-indigo-200"
                                                title="Lihat Riwayat Ujian">
                                            <i class="fa-solid fa-chevron-down text-xs" id="icon-{{ $studentId }}"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Row Detail Ujian Siswa (toggle) -->
                                <tr id="detail-{{ $studentId }}" class="hidden bg-indigo-50/40 student-detail-subrow">
                                    <td colspan="13" class="px-6 py-4">
                                        @if($student['total_exams'] > 0)
                                        <div class="space-y-2">
                                            <p class="text-xs font-extrabold text-indigo-700 mb-2 flex items-center gap-2">
                                                <i class="fa-solid fa-list-check"></i>
                                                Riwayat Ujian {{ $student['name'] }} — Kelas {{ $student['grade'] }} SD
                                            </p>
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-[11px] border-collapse">
                                                    <thead>
                                                        <tr class="bg-white border border-slate-200 text-slate-500 uppercase text-[10px]">
                                                            <th class="py-2 px-3 font-bold text-left">Mata Pelajaran</th>
                                                            <th class="py-2 px-3 font-bold text-center">Nilai</th>
                                                            <th class="py-2 px-3 font-bold text-center">Benar</th>
                                                            <th class="py-2 px-3 font-bold text-center">Salah</th>
                                                            <th class="py-2 px-3 font-bold text-center">Kosong</th>
                                                            <th class="py-2 px-3 font-bold text-center">Durasi</th>
                                                            <th class="py-2 px-3 font-bold text-left">Tanggal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100">
                                                        @foreach($student['exam_results'] as $er)
                                                            @php
                                                                $erBg = $er->score >= 70 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : ($er->score >= 50 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200');
                                                            @endphp
                                                            <tr class="bg-white hover:bg-slate-50">
                                                                <td class="py-2 px-3 font-semibold text-slate-800">{{ $er->subject->title ?? 'Ujian' }}</td>
                                                                <td class="py-2 px-3 text-center">
                                                                    <span class="px-2.5 py-0.5 rounded-lg font-black border text-xs {{ $erBg }}">
                                                                        {{ $er->score }}
                                                                    </span>
                                                                </td>
                                                                <td class="py-2 px-3 text-center font-bold text-emerald-700">{{ $er->correct_count }}</td>
                                                                <td class="py-2 px-3 text-center font-bold text-rose-700">{{ $er->wrong_count }}</td>
                                                                <td class="py-2 px-3 text-center text-slate-500">{{ $er->unanswered_count }}</td>
                                                                <td class="py-2 px-3 text-center font-mono text-slate-600">
                                                                    {{ floor($er->time_spent_seconds / 60) }}m {{ $er->time_spent_seconds % 60 }}s
                                                                </td>
                                                                <td class="py-2 px-3 text-slate-500">{{ $er->created_at ? $er->created_at->format('d M Y, H:i') : '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @else
                                        <p class="text-xs text-slate-400 font-semibold py-2">
                                            <i class="fa-solid fa-circle-info mr-1"></i>
                                            Siswa ini belum mengambil ujian di kelas ini.
                                        </p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="py-10 text-center text-slate-400">
                                        <i class="fa-solid fa-users-slash text-3xl text-slate-300 mb-2"></i>
                                        <p class="font-semibold">Belum ada siswa terdaftar{{ $recapGrade ? ' di Kelas ' . $recapGrade . ' SD' : '' }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Area Tanda Tangan Khusus Cetak / Print -->
            <div class="print-only hidden mt-8 pt-4">
                <div class="grid grid-cols-2 text-xs text-slate-900 px-8">
                    <div class="text-left space-y-1">
                        <p>Mengetahui,</p>
                        <p class="font-bold">Kepala Sekolah SD</p>
                        <div class="h-16"></div>
                        <p class="font-bold underline">( .................................................... )</p>
                        <p class="text-[10px] text-slate-600">NIP. ....................................................</p>
                    </div>
                    <div class="text-right space-y-1">
                        <p>{{ config('app.school_city', 'Kediri') }}, {{ now()->translatedFormat('d F Y') }}</p>
                        <p class="font-bold">Guru Kelas / Administrator CAT</p>
                        <div class="h-16"></div>
                        <p class="font-bold underline">( {{ auth()->user()->name ?? 'Administrator CAT SD' }} )</p>
                        <p class="text-[10px] text-slate-600">NIP/ID: {{ auth()->user()->nomor_induk ?? '-' }}</p>
                    </div>
                </div>
            </div>

        </section>
        @else
        <!-- ================= TAB: MANAJEMEN AKUN & PASSWORD SISWA & GURU ================= -->
        <section class="space-y-6">

            <!-- Card: Pengaturan Kode Penggantian Kelas Siswa -->
            <div id="settings-class-code" class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent p-5 rounded-3xl shadow-sm border border-amber-300/60 flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-900 bg-amber-100 px-3 py-1 rounded-full border border-amber-300">
                        <i class="fa-solid fa-key text-amber-600"></i> Otorisasi Perpindahan Kelas Siswa
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Pengaturan Kode Penggantian Tingkat Kelas</h3>
                    <p class="text-xs text-slate-600">Siswa wajib memasukkan kode ini pada menu "Ganti Tingkat Kelas" di aplikasi jika ingin pindah kelas SD.</p>
                </div>

                <form action="{{ route('admin.settings.class-change-code') }}" method="POST" class="flex items-center gap-2 flex-wrap">
                    @csrf
                    <div class="flex items-center gap-2 bg-white border border-slate-300 rounded-2xl px-3 py-2 shadow-sm focus-within:ring-2 focus-within:ring-amber-500">
                        <i class="fa-solid fa-lock text-amber-500 text-xs"></i>
                        <input type="text" name="class_change_code" value="{{ $classChangeCode }}" required placeholder="Contoh: unpkediri"
                               class="bg-transparent text-xs font-mono font-bold text-slate-900 focus:outline-none w-36">
                    </div>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-2xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Simpan Kode Baru
                    </button>
                </form>
            </div>

            <!-- Header Manajemen Akun -->
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-users-gear text-indigo-600"></i>
                        Manajemen Akun Siswa & Guru
                    </h2>
                    <p class="text-xs text-slate-500">Kelola hak akses, username, password yang dapat dilihat langsung oleh admin, dan kelas akun.</p>
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
                                <th class="py-3.5 px-4">Password</th>
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
                                                <p class="text-[11px] text-slate-400">NISN/ID: {{ $u->nomor_induk ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-700 text-sm">
                                        {{ $u->username }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs">
                                        <div class="inline-flex items-center gap-2 bg-slate-100/90 px-3 py-1.5 rounded-xl border border-slate-200">
                                            <span id="pwd-{{ $u->id }}" data-pass="{{ $u->plain_password ?? 'unpkediri' }}" class="font-extrabold text-slate-800 tracking-wider">••••••••</span>
                                            <button type="button" onclick="togglePasswordVisibility('pwd-{{ $u->id }}', this)" title="Lihat/Sembunyikan Password" class="text-slate-500 hover:text-indigo-600 transition">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </button>
                                            <button type="button" onclick="copyPassword('{{ $u->plain_password ?? 'unpkediri' }}')" title="Salin Password" class="text-slate-400 hover:text-emerald-600 transition">
                                                <i class="fa-regular fa-copy text-xs"></i>
                                            </button>
                                        </div>
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
                                    <td colspan="7" class="py-8 text-center text-slate-400 font-medium">Belum ada data akun user terdaftar.</td>
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
                    <div class="flex items-center justify-between mb-1">
                        <label class="block">Password Akun</label>
                        <span class="text-[11px] text-slate-500">Password saat ini: <strong id="edit_user_current_pass" class="font-mono text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200"></strong></span>
                    </div>
                    <input type="text" id="edit_user_password" name="password" placeholder="Kosongkan jika tidak ingin ganti password"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
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

            <form action="{{ route('admin.subjects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                <div>
                    <label class="block mb-1">Mata Pelajaran / Judul Ujian *</label>
                    <input type="text" name="title" required placeholder="Contoh: Matematika Pecahan & Desimal"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Ikon / Gambar Ujian</label>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-2.5">
                        <div class="flex items-center gap-3">
                            <input type="file" name="icon_file" accept="image/*" id="create_subj_icon_file"
                                   onchange="previewSubjectIcon(this, 'create_subj_icon_preview', 'create_subj_icon_img')"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <div id="create_subj_icon_preview" class="w-10 h-10 rounded-xl bg-white border border-slate-200 hidden flex-shrink-0 flex items-center justify-center overflow-hidden shadow-sm">
                                <img id="create_subj_icon_img" src="" class="w-full h-full object-contain">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-1 border-t border-slate-200/60">
                            <span class="text-[11px] text-slate-500">Atau pilih preset ikon:</span>
                            <select name="icon_name" class="px-2.5 py-1 rounded-lg border border-slate-300 text-xs bg-white text-slate-700 focus:outline-none">
                                <option value="school">🎒 Sekolah / Umum (school)</option>
                                <option value="calculate">🔢 Matematika (calculate)</option>
                                <option value="science">🔬 Sains / IPA (science)</option>
                                <option value="menu_book">📖 Bahasa / Literasi (menu_book)</option>
                                <option value="flag">🇮🇩 PKn / Kewarganegaraan (flag)</option>
                                <option value="palette">🎨 Seni Budaya (palette)</option>
                                <option value="sports_soccer">⚽ PJOK / Olahraga (sports_soccer)</option>
                            </select>
                        </div>
                    </div>
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
                        <label class="block mb-1">Jumlah Soal per Sesi *
                            <span class="text-indigo-600 font-black ml-1">🔀</span>
                        </label>
                        <input type="number" name="max_questions" value="20" min="1" max="200" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-indigo-50 font-bold">
                        <span class="text-[10px] text-indigo-600 font-semibold">Soal acak dari bank soal — tiap siswa beda</span>
                    </div>

                    <div>
                        <label class="block mb-1">Maks Pelanggaran *</label>
                        <input type="number" name="max_violations" value="3" min="1" max="10" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400 font-normal">Batas sebelum ujian terkunci</span>
                    </div>
                </div>

                <div>
                    <label class="block mb-1">Kode Remedi / Buka Kunci *</label>
                    <input type="text" name="remedy_code" value="REMEDI{{ $grade ?: 5 }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none uppercase font-bold text-amber-900 bg-amber-50">
                    <span class="text-[10px] text-slate-400 font-normal">Diberikan ke siswa jika terkunci</span>
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

            <form id="formEditSubject" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1">Mata Pelajaran / Judul Ujian *</label>
                    <input type="text" id="edit_subj_title" name="title" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-1">Ikon / Gambar Ujian</label>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-2.5">
                        <div class="flex items-center gap-3">
                            <input type="file" name="icon_file" accept="image/*" id="edit_subj_icon_file"
                                   onchange="previewSubjectIcon(this, 'edit_subj_icon_preview', 'edit_subj_icon_img')"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <div id="edit_subj_icon_preview" class="w-10 h-10 rounded-xl bg-white border border-slate-200 hidden flex-shrink-0 flex items-center justify-center overflow-hidden shadow-sm">
                                <img id="edit_subj_icon_img" src="" class="w-full h-full object-contain">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-1 border-t border-slate-200/60">
                            <span class="text-[11px] text-slate-500">Atau pilih preset ikon:</span>
                            <select id="edit_subj_icon_name" name="icon_name" class="px-2.5 py-1 rounded-lg border border-slate-300 text-xs bg-white text-slate-700 focus:outline-none">
                                <option value="school">🎒 Sekolah / Umum (school)</option>
                                <option value="calculate">🔢 Matematika (calculate)</option>
                                <option value="science">🔬 Sains / IPA (science)</option>
                                <option value="menu_book">📖 Bahasa / Literasi (menu_book)</option>
                                <option value="flag">🇮🇩 PKn / Kewarganegaraan (flag)</option>
                                <option value="palette">🎨 Seni Budaya (palette)</option>
                                <option value="sports_soccer">⚽ PJOK / Olahraga (sports_soccer)</option>
                            </select>
                        </div>
                    </div>
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
                        <label class="block mb-1">Jumlah Soal per Sesi *
                            <span class="text-indigo-600 font-black ml-1">🔀</span>
                        </label>
                        <input type="number" id="edit_subj_max_questions" name="max_questions" min="1" max="200" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-indigo-50 font-bold">
                        <span class="text-[10px] text-indigo-600 font-semibold">Soal acak dari bank soal — tiap siswa beda</span>
                    </div>

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

                <!-- Pilihan Jawaban Dinamis (A, B, C, D, E, ...) + Gambar Pilihan -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-slate-900 font-bold">
                            Pilihan Jawaban & Gambar Pilihan *
                        </label>
                        <button type="button" onclick="addOptionToForm('create_options_list', 'create')"
                                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl font-bold text-xs border border-emerald-300 transition flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-plus text-xs"></i> Tambah Pilihan Jawaban
                        </button>
                    </div>

                    <div id="create_options_list" class="space-y-2.5">
                        <!-- Default 4 options rendered initially, expandable to any count -->
                    </div>
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

                <!-- Pilihan Jawaban Dinamis (A, B, C, D, E, ...) + Gambar Pilihan -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-slate-900 font-bold">
                            Pilihan Jawaban & Gambar Pilihan *
                        </label>
                        <button type="button" onclick="addOptionToForm('edit_options_list', 'edit')"
                                class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl font-bold text-xs border border-indigo-300 transition flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-plus text-xs"></i> Tambah Pilihan Jawaban
                        </button>
                    </div>

                    <div id="edit_options_list" class="space-y-2.5">
                        <!-- Populated dynamically via JS editQuestion -->
                    </div>
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
            if (id === 'modalCreateQuestion') {
                const list = document.getElementById('create_options_list');
                if (!list.children.length) {
                    initDefaultCreateOptions();
                }
            }
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        // Toggle baris detail riwayat ujian siswa di tab Rekap Nilai Per Kelas
        function toggleStudentDetail(rowId) {
            const row = document.getElementById(rowId);
            if (!row) return;
            const studentId = rowId.replace('detail-', '');
            const icon = document.getElementById('icon-' + studentId);
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            } else {
                row.classList.add('hidden');
                if (icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        }

        function getOptionLabel(idx) {
            return String.fromCharCode(65 + idx);
        }

        function createOptionHtml(prefix, idx, value = '', imgUrl = '', isChecked = false) {
            const label = getOptionLabel(idx);
            return `
                <div class="option-row p-3 rounded-2xl border border-slate-200 bg-white space-y-2" data-index="${idx}">
                    <div class="flex items-center gap-2">
                        <input type="radio" id="${prefix}_correct_opt_${idx}" name="correct_answer_index" value="${idx}" ${isChecked ? 'checked' : ''}
                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 shrink-0">
                        <label for="${prefix}_correct_opt_${idx}" class="opt-label-badge w-6 h-6 rounded-md bg-slate-100 font-black text-xs flex items-center justify-center shrink-0 cursor-pointer text-slate-800">
                            ${label}
                        </label>
                        <input type="text" name="option_${idx}" value="${value ? value.replace(/"/g, '&quot;') : ''}" required placeholder="Teks Pilihan ${label}..."
                               class="w-full px-3 py-1.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs font-semibold">
                        <button type="button" onclick="removeOptionRow(this, '${prefix}')" title="Hapus pilihan ini"
                                class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition shrink-0 border border-rose-200">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                    <div class="pl-8 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                        <input type="file" name="opt_image_file_${idx}" accept="image/*"
                               class="w-full text-[10px] text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-slate-100 file:text-slate-700">
                        <input type="text" name="opt_image_url_${idx}" value="${imgUrl ? imgUrl.replace(/"/g, '&quot;') : ''}" placeholder="URL Gambar Opsi ${label}..."
                               class="w-full px-2.5 py-1 rounded-md border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-[11px]">
                    </div>
                </div>
            `;
        }

        function reindexOptions(containerId, prefix) {
            const container = document.getElementById(containerId);
            const rows = container.querySelectorAll('.option-row');
            rows.forEach((row, idx) => {
                row.setAttribute('data-index', idx);
                const label = getOptionLabel(idx);
                const radio = row.querySelector('input[type="radio"]');
                const badge = row.querySelector('.opt-label-badge');
                const textInput = row.querySelector('input[type="text"]:not([name^="opt_image_url"])');
                const fileInput = row.querySelector('input[type="file"]');
                const imgUrlInput = row.querySelector('input[name^="opt_image_url"]');

                if (radio) {
                    radio.id = `${prefix}_correct_opt_${idx}`;
                    radio.value = idx;
                }
                if (badge) {
                    badge.setAttribute('for', `${prefix}_correct_opt_${idx}`);
                    badge.textContent = label;
                }
                if (textInput) {
                    textInput.name = `option_${idx}`;
                    textInput.placeholder = `Teks Pilihan ${label}...`;
                }
                if (fileInput) {
                    fileInput.name = `opt_image_file_${idx}`;
                }
                if (imgUrlInput) {
                    imgUrlInput.name = `opt_image_url_${idx}`;
                    imgUrlInput.placeholder = `URL Gambar Opsi ${label}...`;
                }
            });
        }

        function addOptionToForm(containerId, prefix, value = '', imgUrl = '', isChecked = false) {
            const container = document.getElementById(containerId);
            const nextIdx = container.querySelectorAll('.option-row').length;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = createOptionHtml(prefix, nextIdx, value, imgUrl, isChecked || nextIdx === 0);
            container.appendChild(tempDiv.firstElementChild);
            reindexOptions(containerId, prefix);
        }

        function removeOptionRow(btn, prefix) {
            const row = btn.closest('.option-row');
            const container = row.parentElement;
            if (container.querySelectorAll('.option-row').length <= 2) {
                alert('Soal pilihan ganda minimal harus memiliki 2 pilihan jawaban.');
                return;
            }
            row.remove();
            reindexOptions(container.id, prefix);
        }

        function initDefaultCreateOptions() {
            const container = document.getElementById('create_options_list');
            container.innerHTML = '';
            for (let i = 0; i < 4; i++) {
                addOptionToForm('create_options_list', 'create', '', '', i === 0);
            }
        }

        function previewSubjectIcon(input, previewId, imgId) {
            const previewBox = document.getElementById(previewId);
            const img = document.getElementById(imgId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    previewBox.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function editSubject(subject) {
            document.getElementById('edit_subj_title').value = subject.title;
            document.getElementById('edit_subj_grade').value = subject.grade;
            document.getElementById('edit_subj_duration').value = subject.duration_minutes;
            document.getElementById('edit_subj_max_questions').value = subject.max_questions || 20;
            document.getElementById('edit_subj_max_viol').value = subject.max_violations || 3;
            document.getElementById('edit_subj_remedy').value = subject.remedy_code || ('REMEDI' + subject.grade);
            document.getElementById('edit_subj_desc').value = subject.description || '';

            // Handle icon preview / preset
            const iconFile = document.getElementById('edit_subj_icon_file');
            if (iconFile) iconFile.value = '';
            const previewBox = document.getElementById('edit_subj_icon_preview');
            const previewImg = document.getElementById('edit_subj_icon_img');
            const iconSelect = document.getElementById('edit_subj_icon_name');

            if (subject.icon_name && (subject.icon_name.includes('/') || subject.icon_name.includes('.'))) {
                previewImg.src = subject.icon_name;
                previewBox.classList.remove('hidden');
            } else {
                previewBox.classList.add('hidden');
                if (subject.icon_name && iconSelect) {
                    iconSelect.value = subject.icon_name;
                }
            }

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

            // Parse dynamic options
            let opts = q.options;
            if (typeof opts === 'string') {
                try { opts = JSON.parse(opts); } catch(e) { opts = []; }
            }
            if (!Array.isArray(opts) || opts.length === 0) {
                opts = ['', '', '', ''];
            }

            // Parse option images
            let optImgs = q.option_images;
            if (typeof optImgs === 'string') {
                try { optImgs = JSON.parse(optImgs); } catch(e) { optImgs = []; }
            }
            if (!Array.isArray(optImgs)) {
                optImgs = [];
            }

            const correctIdx = parseInt(q.correct_answer_index) || 0;

            const editContainer = document.getElementById('edit_options_list');
            editContainer.innerHTML = '';
            opts.forEach((optText, i) => {
                const img = optImgs[i] || '';
                addOptionToForm('edit_options_list', 'edit', optText, img, i === correctIdx);
            });

            const form = document.getElementById('formEditQuestion');
            form.action = `/admin/questions/${q.id}`;

            openModal('modalEditQuestion');
        }

        function editUser(u) {
            document.getElementById('edit_user_name').value = u.name || '';
            document.getElementById('edit_user_username').value = u.username || '';
            document.getElementById('edit_user_nomor_induk').value = u.nomor_induk || '';
            document.getElementById('edit_user_password').value = '';
            document.getElementById('edit_user_current_pass').textContent = u.plain_password || 'unpkediri';
            document.getElementById('edit_user_grade').value = u.grade || 1;
            document.getElementById('edit_user_avatar').value = u.avatar || 'av1';
            document.getElementById('edit_user_is_admin').checked = !!u.is_admin;

            const form = document.getElementById('formEditUser');
            form.action = `/admin/users/${u.id}`;

            openModal('modalEditUser');
        }

        // Toggle Password Plaintext Visibility
        function togglePasswordVisibility(spanId, btn) {
            const span = document.getElementById(spanId);
            const icon = btn.querySelector('i');
            const plainPass = span.getAttribute('data-pass') || 'unpkediri';

            if (span.textContent === '••••••••') {
                span.textContent = plainPass;
                span.classList.remove('tracking-wider');
                span.classList.add('text-indigo-700');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                span.textContent = '••••••••';
                span.classList.add('tracking-wider');
                span.classList.remove('text-indigo-700');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Copy Password to Clipboard
        function copyPassword(text) {
            if (!text) text = 'unpkediri';
            navigator.clipboard.writeText(text).then(() => {
                alert('Password "' + text + '" berhasil disalin ke clipboard!');
            }).catch(() => {
                prompt('Salin password:', text);
            });
        }

        // Auto Refresh for AI Proctoring Monitor (10 seconds)
        let autoRefreshTimer = null;
        let isAutoRefreshActive = true;

        function startAutoRefresh() {
            if (autoRefreshTimer) clearInterval(autoRefreshTimer);
            autoRefreshTimer = setInterval(() => {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('tab') === 'monitoring') {
                    window.location.reload();
                }
            }, 10000);
        }

        function toggleAutoRefresh() {
            const btn = document.getElementById('btnAutoRefresh');
            const dot = document.getElementById('dotAutoRefresh');
            const txt = document.getElementById('textAutoRefresh');

            if (isAutoRefreshActive) {
                clearInterval(autoRefreshTimer);
                isAutoRefreshActive = false;
                dot.classList.remove('bg-emerald-400');
                dot.classList.add('bg-slate-400');
                txt.textContent = 'Auto-Refresh: Mati';
                btn.classList.remove('bg-emerald-500/20', 'text-emerald-300', 'border-emerald-400/30');
                btn.classList.add('bg-slate-500/20', 'text-slate-400', 'border-slate-400/30');
            } else {
                isAutoRefreshActive = true;
                dot.classList.remove('bg-slate-400');
                dot.classList.add('bg-emerald-400');
                txt.textContent = 'Auto-Refresh: Aktif (10s)';
                btn.classList.add('bg-emerald-500/20', 'text-emerald-300', 'border-emerald-400/30');
                btn.classList.remove('bg-slate-500/20', 'text-slate-400', 'border-slate-400/30');
                startAutoRefresh();
            }
        }

        // Initialize auto-refresh only on monitoring tab
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'monitoring') {
            startAutoRefresh();
        }
    </script>
</body>
</html>
