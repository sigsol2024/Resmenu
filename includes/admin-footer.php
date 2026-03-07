    </main>
  </div>
</div>

<script src="<?php echo htmlspecialchars(defined('SITE_URL') ? SITE_URL . '/assets/js/actions-dropdown.js' : '../assets/js/actions-dropdown.js'); ?>"></script>
<script>
// Check if sidebar should be collapsed from cookie
(function() {
  const sidebar = document.getElementById('sidebar');
  if (sidebar && getCookie('sidebar_collapsed') === 'true') {
    sidebar.classList.add('collapsed');
  }
  
  // Handle resize - manage desktop vs mobile sidebar behavior
  function handleResize() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const hamburger = document.querySelector('.mobile-hamburger');
    
    if (window.innerWidth >= 769) {
      // Desktop: sidebar always visible, remove mobile classes
      if (sidebar) {
        sidebar.classList.remove('mobile-open');
        sidebar.style.transform = ''; // Clear inline styles, let CSS handle it
      }
      if (overlay) overlay.classList.remove('show');
      if (hamburger) hamburger.classList.remove('hidden');
    } else {
      // Mobile: ensure sidebar is closed by default, let CSS handle transform
      if (sidebar) {
        sidebar.style.transform = ''; // Clear inline styles
      }
    }
  }
  
  handleResize();
  window.addEventListener('resize', handleResize);
})();

function toggleMobile(){
  const sidebar = document.getElementById('sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  const hamburger = document.querySelector('.mobile-hamburger');
  
  if (sidebar) {
    sidebar.classList.toggle('mobile-open');
    sidebar.style.transform = ''; // Clear any inline styles, let CSS handle it
  }
  
  if (overlay) {
    overlay.classList.toggle('show');
  }
  
  // Hide hamburger when sidebar is open on mobile
  if (hamburger && sidebar) {
    hamburger.classList.toggle('hidden', sidebar.classList.contains('mobile-open'));
  }
}

function toggleCollapse(){
  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    sidebar.classList.toggle('collapsed');
    
    // Save state to cookie
    const isCollapsed = sidebar.classList.contains('collapsed');
    setCookie('sidebar_collapsed', isCollapsed ? 'true' : 'false', 365);
  }
}

function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  return null;
}

function setCookie(name, value, days) {
  const expires = new Date();
  expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
  document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}
</script>

<?php
// Get base URL for assets - use SITE_URL if available, otherwise detect dynamically
if (defined('SITE_URL')) {
    $baseUrl = SITE_URL;
} else {
    // Fallback: dynamically detect protocol and domain
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Get base path - config.php is in root, so we need to find root from any subdirectory
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $scriptDir = dirname($scriptPath);
    // If script is in /admin or /manager, go up one level
    if ($scriptDir === '/admin' || $scriptDir === '/manager' || strpos($scriptDir, '/admin/') === 0 || strpos($scriptDir, '/manager/') === 0) {
        $basePath = dirname($scriptDir);
    } else {
        $basePath = $scriptDir;
    }
    // Normalize: root should be empty string
    $basePath = ($basePath === '/' || $basePath === '\\' || $basePath === '.') ? '' : $basePath;
    $baseUrl = $protocol . $host . $basePath;
}
?>
<script src="<?php echo $baseUrl; ?>/assets/js/admin.js"></script>

<style>
/* Password Toggle Styles */
.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.password-input-wrapper input[type="password"],
.password-input-wrapper input[type="text"] {
    padding-right: 40px !important;
    width: 100%;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: color 0.2s;
    z-index: 10;
    outline: none;
}

.password-toggle:hover {
    color: #374151;
}

.password-toggle:focus {
    outline: 2px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
    border-radius: 4px;
}

.password-toggle svg {
    width: 18px;
    height: 18px;
    pointer-events: none;
}

.password-toggle.hidden .eye-open {
    display: none;
}

.password-toggle.hidden .eye-closed {
    display: block;
}

.password-toggle .eye-closed {
    display: none;
}

.password-toggle .eye-open {
    display: block;
}
</style>

<script>
// Password Toggle Functionality
(function() {
    function initPasswordToggles() {
        // Find all password input fields
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        
        passwordInputs.forEach(function(input) {
            // Skip if already wrapped
            if (input.closest('.password-input-wrapper')) {
                return;
            }
            
            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'password-input-wrapper';
            
            // Wrap the input
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            
            // Create toggle button
            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'password-toggle';
            toggle.setAttribute('aria-label', 'Toggle password visibility');
            
            // Add eye icons
            toggle.innerHTML = `
                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L3.98 8.223m13.793 5.772L21 21m-2.227-2.227L17.022 15.78M15.78 17.022l-2.227-2.227m0 0a3 3 0 01-4.243-4.243M13.553 13.553a3 3 0 01-4.243-4.243" />
                </svg>
            `;
            
            // Add click handler
            toggle.addEventListener('click', function() {
                if (input.type === 'password') {
                    input.type = 'text';
                    toggle.classList.add('hidden');
                } else {
                    input.type = 'password';
                    toggle.classList.remove('hidden');
                }
            });
            
            // Insert toggle button
            wrapper.appendChild(toggle);
        });
    }
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPasswordToggles);
    } else {
        initPasswordToggles();
    }
    
    // Also initialize after dynamic content loads (for modals, etc.)
    setTimeout(initPasswordToggles, 100);
})();
</script>

</body>
</html>

