<script setup>
/**
 * Social sign-in buttons from the auth prototypes.
 *
 * No OAuth provider is wired up yet, so these are inert placeholders rather
 * than links that would dead-end.
 */
</script>

<template>
    <div class="grid grid-cols-2 gap-3 mb-5">
        <button
            type="button"
            disabled
            title="Google sign-in — coming soon"
            class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-2.5 text-[13.5px] font-semibold text-gray-400 cursor-not-allowed"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    fill="#4285F4"
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                />
                <path
                    fill="#34A853"
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                />
                <path
                    fill="#FBBC05"
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                />
                <path
                    fill="#EA4335"
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                />
            </svg>
            Google
        </button>
        <button
            type="button"
            disabled
            title="Apple sign-in — coming soon"
            class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-2.5 text-[13.5px] font-semibold text-gray-400 cursor-not-allowed"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M16.365 1.43c0 1.14-.493 2.27-1.177 3.08-.744.9-1.99 1.57-2.987 1.57-.12 0-.23-.02-.3-.03-.01-.06-.04-.22-.04-.39 0-1.15.572-2.27 1.206-2.98.744-.85 2.03-1.51 3.02-1.55.01.1.03.19.03.3zm4.565 15.71c-.03.07-.463 1.58-1.518 3.12-.9 1.34-1.86 2.67-3.335 2.7-1.46.03-1.93-.86-3.59-.86-1.665 0-2.185.83-3.56.89-1.42.06-2.51-1.45-3.41-2.79-1.86-2.75-3.28-7.75-1.37-11.14.95-1.68 2.64-2.75 4.475-2.78 1.4-.03 2.72.94 3.58.94.85 0 2.45-1.16 4.135-.99.705.03 2.685.285 3.955 2.14-.1.065-2.36 1.38-2.335 4.11.03 3.26 2.86 4.35 2.895 4.36z"
                />
            </svg>
            Apple
        </button>
    </div>

    <div class="flex items-center gap-3 mb-5">
        <div class="flex-1 h-px bg-gray-100" />
        <span class="text-[11.5px] text-gray-400 font-medium">OR</span>
        <div class="flex-1 h-px bg-gray-100" />
    </div>
</template>