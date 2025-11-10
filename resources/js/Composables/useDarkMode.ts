import { ref, onMounted } from 'vue';

const isDarkMode = ref(false);

export function useDarkMode() {
    function updateDarkModeClass(dark: boolean) {
        if (dark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    function enableDarkMode() {
        isDarkMode.value = true;
        localStorage.setItem('darkMode', 'true');
        updateDarkModeClass(true);
    }

    function disableDarkMode() {
        isDarkMode.value = false;
        localStorage.setItem('darkMode', 'false');
        updateDarkModeClass(false);
    }

    function toggleDarkMode() {
        if (isDarkMode.value) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    }

    onMounted(() => {
        // Check user preference or system preference
        const savedPreference = localStorage.getItem('darkMode');
        
        if (savedPreference !== null) {
            // Use user's saved preference
            if (savedPreference === 'true') {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        } else {
            // Use system preference
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (systemPrefersDark) {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        }
    });

    return {
        isDarkMode,
        enableDarkMode,
        disableDarkMode,
        toggleDarkMode,
    };
}