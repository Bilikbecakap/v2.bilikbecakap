import { ref, watch, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    const toggleDarkMode = () => {
        isDark.value = !isDark.value;
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
        updateTheme();
    };

    const updateTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    const initTheme = () => {
        // Check localStorage first
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme) {
            isDark.value = savedTheme === 'dark';
        } else {
            // Check system preference
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        
        updateTheme();
    };

    return {
        isDark,
        toggleDarkMode,
        initTheme
    };
}