<?php
// Número de WhatsApp en formato internacional (sin +)
$waPhone = env('app.whatsapp_phone', '50379075851'); // Ejemplo: 5037XXXXXXXX

// Mensaje predefinido
$mensaje = rawurlencode('¡Hola! Vengo desde el sitio web de Distribuidora Los Cheles. Me gustaría más información.');

// Enlace completo de WhatsApp
$waLink = "https://wa.me/{$waPhone}?text={$mensaje}";
?>

<!-- Botón flotante de WhatsApp -->
<div class="fixed bottom-5 right-5 z-50 group">
    <a href="<?= esc($waLink) ?>" target="_blank" rel="noopener noreferrer"
       class="relative flex items-center justify-center md:justify-start gap-2
              bg-[#25D366] hover:bg-[#1ebe5b] text-white rounded-full shadow-xl
              px-4 py-3 md:px-5 md:py-3 transition-all duration-200"
       aria-label="Escríbenos por WhatsApp">

        <!-- Animación de pulso -->
        <span class="absolute inset-0 rounded-full animate-ping opacity-30 bg-[#25D366]"></span>

        <!-- Ícono oficial de WhatsApp -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor"
             class="w-6 h-6 md:w-7 md:h-7 relative z-10">
            <path d="M16.004 3.2a12.79 12.79 0 00-10.92 19.2L3.2 28.8l6.48-1.68A12.73 12.73 0 0016 28.8 12.8 12.8 0 0016 3.2zm0 23.36a10.47 10.47 0 01-5.4-1.52l-.4-.24-3.84.96 1.04-3.76-.24-.4a10.44 10.44 0 1118.56-5.76 10.49 10.49 0 01-10.72 10.72zm5.76-7.04c-.32-.16-1.84-.88-2.16-1.04s-.56-.16-.8.16c-.24.32-.88 1.04-1.04 1.2-.16.16-.32.16-.56.08-.24-.08-1.04-.4-2-1.2s-1.68-1.76-1.84-2c-.16-.24-.08-.4.08-.56.08-.08.24-.32.4-.48.16-.16.16-.32.24-.48.08-.16.08-.32 0-.48-.08-.16-.72-1.76-1-2.4-.24-.56-.48-.48-.64-.48h-.48c-.16 0-.48.08-.72.32s-.96.96-.96 2.32.96 2.64 1.12 2.8 1.92 3.04 4.56 4.16c.64.32 1.12.48 1.52.64.64.24 1.2.16 1.6.08.48-.08 1.52-.64 1.76-1.28.24-.64.24-1.12.16-1.28-.08-.16-.24-.24-.48-.32z"/>
        </svg>

        <!-- Texto visible solo en pantallas medianas -->
        <span class="hidden md:inline text-sm font-semibold tracking-wide relative z-10"><i class="fa-brands fa-whatsapp"></i>WhatsApp</span>
    </a>

    <!-- Tooltip (visible al hacer hover o tocar) -->
    <div class="absolute right-full mr-3 bottom-1/2 translate-y-1/2
                bg-gray-900 text-white text-xs font-medium py-1 px-2 rounded-md shadow-md
                opacity-0 group-hover:opacity-100 transition-opacity duration-200
                whitespace-nowrap select-none pointer-events-none">
        ¡Escríbenos!
    </div>
</div>
