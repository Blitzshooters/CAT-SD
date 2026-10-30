<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panel Guru & Admin — CAT SD</title>
    <link rel="icon" type="image/png" href="{{ asset('app-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.8);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-x-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Ambient Glowing Orbs -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 space-y-6">

        <!-- Header Branding -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-amber-400 p-0.5 shadow-xl shadow-indigo-950/50">
                <img src="{{ asset('app-logo.png') }}" alt="Logo CAT SD" class="w-full h-full object-cover rounded-[14px]">
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">CAT SD — Panel Ujian</h1>
            <p class="text-xs sm:text-sm text-indigo-200 font-medium">Portal Khusus Administrator & Guru / Pengawas</p>
        </div>

        <!-- Role Badges Banner -->
        <div class="grid grid-cols-2 gap-2 text-[11px] font-bold">
            <div class="bg-indigo-500/20 border border-indigo-400/30 text-indigo-200 px-3 py-2 rounded-xl flex items-center justify-center gap-2 backdrop-blur-sm">
                <i class="fa-solid fa-user-shield text-amber-300"></i>
                <span>Super Administrator</span>
            </div>
            <div class="bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 px-3 py-2 rounded-xl flex items-center justify-center gap-2 backdrop-blur-sm">
                <i class="fa-solid fa-chalkboard-user text-emerald-300"></i>
                <span>Guru & Pengawas</span>
            </div>
        </div>

        <!-- Card Container -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">

            <!-- Alerts -->
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-start gap-2.5 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm mt-0.5 shrink-0"></i>
                    <div class="leading-relaxed">{{ session('error') }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-semibold flex items-start gap-2.5 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                    <div class="leading-relaxed">{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs font-semibold space-y-1">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                        <span>Mohon periksa data berikut:</span>
                    </div>
                    <ul class="list-disc list-inside text-rose-700 pl-2">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Username -->
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Username Akun
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <input type="text"
                               name="username"
                               id="username"
                               value="{{ old('username') }}"
                               placeholder="Masukkan username akun Anda"
                               required
                               autocomplete="username"
                               autofocus
                               class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-300 text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 focus:outline-none transition bg-white/80">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password"
                               name="password"
                               id="password"
                               placeholder="Masukkan password akun Anda"
                               required
                               autocomplete="current-password"
                               class="w-full pl-10 pr-11 py-3 rounded-2xl border border-slate-300 text-slate-900 text-sm font-semibold focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 focus:outline-none transition bg-white/80">
                        <button type="button"
                                onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-indigo-600 transition"
                                title="Lihat/Sembunyikan Password">
                            <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               name="remember"
                               value="1"
                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                        <span class="font-bold text-slate-600">Ingat sesi saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-extrabold text-sm transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket text-base"></i>
                    <span>Masuk ke Panel Admin</span>
                </button>
            </form>

        </div>

        <!-- Student Notice Footer -->
        <div class="text-center space-y-1">
            <div class="inline-flex items-center gap-2 text-xs text-indigo-200/90 font-medium bg-white/5 border border-white/10 px-4 py-2 rounded-full backdrop-blur-sm">
                <i class="fa-solid fa-mobile-screen-button text-amber-300"></i>
                <span>Siswa SD mengerjakan ujian via <strong>Aplikasi CAT Android</strong></span>
            </div>
            <p class="text-[11px] text-indigo-300/60 font-mono">CAT SD v2.0 • Universitas Nusantara PGRI Kediri</p>
        </div>

    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
