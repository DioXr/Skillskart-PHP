</div> <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
        mermaid.initialize({ startOnLoad: true });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-jsx.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-go.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Navbar Scroll Effect
        const nav = document.querySelector('.navbar');
        if (nav) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 10) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            });
        }

        // 2. Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const sunIcon = '<i class="fa-solid fa-sun"></i>';
        const moonIcon = '<i class="fa-solid fa-moon"></i>';

        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            if(themeToggle) themeToggle.innerHTML = theme === 'dark' ? sunIcon : moonIcon;
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });
        }
        // Initialize Theme
        setTheme(localStorage.getItem('theme') || 'light');

        // 3. Global Copy Code Button Logic
        document.querySelectorAll('.copy-code-btn').forEach(button => {
            button.addEventListener('click', () => {
                const pre = button.nextElementSibling; // The <pre> block
                if (pre) {
                    const code = pre.textContent;
                    navigator.clipboard.writeText(code).then(() => {
                        const originalText = button.textContent;
                        button.textContent = 'Copied!';
                        setTimeout(() => button.textContent = originalText, 2000);
                    });
                }
            });
        });
    });
    </script>
</body>
</html>