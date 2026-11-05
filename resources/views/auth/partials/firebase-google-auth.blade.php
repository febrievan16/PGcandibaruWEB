@php
    $firebaseService = app(\App\Services\FirebaseAuthService::class);
    $isConfigured = $firebaseService->isConfigured();
    $clientConfig = $firebaseService->getClientConfig();
    $btnLabel = $buttonText ?? 'Masuk Cepat dengan Akun Google';
    $prefix = $idPrefix ?? 'auth';
@endphp

<div id="{{ $prefix }}-google-container" class="w-full space-y-2">
    <!-- Feedback / Error Banner -->
    <div id="{{ $prefix }}-sso-alert" class="hidden p-3 text-xs border rounded-[2px] transition-all"></div>

    <!-- Google SSO Trigger Button -->
    <button type="button"
            id="{{ $prefix }}-google-btn"
            class="w-full flex items-center justify-center gap-3 py-2.5 px-4 border border-[#6B3F1E] bg-[#FFFFFF] hover:bg-[#F3EAD8] text-[#2A211A] font-semibold text-sm rounded-[2px] transition-all cursor-pointer shadow-xs min-h-[44px] active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed">
        <!-- Google G SVG -->
        <svg id="{{ $prefix }}-google-icon" class="w-4 h-4 shrink-0 transition-transform" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
        </svg>

        <!-- Loading Spinner -->
        <svg id="{{ $prefix }}-google-spinner" class="hidden animate-spin w-4 h-4 shrink-0 text-[#6B3F1E]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>

        <span id="{{ $prefix }}-google-btn-text">{{ $btnLabel }}</span>

        @if(!$isConfigured)
            <span class="ml-1 px-1.5 py-0.5 text-[10px] uppercase font-bold tracking-wider bg-[#C8A24A]/20 text-[#6B3F1E] border border-[#C8A24A]/40 rounded-[2px]">
                Firebase
            </span>
        @endif
    </button>

    <div class="flex items-center justify-between text-[11px] text-[#6E635A] px-0.5">
        <span>SSO Terintegrasi Google &bull; Firebase</span>
        <a href="{{ route('auth.social.redirect', 'google') }}" class="text-[#A67C52] hover:text-[#6B3F1E] underline underline-offset-2">
            Opsi Redirect Standar
        </a>
    </div>
</div>

