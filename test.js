document.addEventListener("DOMContentLoaded", function () {
  function updateCardStats() {
    $.ajax({
      url: '/api/dashboard-stats', // The route we created in Step 2
      method: 'GET',
      success: function (data) {
        const moneyOptions = {
          startVal: 0, // <--- THIS FORCES IT TO START FROM ZERO
          prefix: 'Rp ',
          separator: '.',
          decimal: ',',
          duration: 3
        };

        const numberOptions = {
          startVal: 0, // <--- THIS FORCES IT TO START FROM ZERO
          duration: 3
        }; // Animate each card
        // Initialize and start the animations
        const danaTersedia = new CountUp('dana-tersedia', data.danaTersedia, numberOptions);
        const totalDanaDitf = new CountUp('total-dana-ditf', data.totalDanaDitf, numberOptions);
        const investasiDikembalikan = new CountUp('investasi-dikembalikan', data.investasiDikembalikan, numberOptions);
        const totalTfInvestasi = new CountUp('total-tf-investasi', data.totalTfInvestasi, numberOptions);
        const marginDiterima = new CountUp('margin-diterima', data.marginDiterima, numberOptions);
        const totalMargin = new CountUp('total-margin', data.totalMargin, numberOptions);
        const sisaMargin = new CountUp('sisa-margin', data.sisaMargin, numberOptions);
        const marginTersedia = new CountUp('margin-tersedia', data.marginTersedia, numberOptions);
        const investasiDitahan = new CountUp('investasi-ditahan', data.investasiDitahan, numberOptions);
        const marginDitahan = new CountUp('margin-ditahan', data.marginDitahan, numberOptions);

        danaTersedia.start();
        totalDanaDitf.start();
        investasiDikembalikan.start();
        totalTfInvestasi.start();
        marginDiterima.start();
        totalMargin.start();
        sisaMargin.start();
        marginTersedia.start();
        investasiDitahan.start();
        marginDitahan.start();
      },
      error: function (err) {
        console.error('Failed to fetch stats', err);
      }
    });
  }
  updateCardStats();
});

// Simplified Version

document.addEventListener("DOMContentLoaded", () => {
  const numberOptions = { startVal: 0, duration: 3 };

  // 1. Map your API data keys to their corresponding HTML IDs
  const statsMap = [
    'dana-tersedia', 'total-dana-ditf', 'investasi-dikembalikan',
    'total-tf-investasi', 'margin-diterima', 'total-margin',
    'sisa-margin', 'margin-tersedia', 'investasi-ditahan', 'margin-ditahan'
  ];

  function updateCardStats() {
    $.getJSON('/api/dashboard-stats')
      .done(data => {
        statsMap.forEach(id => {
          // Convert kebab-case ID to camelCase data key if they differ, 
          // or just use the ID as the key if they match.
          const dataKey = id.replace(/-([a-z])/g, g => g[1].toUpperCase());

          if (data[dataKey] !== undefined) {
            new CountUp(id, data[dataKey], numberOptions).start();
          }
        });
      })
      .fail(err => console.error('Failed to fetch stats', err));
  }

  // Initial load
  updateCardStats();

  // 2. THE AUTO-UPDATE: Listen for the Laravel Broadcast event
  if (typeof Echo !== 'undefined') {
    Echo.channel('global-updates')
      .listen('CrudActionOccurred', (e) => {
        console.log('Real-time update triggered:', e.message);
        updateCardStats(); // Re-run the animation with new data
      });
  }
});