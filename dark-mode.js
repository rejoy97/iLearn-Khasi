function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
        
        const toggleButton = document.getElementById("toggle-dark-mode");
        function setDarkMode(enabled) {
            if (enabled) {
                document.body.classList.add("dark-mode");
                localStorage.setItem("darkMode", "enabled");
                toggleButton.textContent = "Light Mode";
            } else {
                document.body.classList.remove("dark-mode");
                localStorage.setItem("darkMode", "disabled");
                toggleButton.textContent = "Dark Mode";
            }
        }
        toggleButton.addEventListener("click", () => {
            const isDarkMode = document.body.classList.contains("dark-mode");
            setDarkMode(!isDarkMode);
        });
        if (localStorage.getItem("darkMode") === "enabled") {
            setDarkMode(true);
        }
