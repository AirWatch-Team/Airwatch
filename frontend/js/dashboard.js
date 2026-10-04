/**
 * AirWatch Dashboard Management Script
 * Statistics Cards, Interactive Data Table, Filters, Modals, and Chart.js Visualizations
 */

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('dashboard-page')) {
    initDashboard();
  }
});

let currentPage = 1;
let currentFilters = {};
const chartInstances = {};

/**
 * Initialize Dashboard Data and Charts
 */
async function initDashboard() {
  bindFilterEvents();
  bindModalEvents();
  await loadDashboardStats();
  await loadRecordsTable(1);
}

/**
 * Load Dashboard Headline Statistics Cards
 */
async function loadDashboardStats() {
  try {
    const response = await API.getDashboardStats();
    const stats = response.data;

    document.getElementById('stat-total-records').textContent = stats.total_records || 0;
    document.getElementById('stat-avg-aqi').textContent = stats.avg_aqi ? stats.avg_aqi.toFixed(1) : 0;
    document.getElementById('stat-highest-aqi').textContent = stats.highest_aqi || 0;
    document.getElementById('stat-unique-locations').textContent = stats.unique_locations || 0;
    document.getElementById('stat-avg-pm25').textContent = stats.avg_pm25 ? stats.avg_pm25.toFixed(1) : 0;
    document.getElementById('stat-avg-pm10').textContent = stats.avg_pm10 ? stats.avg_pm10.toFixed(1) : 0;

    // Display Alert Banner if highest AQI is elevated
    const alertContainer = document.getElementById('pollutant-alert-box');
    if (alertContainer && stats.highest_alert && stats.highest_alert.aqi >= 151) {
      const alert = stats.highest_alert;
      alertContainer.innerHTML = `
        <div class="alert-banner alert-danger">
          <span style="font-size:1.5rem;">⚠️</span>
          <div>
            <strong>AIR QUALITY ALERT: High Pollution Detected!</strong>
            <div>Location: <strong>${alert.location}</strong> | AQI: <strong>${alert.aqi}</strong> (${alert.aqi_status}) | Primary Source: ${alert.pollution_source}</div>
          </div>
        </div>
      `;
      alertContainer.style.display = 'block';
    } else if (alertContainer) {
      alertContainer.style.display = 'none';
    }

  } catch (error) {
    showAlert('Failed to load dashboard statistics: ' + error.message, 'danger');
  }
}

/**
 * Load Records Table with Filters & Pagination
 */
async function loadRecordsTable(page = 1) {
  currentPage = page;
  const tbody = document.getElementById('records-table-body');
  if (!tbody) return;

  tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;">Loading measurement records...</td></tr>';

  try {
    const params = {
      page: currentPage,
      limit: 10,
      ...currentFilters
    };

    const response = await API.getRecords(params);
    const records = response.data;
    const meta = response.meta || {};

    if (records.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;color:#64748b;">No air quality records found. Try adjusting your search filters or click "Seed Demo Data".</td></tr>';
      updatePaginationControls(0, 1, 10);
      return;
    }

    tbody.innerHTML = records.map(rec => `
      <tr>
        <td><strong>${formatDate(rec.record_date)}</strong><br><small style="color:#64748b;">${rec.record_time}</small></td>
        <td><strong>${rec.location}</strong></td>
        <td><strong style="font-size:1.1rem;color:${rec.aqi_details.color}">${rec.aqi}</strong></td>
        <td>${getAQIBadgeHtml(rec.aqi, rec.aqi_status)}</td>
        <td>${rec.pm25} µg/m³</td>
        <td>${rec.pm10} µg/m³</td>
        <td><span style="background:#f1f5f9;padding:0.2rem 0.5rem;border-radius:4px;font-size:0.85rem;">${rec.pollution_source}</span></td>
        <td class="table-actions">
          <button class="btn btn-outline btn-sm" onclick="openViewModal(${rec.id})">👁️ View</button>
          <button class="btn btn-outline btn-sm" onclick="openEditModal(${rec.id})">✏️ Edit</button>
          <button class="btn btn-danger btn-sm" onclick="confirmDeleteRecord(${rec.id}, '${rec.location}')">🗑️ Delete</button>
        </td>
      </tr>
    `).join('');

    updatePaginationControls(meta.total_records, meta.page, meta.total_pages);

    // Refresh dashboard charts with all matching records
    await renderDashboardCharts();

  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:2rem;color:#dc2626;">Error: ${error.message}</td></tr>`;
  }
}

/**
 * Update Pagination Controls UI
 */
function updatePaginationControls(totalRecords, currentPage, totalPages) {
  const infoEl = document.getElementById('pagination-info');
  const prevBtn = document.getElementById('prev-page-btn');
  const nextBtn = document.getElementById('next-page-btn');

  if (infoEl) {
    infoEl.textContent = `Showing page ${currentPage} of ${totalPages || 1} (${totalRecords} total records)`;
  }

  if (prevBtn) {
    prevBtn.disabled = (currentPage <= 1);
    prevBtn.onclick = () => loadRecordsTable(currentPage - 1);
  }

  if (nextBtn) {
    nextBtn.disabled = (currentPage >= totalPages);
    nextBtn.onclick = () => loadRecordsTable(currentPage + 1);
  }
}

