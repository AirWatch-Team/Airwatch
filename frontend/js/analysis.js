/**
 * AirWatch Environmental Analysis & Location Comparison Script
 */

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('analysis-page')) {
    initAnalysisPage();
  }
});

let locationData = [];
let pollutionSourcesData = [];
let comparisonChartInstance = null;

async function initAnalysisPage() {
  await fetchAnalysisData();
  renderAnalysisCards();
  renderPollutionSourcesTable();
  renderAutomatedInsights();
  setupLocationComparisonOptions();
}

async function fetchAnalysisData() {
  try {
    const locRes = await API.getLocationAnalysis();
    locationData = locRes.data || [];

    const srcRes = await API.getPollutionSources();
    pollutionSourcesData = srcRes.data || [];
  } catch (error) {
    showAlert('Error loading analysis data: ' + error.message, 'danger');
  }
}

/**
 * Render Highest / Lowest Pollution Cards
 */
function renderAnalysisCards() {
  if (locationData.length === 0) return;

  const highest = locationData[0];
  const lowest = locationData[locationData.length - 1];

  const highestEl = document.getElementById('highest-pollution-card');
  if (highestEl) {
    highestEl.innerHTML = `
      <div style="font-size:0.85rem;color:#64748b;font-weight:700;text-transform:uppercase;">Highest Pollution Hotspot</div>
      <div style="font-size:1.8rem;font-weight:800;color:#dc2626;margin:0.2rem 0;">${highest.location}</div>
      <div>Average AQI: <strong>${highest.average_aqi}</strong> ${getAQIBadgeHtml(Math.round(highest.average_aqi))}</div>
      <div style="font-size:0.85rem;color:#64748b;margin-top:0.4rem;">PM2.5: ${highest.average_pm25} µg/m³ | Records: ${highest.record_count}</div>
    `;
  }

  const lowestEl = document.getElementById('lowest-pollution-card');
  if (lowestEl) {
    lowestEl.innerHTML = `
      <div style="font-size:0.85rem;color:#64748b;font-weight:700;text-transform:uppercase;">Cleanest Environment</div>
      <div style="font-size:1.8rem;font-weight:800;color:#047857;margin:0.2rem 0;">${lowest.location}</div>
      <div>Average AQI: <strong>${lowest.average_aqi}</strong> ${getAQIBadgeHtml(Math.round(lowest.average_aqi))}</div>
      <div style="font-size:0.85rem;color:#64748b;margin-top:0.4rem;">PM2.5: ${lowest.average_pm25} µg/m³ | Records: ${lowest.record_count}</div>
    `;
  }
}

/**
 * Render Pollution Sources Breakdown Table & Chart
 */
function renderPollutionSourcesTable() {
  const tbody = document.getElementById('pollution-sources-tbody');
  if (!tbody) return;

  tbody.innerHTML = pollutionSourcesData.map(src => `
    <tr>
      <td><strong>${src.pollution_source}</strong></td>
      <td>${src.record_count}</td>
      <td><strong>${src.percentage}%</strong></td>
      <td>${src.average_aqi} ${getAQIBadgeHtml(Math.round(src.average_aqi))}</td>
    </tr>
  `).join('');

  // Render Chart
  const ctx = document.getElementById('analysis-sources-chart');
  if (ctx && pollutionSourcesData.length > 0) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: pollutionSourcesData.map(s => s.pollution_source),
        datasets: [{
          label: 'Percentage Contribution (%)',
          data: pollutionSourcesData.map(s => s.percentage),
          backgroundColor: '#059669',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 100 } }
      }
    });
  }
}

/**
 * Data-Driven Automated Environmental Insights Engine
 */