<!-- Firebase Setup & Simulation Modal (Rendered once per page) -->
@once
<div id="firebase-guide-modal" class="fixed inset-0 z-50 hidden bg-[#2A211A]/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg bg-[#FFFFFF] border-2 border-[#6B3F1E] shadow-2xl p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between border-b border-[#E3D9C9] pb-3 mb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#A67C52]">Single Sign-On &bull; Firebase Google Auth</span>
                <h3 class="font-serif text-xl font-bold text-[#2A211A]">Panduan Konfigurasi Firebase SSO</h3>
            </div>
            <button type="button" onclick="closeFirebaseModal()" class="text-[#6E635A] hover:text-[#2A211A] text-2xl leading-none p-1">&times;</button>
        </div>

        <div class="space-y-4 text-xs text-[#2A211A] leading-relaxed">
            <div class="p-3 bg-[#FAF6EE] border border-[#E3D9C9] space-y-1">
                <strong class="text-[#6B3F1E] block font-bold text-[13px]">Langkah Cepat Mengaktifkan Google SSO (Gratis):</strong>
                <ol class="list-decimal list-inside space-y-1.5 pt-1 text-[#6E635A]">
                    <li>Buka konsol resmi: <a href="https://console.firebase.google.com" target="_blank" rel="noopener noreferrer" class="text-[#6B3F1E] underline font-semibold">console.firebase.google.com</a>.</li>
                    <li>Buat Project baru (atau pilih project Google Cloud yang ada).</li>
                    <li>Pilih menu <strong>Build &gt; Authentication</strong> &gt; tab <strong>Sign-in method</strong> &gt; klik <strong>Google</strong> &gt; centang <strong>Enable</strong>.</li>
                    <li>Buka <strong>Project Settings</strong> (ikon gerigi ⚙️ di samping kiri atas) &gt; scroll ke <strong>Your apps</strong> &gt; klik ikon Web (<strong>&lt;/&gt;</strong>).</li>
                    <li>Buka file <code class="bg-[#FFFFFF] px-1 py-0.5 border border-[#E3D9C9] font-mono text-[11px]">.env</code> di aplikasi ini dan salin kuncinya:</li>
                </ol>

                <div class="mt-2 p-2 bg-[#2A211A] text-[#FAF6EE] font-mono text-[11px] rounded-[2px] overflow-x-auto space-y-0.5">
                    <div>FIREBASE_API_KEY=AIzaSy...</div>
                    <div>FIREBASE_AUTH_DOMAIN=project-anda.firebaseapp.com</div>
                    <div>FIREBASE_PROJECT_ID=project-anda</div>
                    <div>FIREBASE_APP_ID=1:123456789:web:abcdef...</div>
                </div>
            </div>

            <!-- Instant One-Click SSO Simulation -->
            <div class="p-3 bg-[#C8A24A]/10 border border-[#C8A24A]/40 space-y-2">
                <div class="flex items-center gap-1.5 text-[#6B3F1E] font-bold text-[13px]">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Uji Coba Langsung Tanpa Firebase (Mode Simulasi SSO)</span>
                </div>
                <p class="text-[#6E635A]">
                    Ingin menguji login/registrasi Google langsung sekarang? Klik salah satu tombol di bawah untuk menyimulasikan autentikasi Google SSO dan login ke sesi aplikasi secara instan:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    <button type="button"
                            onclick="executeFirebaseSimulation('wisatawan.google@gmail.com', 'Wisatawan Google Simulasi')"
                            class="p-2 text-left bg-[#FFFFFF] border border-[#6B3F1E] hover:bg-[#F3EAD8] text-[#2A211A] rounded-[2px] transition-colors cursor-pointer">
                        <strong class="block text-[#6B3F1E] font-semibold text-xs">Akun Pengunjung / Wisatawan</strong>
                        <span class="text-[11px] text-[#6E635A]">wisatawan.google@gmail.com</span>
                    </button>

                    <button type="button"
                            onclick="executeFirebaseSimulation('admin@pgcandibaru.co.id', 'Administrator PG Candi Baru')"
                            class="p-2 text-left bg-[#FFFFFF] border border-[#6B3F1E] hover:bg-[#F3EAD8] text-[#2A211A] rounded-[2px] transition-colors cursor-pointer">
                        <strong class="block text-[#6B3F1E] font-semibold text-xs">Akun Administrator</strong>
                        <span class="text-[11px] text-[#6E635A]">admin@pgcandibaru.co.id</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-[#E3D9C9] flex justify-end">
            <button type="button" onclick="closeFirebaseModal()" class="btn-heritage-secondary text-xs px-4 py-2">
                Tutup Panduan
            </button>
        </div>
    </div>
</div>

<script>
    function openFirebaseModal() {
        const modal = document.getElementById('firebase-guide-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeFirebaseModal() {
        const modal = document.getElementById('firebase-guide-modal');
        if (modal) modal.classList.add('hidden');
    }

    // Helper to generate simulated mock ID Token and exchange via session endpoint
    async function executeFirebaseSimulation(email, name) {
        closeFirebaseModal();
        const alertEl = document.querySelector('[id$="-sso-alert"]');
        if (alertEl) {
            alertEl.className = 'p-3 text-xs border border-[#C8A24A] bg-[#C8A24A]/10 text-[#6B3F1E] rounded-[2px]';
            alertEl.innerHTML = `<strong>Menghubungkan:</strong> Menyimulasikan login Google SSO untuk <em>${email}</em>...`;
            alertEl.classList.remove('hidden');
        }

        try {
            // Unsigned mock JWT readable by backend fallback
            const b64 = (obj) => btoa(unescape(encodeURIComponent(JSON.stringify(obj))))
                .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

            const header = b64({ alg: 'none', typ: 'JWT' });
            const payload = b64({
                sub: 'sim-google-' + Date.now(),
                email: email,
                name: name,
                picture: 'https://lh3.googleusercontent.com/a/default-user=s96-c'
            });
            const mockToken = `${header}.${payload}.simulated_signature`;

            const res = await fetch('{{ route('auth.firebase.session') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id_token: mockToken })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                if (alertEl) {
                    alertEl.className = 'p-3 text-xs border border-[#2E6B3E] bg-[#2E6B3E]/10 text-[#2E6B3E] rounded-[2px]';
                    alertEl.innerHTML = `<strong>Berhasil!</strong> Masuk sebagai ${data.user.name}. Mengalihkan ke dashboard...`;
                }
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 400);
            } else {
                throw new Error(data.message || 'Gagal masuk melalui simulasi.');
            }
        } catch (err) {
            if (alertEl) {
                alertEl.className = 'p-3 text-xs border border-[#8B2E1F] bg-[#8B2E1F]/10 text-[#8B2E1F] rounded-[2px]';
                alertEl.innerHTML = `<strong>Gagal:</strong> ${err.message}`;
            }
        }
    }