/**
 * Filter Bar Form Submissions & Reset
 */
function bindFilterEvents() {
  const filterForm = document.getElementById('filter-form');
  const resetBtn = document.getElementById('reset-filters-btn');

  if (filterForm) {
    filterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      currentFilters = {
        location: document.getElementById('filter-location').value,
        start_date: document.getElementById('filter-start-date').value,
        end_date: document.getElementById('filter-end-date').value,
        status: document.getElementById('filter-status').value,
        search: document.getElementById('filter-search').value
      };
      loadRecordsTable(1);
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      if (filterForm) filterForm.reset();
      currentFilters = {};
      loadRecordsTable(1);
    });
  }
}

/**
 * Render Interactive Chart.js Visualizations
 */
async function renderDashboardCharts() {
  try {
    // Fetch all filtered records without pagination limit for charts
    const response = await API.getRecords({ limit: -1, ...currentFilters });
    const records = response.data;

    if (records.length === 0) return;

    // 1. AQI vs Date Line Chart
    renderAqiTrendChart(records);

    // 2. AQI by Location Bar Chart
    renderLocationBarChart(records);

    // 3. PM2.5 vs Date Line Chart
    renderPm25TrendChart(records);

    // 4. Pollution Sources Doughnut Chart
    renderSourcesDoughnutChart(records);

  } catch (error) {
    console.error('Error rendering dashboard charts:', error);
  }
}

function destroyChart(chartId) {
  if (chartInstances[chartId]) {
    chartInstances[chartId].destroy();
    delete chartInstances[chartId];
  }
}

function renderAqiTrendChart(records) {
  const ctx = document.getElementById('chart-aqi-trend');
  if (!ctx) return;

  destroyChart('chart-aqi-trend');

  // Sort chronologically
  const sorted = [...records].sort((a, b) => new Date(a.record_date) - new Date(b.record_date));
  const labels = sorted.map(r => `${r.record_date} (${r.location})`);
  const dataValues = sorted.map(r => r.aqi);
  const colors = sorted.map(r => r.aqi_details.color);

  chartInstances['chart-aqi-trend'] = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'AQI Level',
        data: dataValues,
        borderColor: '#059669',
        backgroundColor: 'rgba(5, 150, 105, 0.1)',
        fill: true,
        tension: 0.3,
        pointBackgroundColor: colors,
        pointRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => `AQI: ${ctx.raw}`
          }
        }
      },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Air Quality Index (AQI)' } },
        x: { ticks: { maxRotation: 45, minRotation: 45 } }
      }
    }
  });
}

function renderLocationBarChart(records) {
  const ctx = document.getElementById('chart-aqi-location');
  if (!ctx) return;

  destroyChart('chart-aqi-location');

  // Calculate average AQI per location
  const locMap = {};
  records.forEach(r => {
    if (!locMap[r.location]) locMap[r.location] = { sum: 0, count: 0 };
    locMap[r.location].sum += r.aqi;
    locMap[r.location].count += 1;
  });

  const locations = Object.keys(locMap);
  const avgAqis = locations.map(loc => Math.round(locMap[loc].sum / locMap[loc].count));
  const bgColors = avgAqis.map(val => getAQIInfo(val).color);

  chartInstances['chart-aqi-location'] = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: locations,
      datasets: [{
        label: 'Average AQI',
        data: avgAqis,
        backgroundColor: bgColors,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Average AQI' } }
      }
    }
  });
}

function renderPm25TrendChart(records) {
  const ctx = document.getElementById('chart-pm25-trend');
  if (!ctx) return;

  destroyChart('chart-pm25-trend');

  const sorted = [...records].sort((a, b) => new Date(a.record_date) - new Date(b.record_date));
  const labels = sorted.map(r => r.record_date);
  const pm25Data = sorted.map(r => r.pm25);

  chartInstances['chart-pm25-trend'] = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'PM2.5 (µg/m³)',
        data: pm25Data,
        borderColor: '#0284c7',
        backgroundColor: 'rgba(2, 132, 199, 0.1)',
        fill: true,
        tension: 0.3,
        pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: true } },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'PM2.5 Concentration' } }
      }
    }
  });
}

function renderSourcesDoughnutChart(records) {
  const ctx = document.getElementById('chart-pollution-sources');
  if (!ctx) return;

  destroyChart('chart-pollution-sources');

  const srcMap = {};
  records.forEach(r => {
    const src = r.pollution_source || 'Other';
    srcMap[src] = (srcMap[src] || 0) + 1;
  });

  const labels = Object.keys(srcMap);
  const counts = Object.values(srcMap);
  const colors = ['#059669', '#0284c7', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'];

  chartInstances['chart-pollution-sources'] = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: counts,
        backgroundColor: colors.slice(0, labels.length)
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right' }
      }
    }
  });
}

/* ====================================================================
   VIEW, EDIT, & DELETE MODAL HANDLERS
   ==================================================================== */
