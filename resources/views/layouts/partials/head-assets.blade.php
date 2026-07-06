{{-- Shared head assets for the new design system (admin + guest shells) --}}

{{-- Inter font (matches design system) --}}
<link rel="preconnect" href="https://rsms.me/">
<link rel="stylesheet" href="https://rsms.me/inter/inter.css">

{{-- Icons (transition safety for shared partials still using FontAwesome) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

{{-- Tailwind (compiled via Vite — new design system) --}}
@vite(['resources/css/admin.css'])

{{-- Alpine.js --}}
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
