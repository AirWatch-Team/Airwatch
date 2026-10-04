/**
 * AirWatch Global Frontend Logic
 * Navigation, Notification Alert System, and Standard AQI Helper
 */

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  highlightActiveNavLink();
});

/**
 * Mobile Navigation Drawer Toggle
 */
function initMobileMenu() {
  const toggleBtn = document.querySelector('.mobile-toggle');
  const navLinks = document.querySelector('.nav-links');

  if (toggleBtn && navLinks) {
    toggleBtn.addEventListener('click', () => {
      navLinks.classList.toggle('active');
    });
  }
}

/**
 * Highlight Current Page Link in Nav Bar
 */
function highlightActiveNavLink() {
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('.nav-link');

  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

/**
 * Display modern inline alert banner (replaces raw window.alert)
 *
 * @param {string} message - Message text to display
 * @param {string} type - 'success', 'danger', 'warning', 'info'
 * @param {string} containerId - Target element ID or selector
 * @param {number} autoCloseMs - Auto dismiss timeout in ms (0 to stay)
 */
function showAlert(message, type = 'info', containerId = 'alert-container', autoCloseMs = 5000) {
  let container = typeof containerId === 'string' ? document.getElementById(containerId) : containerId;

  if (!container) {
    // Fallback: create alert container at top of main-content
    container = document.createElement('div');
    container.id = 'alert-container';
    const main = document.querySelector('.main-content');
    if (main) main.prepend(container);
  }

  const icons = {
    success: '✅',
    danger: '⚠️',
    warning: '🔔',
    info: 'ℹ️'
  };

  const alertDiv = document.createElement('div');
  alertDiv.className = `alert-banner alert-${type}`;
  alertDiv.innerHTML = `
    <span class="alert-icon">${icons[type] || 'ℹ️'}</span>
    <div class="alert-content" style="flex:1;">${message}</div>
    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:inherit;opacity:0.7;">&times;</button>
  `;

  container.innerHTML = ''; // Replace previous message
  container.appendChild(alertDiv);

  if (autoCloseMs > 0) {
    setTimeout(() => {
      if (alertDiv.parentElement) {
        alertDiv.remove();
      }
    }, autoCloseMs);
  }
}

/**
 * Calculate AQI Status & Return Classification Metadata (US EPA Standard)
 */
function getAQIInfo(aqi) {
  const value = parseInt(aqi, 10);
  if (isNaN(value)) return { status: 'Unknown', class: 'status-good', color: '#64748b' };

  if (value <= 50) {
    return { status: 'Good', class: 'status-good', color: '#047857' };
  } else if (value <= 100) {
    return { status: 'Moderate', class: 'status-moderate', color: '#92400e' };
  } else if (value <= 150) {
    return { status: 'Unhealthy for Sensitive Groups', class: 'status-sensitive', color: '#9a3412' };
  } else if (value <= 200) {
    return { status: 'Unhealthy', class: 'status-unhealthy', color: '#991b1b' };
  } else if (value <= 300) {
    return { status: 'Very Unhealthy', class: 'status-very-unhealthy', color: '#5b21b6' };
  } else {
    return { status: 'Hazardous', class: 'status-hazardous', color: '#9f1239' };
  }
}

/**
 * Generate standard HTML badge for AQI value
 */
function getAQIBadgeHtml(aqi, statusOverride = null) {
  const info = getAQIInfo(aqi);
  const statusText = statusOverride || info.status;
  return `<span class="aqi-badge ${info.class}">● ${statusText} (${aqi})</span>`;
}

/**
 * Format Date to readable string (e.g. Oct 4, 2026)
 */
function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}
