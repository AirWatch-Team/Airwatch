/**
 * AirWatch Environmental Report Generation Engine
 * Supports Web View, Print (window.print), and CSV Download
 */

document.addEventListener('DOMContentLoaded', () => {
  const generateBtn = document.getElementById('generate-report-btn');
  const printBtn = document.getElementById('print-report-btn');
  const csvBtn = document.getElementById('export-csv-btn');

  if (generateBtn) {
    generateBtn.addEventListener('click', openReportModal);
  }

  if (printBtn) {
    printBtn.addEventListener('click', () => {
      window.print();
    });
  }

  if (csvBtn) {
    csvBtn.addEventListener('click', () => {
      window.location.href = API.getCsvExportUrl();
    });
  }
});

async function openReportModal() {
  const modal = document.getElementById('report-modal');
  const content = document.getElementById('report-modal-content');
  if (!modal || !content) return;

  content.innerHTML = '<div style="padding:2rem;text-align:center;">Compiling environmental report from database...</div>';
  modal.classList.add('active');

  try {
    const response = await API.getReportData();
    const rep = response.data;

    if (!rep.has_data) {
      content.innerHTML = '<div class="alert-banner alert-warning">No data available in database to generate report.</div>';
      return;
    }

    content.innerHTML = `
      <div id="printable-report-area" style="font-family:var(--font-family);color:#0f172a;">
        <div style="text-align:center;border-bottom:2px solid #059669;padding-bottom:1rem;margin-bottom:1.5rem;">
          <h1 style="color:#059669;margin:0;font-size:1.8rem;">🌍 AIRWATCH ENVIRONMENTAL REPORT</h1>
          <p style="color:#64748b;margin:0.25rem 0 0 0;font-size:0.9rem;">Web-Based Air Quality Data Collection, Monitoring and Analysis System</p>
          <div style="font-size:0.85rem;color:#94a3b8;margin-top:0.4rem;">Report Generated: <strong>${rep.report_generated_at}</strong></div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:1rem;margin-bottom:1.5rem;">
          <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:1rem;border-radius:8px;">
            <div style="font-size:0.8rem;color:#64748b;font-weight:700;">TOTAL OBSERVATIONS</div>
            <div style="font-size:1.5rem;font-weight:800;color:#0f172a;">${rep.total_records}</div>
          </div>
          <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:1rem;border-radius:8px;">
            <div style="font-size:0.8rem;color:#64748b;font-weight:700;">LOCATIONS MONITORED</div>
            <div style="font-size:1.5rem;font-weight:800;color:#0284c7;">${rep.locations_monitored}</div>
          </div>
          <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:1rem;border-radius:8px;">
            <div style="font-size:0.8rem;color:#64748b;font-weight:700;">SYSTEM AVERAGE AQI</div>
            <div style="font-size:1.5rem;font-weight:800;color:${rep.avg_aqi_details.color};">${rep.avg_aqi}</div>
            <div style="font-size:0.8rem;color:${rep.avg_aqi_details.color};font-weight:600;">${rep.avg_aqi_details.status}</div>
          </div>
          <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:1rem;border-radius:8px;">
            <div style="font-size:0.8rem;color:#64748b;font-weight:700;">PEAK AQI RECORDED</div>
            <div style="font-size:1.5rem;font-weight:800;color:#dc2626;">${rep.highest_aqi}</div>
          </div>
        </div>

        <h3 style="border-left:4px solid #059669;padding-left:0.5rem;margin-bottom:0.75rem;">Summary Analysis</h3>
        <table class="table" style="width:100%;margin-bottom:1.5rem;">
          <tr><th>Most Polluted Location</th><td><strong>${rep.most_polluted_location ? rep.most_polluted_location.location + ' (Avg AQI: ' + rep.most_polluted_location.avg_aqi + ')' : 'N/A'}</strong></td></tr>
          <tr><th>Least Polluted Location</th><td><strong>${rep.least_polluted_location ? rep.least_polluted_location.location + ' (Avg AQI: ' + rep.least_polluted_location.avg_aqi + ')' : 'N/A'}</strong></td></tr>
          <tr><th>Dominant Pollution Source</th><td><strong>${rep.most_common_source}</strong></td></tr>
          <tr><th>Average PM2.5 Concentration</th><td>${rep.avg_pm25} µg/m³</td></tr>
          <tr><th>Average PM10 Concentration</th><td>${rep.avg_pm10} µg/m³</td></tr>
        </table>

        <h3 style="border-left:4px solid #059669;padding-left:0.5rem;margin-bottom:0.75rem;">Automated Environmental Insights</h3>
        <ul style="margin-bottom:1.5rem;padding-left:1.2rem;line-height:1.7;">
          ${rep.insights.map(item => `<li>${item}</li>`).join('')}
        </ul>

        ${rep.alerts.length > 0 ? `
          <h3 style="border-left:4px solid #dc2626;padding-left:0.5rem;margin-bottom:0.75rem;color:#dc2626;">Recent Pollution Alerts (AQI ≥ 151)</h3>
          <table class="table" style="width:100%;margin-bottom:1.5rem;">
            <thead>
              <tr><th>Location</th><th>Date</th><th>AQI</th><th>Status</th><th>Source</th></tr>
            </thead>
            <tbody>
              ${rep.alerts.map(a => `
                <tr>
                  <td>${a.location}</td>
                  <td>${a.record_date}</td>
                  <td><strong style="color:#dc2626;">${a.aqi}</strong></td>
                  <td><span class="aqi-badge status-unhealthy">${a.aqi_status}</span></td>
                  <td>${a.pollution_source}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        ` : ''}

        <div style="font-size:0.8rem;color:#64748b;border-top:1px solid #e2e8f0;padding-top:0.75rem;text-align:center;">
          Report generated autonomously by AirWatch Engine • Clean Air & Environmental Quality Framework
        </div>
      </div>
    `;

  } catch (error) {
    content.innerHTML = `<div class="alert-banner alert-danger">Failed to generate report: ${error.message}</div>`;
  }
}
