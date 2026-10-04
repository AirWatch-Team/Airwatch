/**
 * AirWatch API Client
 * Dynamic REST API Service Wrapper
 */

const API = (function() {
  // Determine relative base URL dynamically so it works in XAMPP (e.g. /AirWatch/backend/api or /backend/api)
  const getApiBaseUrl = () => {
    const path = window.location.pathname;
    if (path.includes('/AirWatch/') || path.includes('/airwatch/')) {
      const match = path.match(/\/[Aa]ir[Ww]atch/);
      return `${match[0]}/backend/api`;
    }
    return '../backend/api';
  };

  const BASE_URL = getApiBaseUrl();

  /**
   * Helper request handler with error checking
   */
  async function request(endpoint, options = {}) {
    const url = `${BASE_URL}/${endpoint}`;
    
    const defaultHeaders = {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };

    const config = {
      ...options,
      headers: {
        ...defaultHeaders,
        ...(options.headers || {})
      }
    };

    if (config.body && typeof config.body === 'object' && !(config.body instanceof FormData)) {
      config.body = JSON.stringify(config.body);
    }

    try {
      const response = await fetch(url, config);
      const result = await response.json();

      if (!response.ok || result.success === false) {
        throw new Error(result.message || `API Error (${response.status})`);
      }

      return result;
    } catch (error) {
      console.error(`[API Error] ${endpoint}:`, error.message);
      throw error;
    }
  }

  return {
    baseUrl: BASE_URL,

    // Records CRUD
    getRecords: (params = {}) => {
      const queryString = new URLSearchParams(params).toString();
      return request(`records/read.php?${queryString}`);
    },

    getRecordById: (id) => request(`records/read.php?id=${id}`),

    createRecord: (data) => request('records/create.php', {
      method: 'POST',
      body: data
    }),

    updateRecord: (data) => request('records/update.php', {
      method: 'POST',
      body: data
    }),

    deleteRecord: (id) => request('records/delete.php', {
      method: 'POST',
      body: { id }
    }),

    // Analytics & Dashboard
    getDashboardStats: () => request('dashboard/statistics.php'),
    getLocationAnalysis: () => request('analysis/locations.php'),
    getPollutionSources: () => request('analysis/pollution-sources.php'),
    getReportData: () => request('reports/generate.php'),

    // Demo Data Operations
    seedDemoData: () => request('demo/seed.php', { method: 'POST' }),
    clearDemoData: () => request('demo/clear.php', { method: 'POST' }),

    // CSV Export URL helper
    getCsvExportUrl: (params = {}) => {
      const queryString = new URLSearchParams(params).toString();
      return `${BASE_URL}/export/csv.php?${queryString}`;
    }
  };
})();