</script>
@endonce

<!-- Client-Side Firebase Logic -->
@if($isConfigured)
    @once
        <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
        <script>
            (function () {
                const firebaseConfig = @json($clientConfig);
                if (typeof firebase !== 'undefined' && !firebase.apps.length) {
                    firebase.initializeApp(firebaseConfig);
                }
            })();
        </script>
    @endonce

    <script>
        (function () {
            const btn = document.getElementById('{{ $prefix }}-google-btn');
            const alertEl = document.getElementById('{{ $prefix }}-sso-alert');
            const icon = document.getElementById('{{ $prefix }}-google-icon');
            const spinner = document.getElementById('{{ $prefix }}-google-spinner');
            const btnText = document.getElementById('{{ $prefix }}-google-btn-text');
            const originalText = btnText ? btnText.innerText : 'Masuk dengan Google';

            function setLoading(isLoading, text) {
                if (!btn) return;
                btn.disabled = isLoading;
                if (icon) icon.classList.toggle('hidden', isLoading);
                if (spinner) spinner.classList.toggle('hidden', !isLoading);
                if (btnText) btnText.innerText = text || originalText;
            }

            function showAlert(msg, isError = true) {
                if (!alertEl) return;
                alertEl.className = isError
                    ? 'p-3 text-xs border border-[#8B2E1F] bg-[#8B2E1F]/10 text-[#8B2E1F] rounded-[2px]'
                    : 'p-3 text-xs border border-[#2E6B3E] bg-[#2E6B3E]/10 text-[#2E6B3E] rounded-[2px]';
                alertEl.innerHTML = msg;
                alertEl.classList.remove('hidden');
            }

            if (btn) {
                btn.addEventListener('click', async function () {
                    if (typeof firebase === 'undefined' || !firebase.apps.length) {
                        openFirebaseModal();
                        return;
                    }

                    if (alertEl) alertEl.classList.add('hidden');
                    setLoading(true, 'Menghubungkan ke Google...');

                    try {
                        const provider = new firebase.auth.GoogleAuthProvider();
                        provider.setCustomParameters({ prompt: 'select_account' });

                        const result = await firebase.auth().signInWithPopup(provider);
                        const idToken = await result.user.getIdToken();

                        setLoading(true, 'Memverifikasi sesi...');

                        const res = await fetch('{{ route('auth.firebase.session') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ id_token: idToken })
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            showAlert(`<strong>Berhasil!</strong> Mengalihkan...`, false);
                            setLoading(true, 'Mengalihkan...');
                            window.location.href = data.redirect;
                        } else {
                            throw new Error(data.message || 'Verifikasi token Google gagal.');
                        }
                    } catch (err) {
                        if (err.code === 'auth/popup-closed-by-user') {
                            setLoading(false);
                            return;
                        }
                        if (err.code === 'auth/cancelled-popup-request') {
                            setLoading(false);
                            return;
                        }
                        if (err.code === 'auth/unauthorized-domain') {
                            const currentHost = window.location.hostname;
                            showAlert(`<strong>Domain Belum Diizinkan (auth/unauthorized-domain):</strong><br>Domain <code>${currentHost}</code> belum terdaftar di Firebase.<br><span class="mt-1 inline-block">Buka <strong>Firebase Console &gt; Authentication &gt; Settings &gt; Authorized domains</strong> lalu klik <em>Add domain</em> dan masukkan: <strong>${currentHost}</strong></span>`);
                            setLoading(false);
                            return;
                        }
                        showAlert(`<strong>Gagal masuk dengan Google:</strong> ${err.message || 'Terjadi kesalahan sistem.'}`);
                        setLoading(false);
                    }
                });
            }
        })();
    </script>
@else
    <script>
        (function () {
            const btn = document.getElementById('{{ $prefix }}-google-btn');
            if (btn) {
                btn.addEventListener('click', function () {
                    openFirebaseModal();
                });
            }
        })();
    </script>
@endif
