      </div>
      <footer class="console-footer">
        <p class="console-footer-text">Calamus Financial &middot; <?php echo date('Y'); ?></p>
      </footer>
    </main>
  </div>
  <script>
  (function() {
    var root = document.body;
    var html = document.documentElement;
    var savedTheme = localStorage.getItem('financial.theme') || html.dataset.theme || 'light';
    var savedBrand = localStorage.getItem('financial.brand') || html.style.getPropertyValue('--color-primary') || '#545760';

    function applyTheme(theme) {
      root.dataset.theme = theme;
      html.dataset.theme = theme;
      localStorage.setItem('financial.theme', theme);
      document.querySelectorAll('[data-theme-value]').forEach(function(button) {
        button.classList.toggle('active', button.dataset.themeValue === theme);
      });
    }

    function applyBrand(color) {
      root.style.setProperty('--color-primary', color);
      html.style.setProperty('--color-primary', color);
      localStorage.setItem('financial.brand', color);
      var picker = document.getElementById('brandPicker');
      if (picker) picker.value = color;
    }

    applyTheme(savedTheme);
    applyBrand(savedBrand.trim() || '#545760');

    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var menuToggle = document.getElementById('menuToggle');
    function openSidebar() {
      if (!sidebar || !overlay || !menuToggle) return;
      sidebar.classList.add('open');
      overlay.classList.add('show');
      overlay.setAttribute('aria-hidden', 'false');
      menuToggle.setAttribute('aria-expanded', 'true');
    }
    function closeSidebar() {
      if (!sidebar || !overlay || !menuToggle) return;
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
      overlay.setAttribute('aria-hidden', 'true');
      menuToggle.setAttribute('aria-expanded', 'false');
    }
    if (menuToggle) {
      menuToggle.addEventListener('click', function() {
        sidebar && sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
      });
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);

    function bindPopover(buttonId, panelId) {
      var button = document.getElementById(buttonId);
      var panel = document.getElementById(panelId);
      if (!button || !panel) return null;
      button.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = panel.hidden === false;
        panel.hidden = isOpen;
        button.setAttribute('aria-expanded', String(!isOpen));
      });
      panel.addEventListener('click', function(e) { e.stopPropagation(); });
      return { button: button, panel: panel };
    }

    var notifications = bindPopover('notificationToggle', 'notificationDropdown');
    var theme = bindPopover('themeToggle', 'themePopover');
    document.addEventListener('click', function() {
      [notifications, theme].forEach(function(popover) {
        if (popover) {
          popover.panel.hidden = true;
          popover.button.setAttribute('aria-expanded', 'false');
        }
      });
    });

    document.querySelectorAll('[data-theme-value]').forEach(function(button) {
      button.addEventListener('click', function() {
        applyTheme(button.dataset.themeValue);
      });
    });
    document.querySelectorAll('[data-brand-value]').forEach(function(button) {
      button.addEventListener('click', function() {
        applyBrand(button.dataset.brandValue);
      });
    });
    var brandPicker = document.getElementById('brandPicker');
    if (brandPicker) {
      brandPicker.addEventListener('input', function() {
        applyBrand(brandPicker.value);
      });
    }
  })();
  </script>
</body>
</html>