function renderAutomatedInsights() {
  const container = document.getElementById('automated-insights-list');
  if (!container) return;

  const insights = [];

  if (locationData.length > 0) {
    const topLoc = locationData[0];
    const cleanLoc = locationData[locationData.length - 1];

    insights.push(`<strong>${topLoc.location}</strong> represents the primary pollution hotspot, recording the highest average AQI of <strong>${topLoc.average_aqi}</strong>.`);
    insights.push(`<strong>${cleanLoc.location}</strong> exhibits the most favorable air quality profile with an average AQI of <strong>${cleanLoc.average_aqi}</strong>.`);
  }

  if (pollutionSourcesData.length > 0) {
    const mainSrc = pollutionSourcesData[0];
    insights.push(`<strong>${mainSrc.pollution_source}</strong> is currently identified as the leading contributor, accounting for <strong>${mainSrc.percentage}%</strong> of recorded observations.`);
  }

  insights.push(`Continuous daily data collection is active across <strong>${locationData.length}</strong> distinct monitoring zones.`);

  container.innerHTML = insights.map(text => `
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-left:4px solid #059669;padding:1rem;border-radius:8px;margin-bottom:0.75rem;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
      🌿 ${text}
    </div>
  `).join('');
}

/**
 * Location Comparison Interactive Tool
 */
function setupLocationComparisonOptions() {
  const select1 = document.getElementById('compare-loc-1');
  const select2 = document.getElementById('compare-loc-2');
  const compareBtn = document.getElementById('compare-locations-btn');

  if (!select1 || !select2) return;

  const optionsHtml = locationData.map(l => `<option value="${l.location}">${l.location}</option>`).join('');
  select1.innerHTML = optionsHtml;
  select2.innerHTML = optionsHtml;

  if (locationData.length > 1) {
    select2.selectedIndex = 1;
  }

  if (compareBtn) {
    compareBtn.addEventListener('click', updateLocationComparison);
  }

  updateLocationComparison();
}

function updateLocationComparison() {
  const loc1Name = document.getElementById('compare-loc-1').value;
  const loc2Name = document.getElementById('compare-loc-2').value;

  const loc1 = locationData.find(l => l.location === loc1Name);
  const loc2 = locationData.find(l => l.location === loc2Name);

  if (!loc1 || !loc2) return;

  const tbody = document.getElementById('comparison-table-body');
  if (tbody) {
    tbody.innerHTML = `
      <tr><th>Metric</th><th>${loc1.location}</th><th>${loc2.location}</th></tr>
      <tr><td>Average AQI</td><td><strong>${loc1.average_aqi}</strong> (${loc1.aqi_details.status})</td><td><strong>${loc2.average_aqi}</strong> (${loc2.aqi_details.status})</td></tr>
      <tr><td>Highest AQI</td><td>${loc1.highest_aqi}</td><td>${loc2.highest_aqi}</td></tr>
      <tr><td>Lowest AQI</td><td>${loc1.lowest_aqi}</td><td>${loc2.lowest_aqi}</td></tr>
      <tr><td>Average PM2.5 (µg/m³)</td><td>${loc1.average_pm25}</td><td>${loc2.average_pm25}</td></tr>
      <tr><td>Average PM10 (µg/m³)</td><td>${loc1.average_pm10}</td><td>${loc2.average_pm10}</td></tr>
      <tr><td>Total Records Logged</td><td>${loc1.record_count}</td><td>${loc2.record_count}</td></tr>
    `;
  }

  // Render Comparison Bar Chart
  const ctx = document.getElementById('comparison-chart');
  if (ctx) {
    if (comparisonChartInstance) comparisonChartInstance.destroy();

    comparisonChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Avg AQI', 'Highest AQI', 'Avg PM2.5', 'Avg PM10'],
        datasets: [
          {
            label: loc1.location,
            data: [loc1.average_aqi, loc1.highest_aqi, loc1.average_pm25, loc1.average_pm10],
            backgroundColor: '#059669'
          },
          {
            label: loc2.location,
            data: [loc2.average_aqi, loc2.highest_aqi, loc2.average_pm25, loc2.average_pm10],
            backgroundColor: '#0284c7'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
      }
    });
  }
}
