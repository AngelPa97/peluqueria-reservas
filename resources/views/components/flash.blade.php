@if (session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent']))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="mb-6 flex items-start justify-between gap-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
         role="status">
        <span>{{ session('status') }}</span>
        <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-900" aria-label="Cerrar">&times;</button>
    </div>
@endif