function bindModalEvents() {
  const closeBtns = document.querySelectorAll('.modal-close, .modal-cancel');
  closeBtns.forEach(btn => {
    btn.addEventListener('click', closeModal);
  });

  const editForm = document.getElementById('edit-record-form');
  if (editForm) {
    editForm.addEventListener('submit', handleUpdateRecordSubmit);
  }
}

function closeModal() {
  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.classList.remove('active');
  });
}

async function openViewModal(id) {
  try {
    const response = await API.getRecordById(id);
    const rec = response.data;
    const details = rec.aqi_details;

    const content = document.getElementById('view-modal-content');
    content.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <h3 style="margin:0;font-size:1.3rem;">${rec.location}</h3>
        ${getAQIBadgeHtml(rec.aqi, rec.aqi_status)}
      </div>
      <table class="table" style="width:100%;margin-bottom:1rem;">
        <tr><th>Measurement Date:</th><td>${formatDate(rec.record_date)} at ${rec.record_time}</td></tr>
        <tr><th>Air Quality Index (AQI):</th><td><strong style="color:${details.color};font-size:1.2rem;">${rec.aqi}</strong></td></tr>
        <tr><th>AQI Status:</th><td>${rec.aqi_status}</td></tr>
        <tr><th>PM2.5 Concentration:</th><td>${rec.pm25} µg/m³</td></tr>
        <tr><th>PM10 Concentration:</th><td>${rec.pm10} µg/m³</td></tr>
        <tr><th>Temperature:</th><td>${rec.temperature ? rec.temperature + ' °C' : 'N/A'}</td></tr>
        <tr><th>Humidity:</th><td>${rec.humidity ? rec.humidity + ' %' : 'N/A'}</td></tr>
        <tr><th>Pollution Source:</th><td>${rec.pollution_source}</td></tr>
        <tr><th>Health Advisory:</th><td style="color:${details.color};font-weight:600;">${details.health_advisory}</td></tr>
        <tr><th>Notes:</th><td>${rec.notes || 'None recorded'}</td></tr>
        <tr><th>Database Record ID:</th><td>#${rec.id}</td></tr>
      </table>
    `;

    document.getElementById('view-modal').classList.add('active');
  } catch (error) {
    showAlert('Failed to load record details: ' + error.message, 'danger');
  }
}

async function openEditModal(id) {
  try {
    const response = await API.getRecordById(id);
    const rec = response.data;

    document.getElementById('edit-id').value = rec.id;
    document.getElementById('edit-location').value = rec.location;
    document.getElementById('edit-date').value = rec.record_date;
    document.getElementById('edit-time').value = rec.record_time;
    document.getElementById('edit-aqi').value = rec.aqi;
    document.getElementById('edit-pm25').value = rec.pm25;
    document.getElementById('edit-pm10').value = rec.pm10;
    document.getElementById('edit-temperature').value = rec.temperature || '';
    document.getElementById('edit-humidity').value = rec.humidity || '';
    document.getElementById('edit-pollution-source').value = rec.pollution_source;
    document.getElementById('edit-notes').value = rec.notes || '';

    document.getElementById('edit-modal').classList.add('active');
  } catch (error) {
    showAlert('Failed to load record for editing: ' + error.message, 'danger');
  }
}

async function handleUpdateRecordSubmit(e) {
  e.preventDefault();
  const form = e.target;

  const payload = {
    id: document.getElementById('edit-id').value,
    location: document.getElementById('edit-location').value,
    date: document.getElementById('edit-date').value,
    time: document.getElementById('edit-time').value,
    aqi: document.getElementById('edit-aqi').value,
    pm25: document.getElementById('edit-pm25').value,
    pm10: document.getElementById('edit-pm10').value,
    temperature: document.getElementById('edit-temperature').value,
    humidity: document.getElementById('edit-humidity').value,
    pollution_source: document.getElementById('edit-pollution-source').value,
    notes: document.getElementById('edit-notes').value
  };

  try {
    const response = await API.updateRecord(payload);
    closeModal();
    showAlert(`Record #${response.data.id} updated successfully!`, 'success');
    await loadDashboardStats();
    await loadRecordsTable(currentPage);
  } catch (error) {
    showAlert('Failed to update record: ' + error.message, 'danger', 'edit-modal-alerts');
  }
}

function confirmDeleteRecord(id, location) {
  const modal = document.getElementById('delete-modal');
  if (!modal) {
    if (confirm(`Are you sure you want to delete record #${id} from ${location}?`)) {
      executeDeleteRecord(id);
    }
    return;
  }

  document.getElementById('delete-record-info').textContent = `Record #${id} (${location})`;
  document.getElementById('confirm-delete-btn').onclick = async () => {
    await executeDeleteRecord(id);
    closeModal();
  };

  modal.classList.add('active');
}

async function executeDeleteRecord(id) {
  try {
    const response = await API.deleteRecord(id);
    showAlert(response.message, 'success');
    await loadDashboardStats();
    await loadRecordsTable(currentPage);
  } catch (error) {
    showAlert('Failed to delete record: ' + error.message, 'danger');
  }
}
