/**
 * Global Actions Dropdown (portal) – prevents clipping by overflow:hidden parents.
 * Moves .actions-dropdown into body when open, positions with fixed, closes on outside click / Escape.
 */
(function () {
  const PORTAL_CLASS = 'resmenu-portal';
  const DROPDOWN_SELECTOR = '.actions-dropdown';
  const TRIGGER_SELECTOR = '.actions-btn';

  let activeDropdown = null;
  let activePlaceholder = null;
  let activeTrigger = null;

  function positionPortal(trigger, dropdown) {
    var rect = trigger.getBoundingClientRect();
    var viewportH = window.innerHeight;
    var viewportW = window.innerWidth;
    var dropdownW = dropdown.offsetWidth;
    var dropdownH = dropdown.offsetHeight;
    var gap = 4;

    dropdown.classList.add(PORTAL_CLASS);
    dropdown.style.position = 'fixed';
    dropdown.style.zIndex = '99999';
    dropdown.style.right = 'auto';
    dropdown.style.marginRight = '0';

    // Horizontal: prefer left of button; if not enough space, show to the right
    var left = rect.left - dropdownW - gap;
    if (left < 8) left = rect.right + gap;
    if (left + dropdownW > viewportW - 8) left = viewportW - dropdownW - 8;
    if (left < 8) left = 8;
    dropdown.style.left = left + 'px';

    // Vertical: keep entire dropdown in viewport; prefer below button, else above
    var top = rect.top;
    if (top + dropdownH > viewportH - 8) {
      // Would overflow bottom: place above button or clamp to viewport
      var topAbove = rect.top - dropdownH - gap;
      if (topAbove >= 8) {
        top = topAbove;
      } else {
        top = Math.max(8, viewportH - dropdownH - 8);
      }
    }
    if (top < 8) top = 8;
    dropdown.style.top = top + 'px';
  }

  function openDropdown(trigger) {
    if (!trigger) return;
    if (activeDropdown && activeTrigger === trigger) {
      closeDropdown();
      return;
    }
    var dropdown = trigger.nextElementSibling;
    if (!dropdown || !dropdown.classList.contains('actions-dropdown')) {
      if (activeTrigger === trigger) closeDropdown();
      return;
    }

    closeDropdown();

    var parent = dropdown.parentNode;
    var placeholder = document.createElement('span');
    placeholder.setAttribute('aria-hidden', 'true');
    placeholder.style.display = 'none';
    parent.insertBefore(placeholder, dropdown);
    document.body.appendChild(dropdown);

    dropdown.classList.add('show');
    positionPortal(trigger, dropdown);
    activeDropdown = dropdown;
    activePlaceholder = placeholder;
    activeTrigger = trigger;

    setTimeout(function () {
      document.addEventListener('click', onDocumentClick);
      document.addEventListener('keydown', onEscape);
    }, 0);
  }

  function closeDropdown() {
    if (activeDropdown && activePlaceholder) {
      activeDropdown.classList.remove('show', PORTAL_CLASS);
      activeDropdown.style.position = '';
      activeDropdown.style.zIndex = '';
      activeDropdown.style.left = '';
      activeDropdown.style.top = '';
      activeDropdown.style.right = '';
      activeDropdown.style.marginRight = '';
      activePlaceholder.parentNode.insertBefore(activeDropdown, activePlaceholder);
      activePlaceholder.parentNode.removeChild(activePlaceholder);
      activeDropdown = null;
      activePlaceholder = null;
      activeTrigger = null;
    }
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onEscape);
  }

  function onDocumentClick(e) {
    if (!activeDropdown) return;
    var t = e.target;
    if (activeDropdown.contains(t)) return;
    var trigger = document.querySelector(TRIGGER_SELECTOR + '.active-dropdown');
    if (trigger && trigger.contains(t)) return;
    closeDropdown();
  }

  function onEscape(e) {
    if (e.key === 'Escape') closeDropdown();
  }

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest(TRIGGER_SELECTOR);
    if (!trigger) return;
    e.preventDefault();
    e.stopPropagation();
    document.querySelectorAll(TRIGGER_SELECTOR).forEach(function (t) { t.classList.remove('active-dropdown'); });
    trigger.classList.add('active-dropdown');
    openDropdown(trigger);
  });

  window.ResmenuActionsDropdown = { open: openDropdown, close: closeDropdown };
})();
